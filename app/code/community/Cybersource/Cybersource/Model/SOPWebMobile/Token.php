<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Token extends Mage_Core_Model_Abstract
{
    /**
     * Set to tru if the response from cybersource has to be logged.
     * @access public
     */
    public $_logResponse = null;

    /**
     * Holds system config values.
     * @access public
     * @var array
     */
    public $_config = null;

    /**
     * Cybersource URL
     * @access public
     * @var string
     */
    public $_url = null;

    /**
     * Request parameters
     * @access public
     * @var array
     */
    public $_params = null;

    /**
     * Request fields
     * @access public
     * @var array
     */
    public $_fields = null;

    /**
     * Customer token
     * @access public
     * @var string
     */
    public $_tokenID = null;

    /**
     * Store Id
     * @access public
     * @var string|int
     */
    public $_storeId = null;

    /**
     * Generated merchant reference
     * @access public
     */
    public $_merchantRef = null;

    /**
     * Old customer's token. Used when updating tokenID.
     * @access public
     */
    public $_oldTokenID = null;

    /**
     * @return Cybersource_Cybersource_Model_Core_Soap_Client
     */
    private function getSoapClient()
    {
        $wsdlPath = Mage::helper('cybersource_core')->getWsdlUrl();
        $client = new Cybersource_Cybersource_Model_Core_Soap_Client($wsdlPath);

        $client->setContext(self::class);
        $client->setLogFilename(Cybersource_Cybersource_Helper_SOPWebMobile_Data::LOGFILE);
        $client->setPreventLogFlag(!Mage::helper('cybersourcesop')->isDebugMode());

        return $client;
    }

    /**
     * Main constructor
     */
    protected function _construct()
    {
        $this->_init('cybersourcesop/token');
        $this->getConfig();
    }

    /**
     * Loads payment system config
     */
    protected function getConfig()
    {
        if (!$this->_storeId) {
            $this->_storeId = Mage::app()->getStore()->getStoreId();
        }
        $this->_config = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig(null, $this->_storeId);
    }

    /**
     * Generates token delete request
     * @param $tokenId
     * @param $merchantRef
     * @return array|bool|string
     */
    public function createDeleteRequest($tokenId, $merchantRef)
    {
        $this->_merchantRef = $merchantRef;

        $request = new stdClass();
        $request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $request->merchantReferenceCode = $this->_generateReferenceCode();

        $recurringSubscriptionInfo = new stdClass();
        $recurringSubscriptionInfo->subscriptionID = $tokenId;
        $request->recurringSubscriptionInfo = $recurringSubscriptionInfo;

        $paySubscriptionDelete = new stdClass();
        $paySubscriptionDelete->run = 'true';
        $request->paySubscriptionDeleteService = $paySubscriptionDelete;

        $this->_params = $request;

        return $this->callCyber();
    }

    /**
     * Returns generated merchant reference code
     * @return string
     */
    protected function _generateReferenceCode()
    {
        //else use unique hash
        if (is_null($this->_merchantRef)) {
            return Mage::helper('core')->uniqHash();
        }
        return $this->_merchantRef;
    }

    /**
     * Returns a collection of tokens
     * @return mixed
     */
    protected function getTokens()
    {
        return $this->getCollection();
    }

    /**
     * Loads token by id
     * @param $id
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Token
     */
    public function getTokenValue($id)
    {
        $token = $this->load($id);
        return $token;
    }

    /**
     * Sends the request to cybersource
     * @return array|bool|string
     */
    protected function callCyber()
    {
        $soapClient = $this->getSoapClient();
        $tokenId = (string)$this->_params->recurringSubscriptionInfo->subscriptionID;
        $result = $soapClient->runTransaction($this->_params);
        if (isset($result->paySubscriptionCreateReply)) {
            if ((int)$result->paySubscriptionCreateReply->reasonCode == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
                $this->_tokenID = (string)$result->paySubscriptionCreateReply->subscriptionID;
            } else {
                return false;
            }
        } elseif (isset($result->paySubscriptionDeleteReply)) {
            if ((int)$result->paySubscriptionDeleteReply->reasonCode == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
                $this->load($tokenId, 'token_id');
                if ($this->getData()) {
                    //Delete Token from Magento
                    $this->delete();
                }
                return true;
            } else {
                return false;
            }
        } elseif (isset($result->paySubscriptionUpdateReply)) {
            if ((int)$result->paySubscriptionUpdateReply->reasonCode == Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
                //Update the token:
                if (isset($result->paySubscriptionUpdateReply->subscriptionIDNew)) {
                    $this->_tokenID = (string)$result->paySubscriptionUpdateReply->subscriptionIDNew;
                } else {
                    $this->_tokenID = (string)$result->paySubscriptionUpdateReply->subscriptionID;
                }

            } else {
                $response = array((int)$result->paySubscriptionUpdateReply->reasonCode, isset($result->invalidField) ? (string)$result->invalidField : "");
                return $response;
            }
        } else {
            return false;
        }

        return $this->_tokenID;
    }

    /**
     * core iterator callback method to set all customer default tokens to nothing.
     * @param array $args
     */
    public function saveDefaultTokenCallback($args)
    {
        $defaultToken = Mage::getModel('cybersourcesop/token');
        $defaultToken->setData($args['row']);
        $defaultToken->setIsDefault(0);
        $defaultToken->save();
    }

    /**
     * Sets the default token
     * @return $this|string
     */
    public function setAsDefault()
    {
        $defaultToken = Mage::getModel('cybersourcesop/token')->getCollection()
            ->addFieldToFilter('customer_id', $this->getCustomerId());

        if (count($defaultToken) > 1) {
            Mage::getSingleton('core/resource_iterator')->walk($defaultToken->getSelect(), array(array($this, 'saveDefaultTokenCallback')));
        } else {
            $defaultToken->setIsDefault(0)->save();
        }
        //clear out all other tokens assigned to customer and set their default to null

        //save it as the default last.
        try {
            $this->setIsDefault(1)->save();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $this;
    }

    /**
     * Loads default token for the customer
     * @param string $customer_id
     * @return mixed
     */
    public function getDefaultToken($customer_id)
    {
        $collection = $this->getResourceCollection()->addCustomerFilter($customer_id)->addDefaultFilter()->setPageSize(1);
        return $collection->getFirstItem()->getTokenId();
    }
}
