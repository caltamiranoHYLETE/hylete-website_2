<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_ApplepayController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $this->_forward('defaultNoRoute');
    }

    /**
     * AJAX controller to fetch shipping rates for current quote, using address info passed in.
     * Accepts and returns JSON.
     *
     * @return $this
     */
    public function onshippingcontactselectedAction()
    {
        /** @var SubscribePro_Autoship_Helper_Applepay $applePayHelper */
        $applePayHelper = Mage::helper('autoship/applepay');

        // Get JSON POST
        $postData = json_decode($this->getRequest()->getRawBody(), true);
        if (!isset($postData['shippingContact'])) {
            Mage::throwException('Invalid request!');
        }

        // Pass over the shipping destination
        $applePayHelper->setApplePayShippingContactOnQuote($postData['shippingContact']);

        // Retrieve the shipping rates available for this quote
        $applePayShippingMethods = $applePayHelper->getApplePayShippingMethods();

        // Set shipping method on quote if none already set and if some rates are available
        if (!strlen($applePayHelper->getQuote()->getShippingAddress()->getShippingMethod())) {
            if (count($applePayShippingMethods)) {
                $applePayHelper->setApplePayShippingMethodOnQuote($applePayShippingMethods[0]);
            }
        }

        // Build up our response
        $response = [
            'newShippingMethods' => $applePayShippingMethods,
            'newTotal' => $applePayHelper->getApplePayTotal(),
            'newLineItems' => $applePayHelper->getApplePayLineItems(),
        ];

        // Return JSON response
        $this->getResponse()->setBody(json_encode($response, JSON_PRETTY_PRINT));
        $this->getResponse()->setHeader('Content-type', 'application/json');

        return $this;
    }

    /**
     * AJAX controller to fetch update quote after user selects new shipping method on payment sheet.
     * Accepts and returns JSON.
     *
     * @return $this
     */
    public function onshippingmethodselectedAction()
    {
        /** @var SubscribePro_Autoship_Helper_Applepay $applePayHelper */
        $applePayHelper = Mage::helper('autoship/applepay');

        // Get JSON POST
        $postData = json_decode($this->getRequest()->getRawBody(), true);
        if (!isset($postData['shippingMethod'])) {
            Mage::throwException('Invalid request!');
        }

        // Set shipping method selection
        $applePayHelper->setApplePayShippingMethodOnQuote($postData['shippingMethod']);

        // Build up our response
        $response = [
            'newTotal' => $applePayHelper->getApplePayTotal(),
            'newLineItems' => $applePayHelper->getApplePayLineItems(),
        ];

        // Return JSON response
        $this->getResponse()->setBody(json_encode($response, JSON_PRETTY_PRINT));
        $this->getResponse()->setHeader('Content-type', 'application/json');

        return $this;
    }

    /**
     * AJAX controller to complete Apple Pay payment and Magento order
     * Accepts and returns JSON.
     *
     * @return $this
     */
    public function onpaymentauthorizedAction()
    {
        try {
            /** @var SubscribePro_Autoship_Helper_Applepay $applePayHelper */
            $applePayHelper = Mage::helper('autoship/applepay');

            // Get JSON POST
            $postData = json_decode($this->getRequest()->getRawBody(), true);
            if (!isset($postData['payment'])) {
                Mage::throwException('Invalid request!');
            }

            // Complete the Magento order from Apple Pay data
            $applePayHelper->setApplePayPaymentOnQuote($postData['payment']);
            $applePayHelper->placeOrder();

            // Build up our response
            $response = [
                'redirectUrl' => Mage::getUrl('checkout/onepage/success'),
            ];

            // Return JSON response
            $jsonResponseBody = json_encode($response, JSON_PRETTY_PRINT);
            $this->getResponse()->setBody($jsonResponseBody);
            $this->getResponse()->setHeader('Content-type', 'application/json');
        }
        catch (\Exception $e) {
            SubscribePro_Autoship::logException($e);
            // Return JSON error response
            $response = [
                'status' => '500',
                'errorMessage' => 'Failed to complete order!',
            ];
            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody(json_encode($response, JSON_PRETTY_PRINT));
            $this->getResponse()->setHeader('Content-type', 'application/json');
        }

        return $this;
    }

}
