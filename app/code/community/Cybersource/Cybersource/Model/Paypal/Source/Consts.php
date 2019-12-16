<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Paypal_Source_Consts extends Varien_Object
{
    const STATUS_ACCEPT = 100;

    static function getSystemConfig($valin = false, $store = null)
    {
        if (! $store) {
            $store = Mage::app()->getStore()->getId();
        }

        if ($valin) {
            return Mage::getStoreConfig('payment/cybersourcepaypal/' . $valin, $store);
        }

        return Mage::getStoreConfig('payment/cybersourcepaypal', $store);
    }

    static function getErrorCode($codein)
    {
        //list of error responses here
        $errorArray = array(
            '150' => Mage::helper('cybersourcepaypal')->__('A general error has occurred'),
            '151' => Mage::helper('cybersourcepaypal')->__('The communication to your bank has failed, please try again later'),
            '152' => Mage::helper('cybersourcepaypal')->__('The communication to your bank has failed, please try again later'),
            '250' => Mage::helper('cybersourcepaypal')->__('The communication to your bank has failed, please try again later'),
            '203' => Mage::helper('cybersourcepaypal')->__('Sorry your card has been declined by your bank, please try a different card or check with your bank'),
            '201' => Mage::helper('cybersourcepaypal')->__('Your issuing bank has requested more information about the transaction, please contact them and try again'),
            '202' => Mage::helper('cybersourcepaypal')->__('Your credit card has expired, please enter a valid card'),
            '204' => Mage::helper('cybersourcepaypal')->__('You have insufficient funds on the account for this transaction'),
            '207' => Mage::helper('cybersourcepaypal')->__('Sorry we are unable to reach your bank to verify this transaction, please try again.'),
            '210' => Mage::helper('cybersourcepaypal')->__('Your card has reached its credit limit and this transaction cannot be processed'),
            '211' => Mage::helper('cybersourcepaypal')->__('Your CVN (3 digit code) is invalid, please amend and try again'),
            '230' => Mage::helper('cybersourcepaypal')->__('Your CVN (3 digit code) is invalid, please amend and try again'),
            '200' => Mage::helper('cybersourcepaypal')->__('Your Billing address does not match the one registered to that card, please amend your address and try again'),
            '476' => Mage::helper('cybersourcepaypal')->__('Your card failed the authentication process, please try again'),
            '481' => Mage::helper('cybersourcepaypal')->__('Your card failed the fraud screening process, please check the details or try a new card'),
            '102' => Mage::helper('cybersourcepaypal')->__('Your billing and/or shipping address details are invalid or missing.'),
            '101' => Mage::helper('cybersourcepaypal')->__('The request is missing one or more required fields.'),
            '223' => Mage::helper('cybersourcepaypal')->__('PayPal rejected the transaction.'),
            '233' => Mage::helper('cybersourcepaypal')->__('General decline by PayPal.'),
            '234' => Mage::helper('cybersourcepaypal')->__('There is a problem with the merchant configuration.'),
            '238' => Mage::helper('cybersourcepaypal')->__('PayPal rejected the transaction. A successful transaction was already completed for this paypalToken value.'),
            '387' => Mage::helper('cybersourcepaypal')->__('PayPal authorization failed.')
        );

        if (isset($errorArray[$codein])) {
            return $errorArray[$codein];
        }

        return 'Unknown error occured.';
    }
}
