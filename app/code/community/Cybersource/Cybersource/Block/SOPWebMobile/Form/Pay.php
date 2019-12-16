<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_SOPWebMobile_Form_Pay extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cybersourcesop/info.phtml');
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

    /**
     * Called from admin area only.
     * @return array
     */
    public function getCustomerTokens()
    {
        $quote = Mage::getSingleton('adminhtml/session_quote');
        if (! $quote->getCustomer()->getId()) {
            return array();
        }

        $collection = Mage::getModel('cybersourcesop/token')
            ->getCollection()
            ->addFieldToFilter('customer_id', $quote->getCustomer()->getId())->toArray();

        return $collection['items'];
    }
}
