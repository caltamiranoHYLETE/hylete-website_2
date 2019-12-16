<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Paypal_Soapapi_Abstract extends Mage_Core_Model_Abstract
{
    protected $_request;
    protected $_orderref = null;
    public $_storeId = null;

    protected function iniRequest($payment = null)
    {
        $this->_request = new stdClass();

        //if a payment has been passed in, it will contain a store that the payment was made from.
        if ($payment->getOrder()) {
            $this->_storeId = $payment->getOrder()->getStoreId();
        } elseif ($payment->getQuote()) {
            $this->_storeId = $payment->getQuote()->getStoreId();
        } else {
            $orderId = Mage::app()->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $this->_storeId = $order->getStoreId();
        }

        //set store ID in session for use with SOAP API Requests.
        Mage::getModel('core/session')->setSoapRequestStoreId($this->_storeId);
        $this->_request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $this->_request->merchantReferenceCode = $this->_generateReferenceCode();

        $this->_request->clientLibrary = "PHP";
        $this->_request->clientLibraryVersion = phpversion();
    }

    protected function assignOrderRef($refin)
    {
        if ($refin) {
            $this->_orderref = $refin;
        }
    }

    /**
     * @return Cybersource_Cybersource_Model_Core_Soap_Client
     */
    protected function getSoapClient()
    {
        $wsdlPath = Mage::helper('cybersource_core')->getWsdlUrl();
        $client = new Cybersource_Cybersource_Model_Core_Soap_Client($wsdlPath);

        $client->setContext(self::class);
        $client->setLogFilename(Cybersource_Cybersource_Helper_Paypal_Data::LOGFILE);
        $client->setPreventLogFlag(!Mage::helper('cybersourcepaypal')->isDebugMode());

        return $client;
    }

    /**
     * Generator for merchant reference code
     *
     * @return string number
     */
    protected function _generateReferenceCode()
    {
        //else use unique hash
        if (is_null($this->_orderref)) {
            return Mage::helper('core')->uniqHash();
        }

        return $this->_orderref;
    }


    //builds PayPal SetService request
    protected function buildPayPalSetServiceRequest($quote)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $payment = $quote->getPayment();

        $this->assignOrderRef($quote->getReservedOrderId());
        $this->iniRequest($payment);

        $payPalEcSetService = new stdClass();
        $payPalEcSetService->run = 'true';
        $payPalEcSetService->paypalReturn = Mage::getUrl('cybersource/paypal/return');
        $payPalEcSetService->paypalCancelReturn = Mage::getUrl('cybersource/paypal/cancel');
        $payPalEcSetService->paypalAddressOverride = 'true';
        $payPalEcSetService->paypalNoshipping = '1';

        $pullPayPalAddress = $this->getAddressToCyberSource($quote->getStoreId(), "");
        $payPalEcSetService->requestBillingAddress = ($pullPayPalAddress == Cybersource_Cybersource_Model_Paypal_Source_AddressDetails::PAYPAL_ADDRESS ? '1' : '0');// set to '1' to retrieve billing fields from paypal using payPalEcGetDetailsService

        $this->_request->payPalEcSetService = $payPalEcSetService;
        //line items
        //$this->addLineItems($quote->getAllVisibleItems());
        //purchase Totals
        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $quote->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = sprintf('%0.2f', $quote->getBaseGrandTotal());
        $this->_request->purchaseTotals = $purchaseTotals;
        return $this->_request->purchaseTotals;
    }

    //builds PayPal SetService request
    protected function buildPayPalGetDetailsRequest($quote, $paypalToken)
    {
        $payment = $quote->getPayment();
        $paymentInfo = $payment->getAdditionalInformation();

        $this->assignOrderRef($quote->getReservedOrderId());
        $this->iniRequest($quote);

        $this->_request = new stdClass();
        $this->_request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $this->_request->merchantReferenceCode = $paymentInfo['merchantReferenceCode'];
        $this->_request->clientLibrary = "PHP";
        $this->_request->clientLibraryVersion = phpversion();

        $payPalEcGetDetailsService = new stdClass();
        $payPalEcGetDetailsService->run = "true";
        $payPalEcGetDetailsService->paypalToken = $paypalToken;
        $payPalEcGetDetailsService->paypalEcSetRequestID = $paymentInfo['initRequestID'];
        $payPalEcGetDetailsService->paypalEcSetRequestToken = $paymentInfo['initRequestToken'];

        $this->_request->payPalEcGetDetailsService = $payPalEcGetDetailsService;

        return $this->_request->payPalEcGetDetailsService;
    }

    /**
     * Assigning billing address to soap
     *
     * @param Varien_Object $billing
     * @param String $email
     */
    protected function addBillingAddress($billing, $email)
    {
        if (!$email) {
            $email = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getEmail();
        }

        // Max character count for bill_to_company API request field is 40.
        $billToCompany = $billing->getCompany();

        if (strlen($billToCompany) > 40) {
            $billToCompany = substr($billToCompany, 0, 40);
        }

        $billTo = new stdClass();
        $billTo->firstName = $billing->getFirstname();
        $billTo->lastName = $billing->getLastname();
        $billTo->company = $billToCompany;
        $billTo->street1 = $billing->getStreet(1);
        $billTo->street2 = $billing->getStreet(2);
        $billTo->city = $billing->getCity();
        $billTo->state = $billing->getRegion();
        $billTo->postalCode = $billing->getPostcode();
        $billTo->country = $billing->getCountry();
        $billTo->phoneNumber = $billing->getTelephone();
        $billTo->email = ($email ? $email : Mage::getStoreConfig('trans_email/ident_general/email'));
        $billTo->ipAddress = Mage::app()->getRequest()->getClientIp(false);
        $billTo->customerID = $billing->getCustomerId();
        $this->_request->billTo = $billTo;
        return $this->_request->billTo;
    }

    /**
     * Assigning shipping address to soap object
     *
     * @param Varien_Object $shipping
     */
    protected function addShippingAddress($shipping)
    {
        //checking if we have shipping address, in case of virtual order we will not have it
        if ($shipping) {

            // Max character count for ship_to_company API request field is 40.
            $shipCompany = $shipping->getCompany();

            if (strlen($shipCompany) > 40) {
                $shipCompany = substr($shipCompany, 0, 40);
            }

            $shipTo = new stdClass();
            $shipTo->firstName = $shipping->getFirstname();
            $shipTo->lastName = $shipping->getLastname();
            $shipTo->company = $shipCompany;
            $shipTo->street1 = $shipping->getStreet(1);
            $shipTo->street2 = $shipping->getStreet(2);
            $shipTo->city = $shipping->getCity();
            $shipTo->state = $shipping->getRegion();
            $shipTo->postalCode = $shipping->getPostcode();
            $shipTo->country = $shipping->getCountry();
            $shipTo->phoneNumber = $shipping->getTelephone();
            $this->_request->shipTo = $shipTo;
            return $this->_request->shipTo;
        }
        return true;
    }

    protected function addLineItems($itemsCollection)
    {
        $i = 0;
        $items = array();

        foreach ($itemsCollection as $orderItem) {
            ${"item" . $i} = new stdClass();
            ${"item" . $i}->productCode = $i;
            ${"item" . $i}->unitPrice = number_format($orderItem->getPrice(), 2, '.', '');
            ${"item" . $i}->quantity = number_format($orderItem->getQtyOrdered(), 0, '.', '');
            ${"item" . $i}->productSKU = $orderItem->getSku();
            ${"item" . $i}->productName = $orderItem->getName();
            ${"item" . $i}->taxAmount = number_format($orderItem->getTaxAmount(), 2, '.', '');
            $items[] = ${"item" . $i};
            $i++;
        }
        //$this->_request->item = $items;
        return $items;
    }

    /*
     * @ TODO: refactor
     * Pulls system configuration values:
     *
     * @param String $storeId: store Id where the order is being placed.
     * @param String $addressToSend: system configuration whose value has to be pulled
     *
     * @return Cybersource_Cybersource_Model_Paypal_Source_AddressDetails $getPaypalAddress
     *
     */
    public function getAddressToCyberSource($storeID, $addressToSend = "")
    {
        if (strlen($addressToSend) < 5) {//check if the $addressToSend empty
            $billingAddress = Cybersource_Cybersource_Model_Paypal_Source_Consts::getSystemConfig('request_bill_address', $storeID);
            $shippingAddress = Cybersource_Cybersource_Model_Paypal_Source_Consts::getSystemConfig('request_shipping_address', $storeID);
            $getPaypalAddress = ($billingAddress == Cybersource_Cybersource_Model_Paypal_Source_AddressDetails::PAYPAL_ADDRESS
            || $shippingAddress == Cybersource_Cybersource_Model_Paypal_Source_AddressDetails::PAYPAL_ADDRESS ? '1' : '0');
        } else {
            $getPaypalAddress = Cybersource_Cybersource_Model_Paypal_Source_Consts::getSystemConfig($addressToSend, $storeID);
        }
        return $getPaypalAddress;
    }

    /**
     * Assigning PayPal address to soap object
     *
     * @param array $paypalInfo
     * @param Varien_Object $order
     * @param boolean $addressIsBilling
     *
     * @return stdClass $addressObject
     */
    protected function getPayPalAddressObject($paypalInfo, $order, $addressIsBilling)
    {
        $addressObject = new stdClass();
        $addressObject->firstName = $paypalInfo['ppPayerFirstname'];
        $addressObject->lastName = $paypalInfo['ppPlayerLastname'];

        if (array_key_exists('ppCompany', $paypalInfo)) {
            $addressObject->company = $paypalInfo['ppCompany'];
        }
        if (array_key_exists('ppStreet1', $paypalInfo)) {
            $addressObject->street1 = $paypalInfo['ppStreet1'];
        }
        if (array_key_exists('ppStreet2', $paypalInfo)) {
            $addressObject->street2 = $paypalInfo['ppStreet2'];
        }
        if (array_key_exists('ppCity', $paypalInfo)) {
            $addressObject->city = $paypalInfo['ppCity'];
        }
        if (array_key_exists('ppState', $paypalInfo)) {
            $addressObject->state = $paypalInfo['ppState'];
        }
        if (array_key_exists('ppPostcode', $paypalInfo)) {
            $addressObject->postalCode = $paypalInfo['ppPostcode'];
        }
        if (array_key_exists('ppCountry', $paypalInfo)) {
            $addressObject->country = $paypalInfo['ppCountry'];
        }
        if (array_key_exists('ppPhonenumber', $paypalInfo)) {
            $addressObject->country = $paypalInfo['ppPhonenumber'];
        }

        if ($addressIsBilling) {
            $addressObject->email = $paypalInfo['payerEmail'];
            $addressObject->ipAddress = Mage::app()->getRequest()->getClientIp(false);
            $addressObject->customerID = $order->getCustomerId();
        }

        return $addressObject;
    }

    /**
     * Assigning Magento address to soap object
     *
     * @param Varien_Object $magentoAddress
     * @param Varien_Object $order
     * @param boolean $addressIsBilling
     *
     * @return stdClass $addressObject
     */
    protected function getMagentoAddressObject($magentoAddress, $order, $addressIsBilling)
    {
        $addressObject = new stdClass();
        if ($magentoAddress->getData()) {
            $addressObject->firstName = $magentoAddress->getFirstname();
            $addressObject->lastName = $magentoAddress->getLastname();
            $addressObject->company = $magentoAddress->getCompany();
            $street = $magentoAddress->getStreet();
            $addressObject->street1 = $street[0];

            if (count($magentoAddress->getStreet()) > 1) {
                $addressObject->street2 = $street[1];
            }
            $addressObject->city = $magentoAddress->getCity();
            $region = Mage::getModel('directory/region')->loadByName($magentoAddress->getRegion(), $magentoAddress->getCountryId());
            $addressObject->state = ($region->getData() ? substr($region->getCode(), 0, 2) : null);// The length of the region code has to be 2.
            $addressObject->postalCode = $magentoAddress->getPostcode();
            $addressObject->country = $magentoAddress->getCountryId();
            $addressObject->phoneNumber = $magentoAddress->getTelephone();
            if ($addressIsBilling) {
                $addressObject->email = ($order->getCustomerEmail() ? $order->getCustomerEmail() : $magentoAddress->getEmail());
                $addressObject->ipAddress = Mage::app()->getRequest()->getClientIp(false);
                $addressObject->customerID = $order->getCustomerId();
            }
        }
        return $addressObject;
    }

}
