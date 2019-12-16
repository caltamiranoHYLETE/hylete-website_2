<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_SOPWebMobile_Form_Payform extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
	{
		parent::_construct();

		$this->setMethod($this->getQuote()->getPayment()->getMethodInstance());

		if ($this->getMethod()->getCode() == Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck::CODE) {
		    $this->setTemplate('cybersourcesop/echeck.phtml');

            if (Mage::helper('cybersourcesop')->isMobile()) {
                $this->setTemplate('cybersourcesop/echeck_mobile.phtml');
            }
            return;
        }

        if (Mage::helper('cybersourcesop')->isMobile()) {
            $this->setTemplate('cybersourcesop/cc_mobile.phtml');
            return;
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
		    $this->setTemplate('cybersourcesop/cc.phtml');
        } else {
            $this->setTemplate('cybersourcesop/cc_guest.phtml');
        }
    }

    /**
     * Retrieves card regex
     * @return string
     */
    public function getCardsRegex()
    {
        $allcards = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getCCMap();
        $regexhtml = '';

        foreach ($allcards as $card) {
            $regexhtml .= sprintf("'%s': %s,", $card->cybercode, $card->regex);
        }

        return $regexhtml;
    }

    /**
     * checks if tokenisation is enabled in admin
     * @return bool
     */
    public function isTokenisationEnabled()
    {
        return (bool) Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('enable_tokenisation');
    }

    /**
     * gets tokens that belong to customer
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Resource_Token_Collection|bool
     */
    public function getCustomerTokens()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (! $customer->getId()) {
            return false;
        }

        return Mage::getModel('cybersourcesop/token')->getCollection()->addFieldToFilter('customer_id', $customer->getId());;
    }

    /**
     * @param string $cardType
     * @return string
     */
    public function getCardClass($cardType) {
        $type = '';
        switch ($cardType) {
            case 001 : $type = 'visa';
                       break;
            case 002 : $type = 'mastercard';
                       break;
            case 003 : $type = 'amex';
                       break;
            case 033 : $type = 'visa';
                       break;
            case 042 : $type = 'maestro';
                       break;
        }
        return $type;
    }

    /**
     * Formats the date.
     * @param $date
     * @return string
     */
    public function formatExpirationDate($date)
    {
        $year = substr($date,strlen($date)-2);
        $month =  substr($date,0,2);
        $result = $month . '/' . $year;
        return $result;
    }

    /**
     * Checks the status of payment method
     * @return bool
     */
    public function isRegistering()
    {
        return $this->getMethod()->getData('info_instance')->getQuote()->getCheckoutMethod(true) == 'register';
    }

    /**
     * Checks if the block content is to be used.
     * @return bool
     */
    public function useBlockContent()
    {
        return (bool) Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('use_block_content');
    }

    /**
     * @return string
     */
    public function getContentBlockId()
    {
        return Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig('block_content_id');
    }

    /**
     * @return array|bool
     */
    public function getEcheckFields()
    {
        $requestBuilder = Mage::getModel('cybersourcesop/sopwm_requestBuilder');
        return $requestBuilder->getEcheckFields();
	}

    /**
     * @return array|bool
     */
    public function getCcFields()
    {
        $requestBuilder = Mage::getModel('cybersourcesop/sopwm_requestBuilder');
        return $requestBuilder->getCcFields();
    }

    /**
     * @return string
     */
    public function getCybersourceUrl()
    {
        return Mage::helper('cybersourcesop')->getCybersourceUrl();
	}

    public function getCybersourceEcheckUrl()
    {
        $isTestMode = Mage::helper('cybersource_core')->getIsTestMode();

        return $isTestMode
            ? Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::TESTURL
            : Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::LIVEURL;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $allowedCodes = array(
            Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck::CODE,
            Cybersource_Cybersource_Model_SOPWebMobile_Payment_Cc::CODE
        );

        if (in_array($this->getMethod()->getCode(), $allowedCodes)) {
            return parent::_toHtml();
        }

        return '';
    }
}
