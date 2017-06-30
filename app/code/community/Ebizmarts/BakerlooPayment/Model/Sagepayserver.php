<?php

/**
 * Sagepay (Server iFrame integration)
 */
class Ebizmarts_BakerlooPayment_Model_Sagepayserver extends Ebizmarts_BakerlooPayment_Model_Method_Iframe {

    protected $_code          = "bakerloo_sagepayserver";
    const SIM_API_VERSION     = '3.00';

    protected $_infoBlockType = 'bakerloo_payment/info_sagepayServer';

    public function getSMIPostUrl() {
        $code = 'sagepayserver';
        $key  = 'post';

        $_mode = $this->getConfigData('api_mode');

        $urls = Mage::helper('sagepaysuite')->getSagePayUrlsAsArray();

        return $urls[$code][$_mode][$key];

    }

    public function isSagepaySuiteInstalledAndCompatible(){

        if(Mage::helper('core')->isModuleEnabled('Ebizmarts_SagePaySuite')){
            $version = (string) Mage::getConfig()->getNode()->modules->Ebizmarts_SagePaySuite->version;
            if(strcmp($version,'3.0.0')>=0){
                return true;
            }else{
                Mage::throwException("You are running an old version of the SagePaySuite module. Version supported is 3.0.0+");
            }
        }else{
            Mage::throwException("SagePaySuite module is either not installed or disabled.");
        }
    }

    public function buildSagepayServerRequest($postData){
        if($this->_verifyRequiredData($postData)){
            return $this->_buildRequest($postData);
        }
    }

    protected function _buildRequest($postData) {

        $accountType = $this->getConfigData('account_type');

        if($accountType == 'E')
            $sagepaysuite = Mage::getModel('sagepaysuite/sagePayServer');
        else
            $sagepaysuite = Mage::getModel('sagepaysuite/sagePayServerMoto');

        // Get request data
        $action        = $sagepaysuite->getConfigData('payment_action');
        $vendor        = $sagepaysuite->getConfigData('vendor');
        $orderId       = $postData->getData('orderId');
        $vendorTxCode  = substr($orderId . '-' . date('Y-m-d-H-i-s'), 0, 40);
        $amount        = Mage::getModel('sagepaysuite/api_payment')->formatAmount( $postData->getData('amount'),  $postData->getData('currency') );
        $currency      = $postData->getData('currency');
        $description   = $postData->getData('description');
        $customerEmail = $postData->getData('customerEmail');
        $billSurname   = $postData->getData('billSurname');
        $billFirstname = $postData->getData('billFirstname');
        $billAddress   = $postData->getData('billAddress');
        $billCity      = $postData->getData('billCity');
        $billPostcode  = $postData->getData('billPostcode');
        $billCountry   = $postData->getData('billCountry');
        $billState     = $postData->getData('billState');

        $request                       = array();
        $request['VPSProtocol']        = self::SIM_API_VERSION;
        $request['TxType']             = strtoupper($action);
        $request['Vendor']             = $vendor;
        $request['VendorTxCode']       = $vendorTxCode;
        $request['Amount']             = $amount;
        $request['Currency']           = $currency;
        $request['Description']        = $description;
        $request['NotificationURL']    = Mage::getModel('core/url')->addSessionParam()->getUrl('pos_payment/sagepayServer/notify', array('_secure' => true, 'method' => $postData->getMethod()));
        $request['SuccessURL']         = Mage::getModel('core/url')->addSessionParam()->getUrl('pos_payment/sagepayServer/notify', array('_secure' => true, 'method' => $postData->getMethod()));
        $request['RedirectURL']        = Mage::getModel('core/url')->addSessionParam()->getUrl('pos_payment/sagepayServer/notify', array('_secure' => true, 'method' => $postData->getMethod()));
        $request['FailureURL']         = Mage::getModel('core/url')->addSessionParam()->getUrl('pos_payment/sagepayServer/notify', array('_secure' => true, 'method' => $postData->getMethod()));
        $request['CustomerEMail']      = $customerEmail;
        $request['BillingSurname']     = $billSurname;
        $request['BillingFirstnames']  = $billFirstname;
        $request['BillingAddress1']    = $billAddress;
        $request['BillingCity']        = $billCity;
        $request['BillingPostCode']    = $billPostcode;
        $request['BillingCountry']     = $billCountry;
        $request['DeliverySurname']    = $billSurname;
        $request['DeliveryFirstnames'] = $billFirstname;
        $request['DeliveryAddress1']   = $billAddress;
        $request['DeliveryCity']       = $billCity;
        $request['DeliveryPostCode']   = $billPostcode;
        $request['DeliveryCountry']    = $billCountry;
        $request['Profile']            = "LOW";

        if ($request['DeliveryCountry'] == 'US') {
            $request['DeliveryState'] = $billState;
        }
        if ($request['BillingCountry'] == 'US') {
            $request['BillingState'] = $billState;
        }

        $request['AccountType'] = $accountType;

        if($accountType == 'M')
            $data['Apply3DSecure'] = '2';

        $sagePayServerSession = Mage::getSingleton('sagepaysuite/session');
        $sagePayServerSession->setVendorTxCode($vendorTxCode);

        return $request;


    }

    /*protected function _verifyRequiredData($postData){

        //@TODO: Validate request

        return parent::_verifyRequiredData($postData);
    }*/

    public function requestPost($request){
        return $this->_postRequest($request);
    }

    protected function _postRequest($requestData) {

        $result = Mage::getModel('sagepaysuite/sagepaysuite_result');
        $paymentApi = Mage::getModel('sagepaysuite/api_payment');
        $sagePayServerSession = Mage::getSingleton('sagepaysuite/session');

        try {

            $response = $paymentApi->requestPost($this->getSMIPostUrl(), $requestData);

        } catch (Exception $e) {

            Mage::logException($e);

            $result->setResponseStatus('ERROR')->setResponseStatusDetail($e->getMessage());
            return $result;
        }

        $r = $response;

        try {

            if (empty($r) || $r['Status'] == 'FAIL') {
                $msg = Mage::helper('sagepaysuite')->__('Sage Pay is not available at this time. Please try again later.');
                $result->setResponseStatus('ERROR')->setResponseStatusDetail($msg);
                return $result;
            }

            if ($this->_isInvalid($r['Status'])) {
                $result->setResponseStatus($r['Status'])->setResponseStatusDetail(Mage::helper('sagepaysuite')->__($r['StatusDetail']));
            }
            else {

                $result->setResponseStatus($r['Status'])->setResponseStatusDetail(Mage::helper('sagepaysuite')->__($r['StatusDetail']))->setNextUrl($r['NextURL'])->setVPSTxID($r['VPSTxId']);
                $result->addData(Mage::helper('sagepaysuite')->arrayKeysToUnderscore($r));
                $result->setTxType($requestData['TxType']);

                // Set values on SagePayServer session's namespace
                $sagePayServerSession->setSecurityKey($r['SecurityKey'])
                                    ->setVpsTxId($r['VPSTxId'])
                                    ->setNextUrl($r['NextURL']);

                Mage::log(Mage::getSingleton('sagepaysuite/session'));

            }

        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
            Mage::logException($e);

            $result->setResponseStatus('ERROR')->setResponseStatusDetail($e->getMessage());
            return $result;
        }

        return $result;
    }

    protected function _isInvalid($status) {

        $invalid = false;

        if ($status == Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_CODE_INVALID || $status == Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_CODE_MALFORMED || $status == Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_CODE_ERROR || $status == Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_CODE_REJECTED || $status == Ebizmarts_SagePaySuite_Model_Api_Payment::RESPONSE_CODE_NOTAUTHED) {
            $invalid = true;
        }

        return $invalid;
    }

    public function getReturnData($post = array()) {
        return $post;
    }

}