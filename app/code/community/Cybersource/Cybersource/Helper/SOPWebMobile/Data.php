<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Helper_SOPWebMobile_Data extends Mage_Core_Helper_Abstract
{
    const LOGFILE = 'cybs_sa.log';

	/**
     * Get controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName();
    }
    
     /**
     * Retrieve save order url params
     *
     * @param string $controller
     * @return array
     */
    public function getSaveOrderUrlParams($controller)
    {
        $route = array();
        if ($controller === "onepage") {
            $route['action'] = 'saveOrder';
            $route['controller'] = 'onepage';
            $route['module'] = 'checkout';
        }

        return $route;
    }

    public function getPaymentAction()
    {
		$config = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig();
		return $this->isMobile() ? $config['mobile_payment_action'] : $config['payment_action'];
	}
	
	public function isMobile()
    {
        if (! Mage::app()->getStore()->isAdmin()) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $methodInstance = $quote->getPayment()->getMethodInstance();
            if ($methodInstance->getCode() == Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck::CODE) {
                return false;
            }
        }

        return (bool) Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('mobile_enabled');
    }

    public function getCybersourceUrl()
    {
        $isTestMode = Mage::helper('cybersource_core')->getIsTestMode();

        $mobileCreateTokenUrl = $isTestMode
            ? Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CREATE_TOKEN_MOBILE_TESTURL
            : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CREATE_TOKEN_MOBILE_LIVEURL;

        $mobilePayUrl = $isTestMode
            ? Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::MOBILE_TESTURL
            : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::MOBILE_LIVEURL;

        $sopPayUrl = $isTestMode
            ? Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::TESTURL
            : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::LIVEURL;

        $sopCreateTokenUrl = $isTestMode
            ? Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CREATE_TOKEN_TESTURL
            : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::CREATE_TOKEN_LIVEURL;

        if ($this->useSoapForTransactions()) {
            return $this->isMobile() ? $mobileCreateTokenUrl : $sopCreateTokenUrl;
        }

        return $this->isMobile() ? $mobilePayUrl : $sopPayUrl;
    }

    public function isValidToken($tokenId)
    {
        if (! $tokenId) {
            return false;
        }

        $tokenModel = Mage::getModel('cybersourcesop/token')->load($tokenId,'token_id');
        if (! $tokenModel->getId()) {
            return false;
        }

        if (Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($tokenModel->getCustomerId() != $customer->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @param array|string $message
     * @param bool $force
     * @return $this
     */
    public function log($message, $force = false)
    {
        if (!$this->isDebugMode() && !$force) {
            return $this;
        }

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        Mage::log($message, null, self::LOGFILE, $force);

        return $this;
    }

    public function useSoapForTransactions()
    {
        return (bool) Mage::getStoreConfig('payment/cybersourcesop/usesoap');
    }

    public function isDebugMode()
    {
        return (bool) Mage::getStoreConfig('payment/cybersourcesop/debug');
    }

    public function isPaEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/cybersource3ds/active');
    }

    public function getPaOrgId()
    {
        return Mage::getStoreConfig('payment/cybersource3ds/org_unit_id');
    }

    public function getPaApiId()
    {
        return Mage::getStoreConfig('payment/cybersource3ds/api_id');
    }

    public function getPaApiKey()
    {
        return Mage::getStoreConfig('payment/cybersource3ds/api_key');
    }
}
