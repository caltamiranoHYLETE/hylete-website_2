<?php

/**
 * POS REST Api entry point.
 */
class Ebizmarts_BakerlooRestful_IndexController extends Mage_Core_Controller_Front_Action
{

    public $verb = null;
    public $parameters = array();

    public function indexAction() {

        $this->verb       = $this->getRequest()->getMethod();
        $this->parameters = $this->getRequest()->getParams();

        $h = Mage::helper('bakerloo_restful');

        $id = $h->debug($this->getRequest(), $this->_getControllerName());
        Mage::register('brest_request_id', $id);
        //@TODO: Log ERRORS (rejected orders for example) always to DB even though logs are disabled.

        $moduleVersion = $h->getApiModuleVersion();

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setHeader('Connection', 'keep-alive', true);

        //Check if module is active
        if (!$this->_isEnabled()) {
            return $this->getResponse()
                ->setHttpResponseCode(410)
                ->setBody($this->encodeResponse($this->_error("Service is not active.")));
        }

        //Check if activation request
        if ($this->_isActivationRequest()) {
            $activationData = $this->_getActivateAccountData();
            return $this->getResponse()
                ->setHttpResponseCode(200)
                ->setBody($this->encodeResponse($activationData));
        }

        //Validate Request
        try {

            $this->_isCallAllowed();

        } catch (Mage_Core_Exception $ce) {
            return $this->getResponse()
                ->setHttpResponseCode(401)
                ->setBody($this->encodeResponse($this->_error($ce->getMessage())));
        }

        //Set API Key Header
        $this->getResponse()
            ->setHeader($h->getApiKeyHeader(), $this->_getApiKey(), true)
            ->setHeader($h->getApiVersionHeader(), $moduleVersion, true)
            ->setHeader($h->getMagentoVersionHeader(), Mage::helper('bakerloo_restful')->getMagentoVersionCode() . ' ' . Mage::getVersion(), true);

        try {

            //Validate version provided on Accept header
            $accept = (string)$this->getRequest()->getHeader('B-Accept');
            if ($accept != "*/*" && !empty($accept)) {
                $requestVersion = explode("=", $accept);

                $requestVersion = $requestVersion[1];

                if (version_compare($requestVersion, $moduleVersion, 'gt')) {

                    $versionMessage = Mage::helper('bakerloo_restful')->__("Version not supported. You asked for v%s, I have v%s.", $requestVersion, $moduleVersion);

                    $this->getResponse()
                        ->setHttpResponseCode(406)
                        ->setBody($this->encodeResponse($this->_error($versionMessage)));
                    return;
                }
            }

            if($this->getRequest()->getParam('PROFILER')) {
                $h->startprofiler();
            }

            try {
                $className = 'bakerloo_restful/api_' . $this->_getControllerName();

                $model = Mage::getModel($className, $this->parameters);
            } catch (Mage_Core_Model_Store_Exception $e) {
                Mage::logException($e);
                Mage::throwException("Invalid Store.");
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::throwException("Resource not found.");
            }

            //GET, POST, PUT, DELETE...
            $actionName = strtolower($this->verb);

            //Allow custom actions to be used eg ?action=sendEmail
            //When using custom actions, HTTP VERB does not matter
            $customAction = $this->getRequest()->getParam('action', null);
            if (!empty($customAction)) {
                $actionName = $customAction;
            }

            //Execute action
            if ('getZip' == $actionName) {
                Mage::helper('bakerloo_restful/pages')->getZippedPages($this->getRequest(), $this->getResponse(), null, false);
                return;
            }
            elseif ('getDB' == $actionName) {
                Mage::helper('bakerloo_restful/pages')->getDB($this->getRequest(), $this->getResponse(), true, null, false);
                return;
            }
            elseif (is_object($model) && $model->getModelName() == "Ebizmarts_BakerlooRestful_Model_Api_Backup" && 'get' == $actionName) {
                $model->getBackup($this->getResponse());
                return;
            }
            else {

                if (!method_exists($model, $actionName)) {
                    Mage::throwException("Invalid action.");
                }

                $result = $model->$actionName();
            }

            if($this->getRequest()->getParam('PROFILER')) {
                $h->logprofiler((int)$this->getRequest()->getHeader('B-Store-Id'), $this->_getControllerName(), $actionName);
                $h->endprofiler();
            }

            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setBody($this->encodeResponse($result));

            if ($actionName == "post") {

                if(is_array($result) && isset($result['error_message'])) {
                    $this->getResponse()->setHttpResponseCode(500);
                }
                else {
                    $this->getResponse()->setHttpResponseCode(201);
                }

            }

        } catch (Exception $ex) {

            Mage::logException($ex);

            if($this->getRequest()->getParam('PROFILER')) {
                $h->logprofiler((int)$this->getRequest()->getHeader('B-Store-Id'), $this->_getControllerName(), $actionName);
                $h->endprofiler();
            }

            $this->getResponse()
                ->setHttpResponseCode(500)
                ->setBody($this->encodeResponse($this->_error($ex->getMessage())));

        }

    }

    private function _getControllerName()
    {

        $params = $this->parameters;
        $name = "";

        if (count($params)) {
            $keys = array_keys($params);
            $name = $keys[0];
        }

        return $name;

    }

    public function encodeResponse($data)
    {
        return Mage::helper("bakerloo_restful")->encodeResponse($data);
    }

    private function _error($message)
    {
        return Mage::helper("bakerloo_restful")->jsonError($message);
    }

    /**
     * Validate request IP address checking against whitelist in config.
     *
     * @param type $storeId
     * @return boolean
     */
    private function _isCallAllowed($storeId = null)
    {
        return Mage::helper("bakerloo_restful")->isCallAllowed($this->getRequest(), $storeId);
    }

    private function _isActivationRequest()
    {

        $activationKey = $this->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getActivationKeyHeader());

        if ($activationKey && $activationKey != '') {
            return true;
        } else {
            return false;
        }


    }

    private function _getActivateAccountData()
    {

        $activationKey = $this->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getActivationKeyHeader());

        $decriptedKeyData = Mage::helper('bakerloo_restful')->decryptActivationKey($activationKey);

        //activation key syntax is URL|apipath|DATE_CREATED
        $pieces = explode("|", $decriptedKeyData);
        if (count($pieces) == 3) {

            $url = $pieces[0];
            $date = $pieces[2];
            $magentoDomain = Mage::helper('bakerloo_restful')->getMagentoDomain();
            $currDate = date("Y-m-d");
            $hourdiff = round((strtotime($currDate) - strtotime($date)) / 3600, 1);

            if (strpos($magentoDomain, $url) == 0) {     //($url == $magentoDomain) {
                if ($hourdiff < Mage::helper('bakerloo_restful')->getActivationKeyExpirationHours()) {
                    return $this->encodeResponse(array("api_key" => $this->_getApiKey(), "shop_type" => "magento"));
                } else {
                    return $this->encodeResponse(array("error" => "Activation key expired"));
                }
            } else {
                return $this->encodeResponse(array("error" => "Incorrect domain in activation key"));
            }

        } else {
            return $this->encodeResponse(array("error" => "Incorrect activation key"));
        }

    }

    /**
     * Check if the module is active.
     *
     */
    private function _isEnabled()
    {
        return (boolean)Mage::helper("bakerloo_restful")->config("general/enabled");
    }

    private function _getApiKey()
    {
        return Mage::helper('bakerloo_restful')->getApiKey();
    }

}