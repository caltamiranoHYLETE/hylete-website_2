<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Adapter
{
    const XML_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    const ADDRESS_TYPE_BILLTO = 'billTo';
    const ADDRESS_TYPE_SHIPTO = 'shipTo';

    const LOG_TYPE_REQUEST = 'request';
    const LOG_TYPE_RESPONSE = 'response';
    const LOG_TYPE_ERROR = 'error';

    const TAX_DEFAULT_CODE = 'cybersourcedefaulttax';

    private $client;
    private $merchantId;
    private $transactionKey;
    private $wsdl;
    private $result;
    private $taxClasses = array();
    private $nexus = array();
    private $shipFrom = array();
    private $acceptance = array();
    private $origin = array();
    private $vat;

    private $taxShippingEnabled = false;
    private $taxShippingSKU = null;
    private $taxShippingProductCode = null;

    /**
     * @param $merchantId
     * @param $transactionKey
     * @param $wsdl
     */
    public function __construct($merchantId, $transactionKey, $wsdl, SoapClient $client = null)
    {
        $this->merchantId = $merchantId;
        $this->transactionKey = $transactionKey;
        $this->wsdl = $wsdl;
        $this->client = $client;
    }

    /**
     * Set the nexus regions to pass along to the Cybersource adapter.  Must be in ISO2 format
     *
     * @param array $regions
     */
    public function setNexusRegions(array $regions)
    {
        $this->nexus = $regions;
    }

    /**
     * Resets the nexus regions
     */
    public function resetNexusRegions()
    {
        $this->nexus = array();
    }

    /**
     * Set the Vat for the merchant
     *
     * @param $vat
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    }

    /**
     * Set ship-from information of the merchant to pass along to Cybersource
     *
     * @param $city
     * @param $postCode
     * @param $region
     * @param $country
     */
    public function setShipFrom($city, $postCode, $region, $country)
    {
        $this->shipFrom = array(
            'city' => $city,
            'postalCode' => $postCode,
            'region' => $region,
            'country' => $country,
        );
    }

    /**
     * Reset the ship-from information for the merchant.
     */
    public function resetShipFrom()
    {
        $this->shipFrom = array();
    }

    public function configureTaxShipping($taxShippingEnabled, $taxShippingSKU, $taxShippingProductCode)
    {
        $this->taxShippingEnabled = $taxShippingEnabled;
        $this->taxShippingSKU = $taxShippingSKU;
        $this->taxShippingProductCode = $taxShippingProductCode;
    }

    /**
     * Set the acceptance address for the merchant.
     *
     * @param $city
     * @param $postCode
     * @param $region
     * @param $country
     */
    public function setAcceptance($city, $postCode, $region, $country)
    {
        $this->acceptance = array(
            'city' => $city,
            'postalCode' => $postCode,
            'region' => $region,
            'country' => $country,
        );
    }

    /**
     * Reset the acceptance address for the merchant.
     */
    public function resetAcceptance()
    {
        $this->acceptance = array();
    }

    /**
     * Set the origin information for the merchant
     *
     * @param $city
     * @param $postCode
     * @param $region
     * @param $country
     */
    public function setOrigin($city, $postCode, $region, $country)
    {
        $this->origin = array(
            'city' => $city,
            'postalCode' => $postCode,
            'region' => $region,
            'country' => $country,
        );
    }

    /**
     * Reset the origin information for the merchant.
     */
    public function resetOrigin()
    {
        $this->origin = array();
    }

    /**
     *
     * @param $quote Mage_Sales_Model_Quote The quote for whose tax must be calculated
     * @return Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Result
     */
    public function send(Mage_Sales_Model_Quote $quote)
    {
        Mage::dispatchEvent(
            'cybersource_taxservices_validate_before',
            array(
                'quote' => $quote,
                'adapter' => $this
            )
        );

        // Return an empty result object
        if (!$this->isValidAddress($quote->getShippingAddress())) {
            return $this->getResultObject();
        }
        Mage::dispatchEvent(
            'cybersource_taxservices_build_before',
            array(
                'quote' => $quote,
                'adapter' => $this
            )
        );

        $requestIdentifier = uniqid('', true); // used so you can easily grep log files for related requests
        $payload = $this->buildRequest($quote);
        $this->logPayload($requestIdentifier, self::LOG_TYPE_REQUEST, $payload);

        try {
            Mage::dispatchEvent(
                'cybersource_taxservices_transaction_before',
                array(
                    'quote' => $quote,
                    'adapter' => $this,
                    'payload' => $payload
                )
            );

            // Make the Soap call
            $result = $this->getClient()->runTransaction($payload);

            // Log the result
            $this->logPayload($requestIdentifier, self::LOG_TYPE_RESPONSE, $result);

            // Set the result in the result object
            $this->getResultObject()->setApiResult($result);

            Mage::dispatchEvent(
                'cybersource_taxservices_transaction_success',
                array(
                    'quote' => $quote,
                    'adapter' => $this,
                    'result' => $result
                )
            );
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $client = $this->getClient();
            $headers = $client->__getLastRequestHeaders();
            $response = $client->__getLastRequest();
            $this->logPayload($requestIdentifier, self::LOG_TYPE_ERROR,
                array(
                    'message' => $message,
                    'headers' => $headers,
                    'response' => $response
                )
            );
            Mage::dispatchEvent(
                'cybersource_taxservices_transaction_failure',
                array(
                    'quote' => $quote,
                    'adapter' => $this,
                    'exception' => $e
                )
            );

        }
        return $this->getResultObject();
    }

    /**
     * Log the payload, json_encoding() it
     *
     * @param $requestIdentifier
     * @param $type
     * @param $payload
     */
    protected function logPayload($requestIdentifier, $type, $payload)
    {
        $result = json_encode($payload);
        $logEntry = sprintf('%s - %s: %s', $requestIdentifier, $type, $result);
        Mage::log($logEntry, Zend_Log::NOTICE, 'cybs_tax.log');
    }

    /**
     * Retrieves the result object
     *
     * @return Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Result
     */
    public function getResultObject()
    {
        if (!$this->result instanceof Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Result) {
            $this->result = new Cybersource_Cybersource_Model_TaxServices_Api_Calculator_Result();
        }
        return $this->result;
    }

    /**
     * Does the adapter consider the address valid?
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return bool
     */
    public function isValidAddress(Mage_Sales_Model_Quote_Address $address)
    {
        return $address->hasCountryId()
            && $address->hasRegion()
            && $address->hasCity()
            && $address->hasPostcode();
    }

    /**
     * Builds payload that will be sent.  By default the request will follow:
     * https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.115.xsd
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return stdClass
     */
    protected function buildRequest(Mage_Sales_Model_Quote $quote)
    {
        $payload = new stdClass();
        $payload->partnerSolutionID = '3IM74O3P';

        // Add the addresses, both shipping and billing
        if ($this->isValidAddress($quote->getBillingAddress())) {
            $payload->billTo = $this->buildAddress($quote->getBillingAddress());
        } else {
            $payload->billTo = $this->buildAddress($quote->getShippingAddress());
        }
        if ($this->isValidAddress($quote->getShippingAddress())) {
            $payload->shipTo = $this->buildAddress($quote->getShippingAddress());
        }

        // Build each line item
        $payload->item = $this->buildLineItems($quote);

        if ($this->shipFrom) {
            $shipFrom = new stdClass();
            $shipFrom->city = $this->shipFrom['city'];
            $shipFrom->postalCode = $this->shipFrom['postalCode'];
            $shipFrom->state = $this->shipFrom['region'];
            $shipFrom->country = $this->shipFrom['country'];
            $payload->shipFrom = $shipFrom;
        }

        $payload->taxService = $this->buildTaxService($quote);

        // Set the merchant ID
        $payload->merchantID = $this->merchantId;

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $quote->getStoreCurrencyCode();
        $payload->purchaseTotals = $purchaseTotals;

        // Ensure that the quote reserves an order ID so there is easy tracking between Magento and Cybersource's UI
        $quote->reserveOrderId();
        $payload->merchantReferenceCode = $quote->getReservedOrderId();

        return $payload;
    }

    protected function buildTaxService(Mage_Sales_Model_Quote $quote)
    {
        // Create the tax service node.
        $taxService = new stdClass();
        $taxService->run = 'true';
        if ($this->vat) {
            $taxService->sellerRegistration = $this->vat;
        }
        if ($this->nexus) {
            $taxService->nexus = implode(' ', $this->nexus);
        }
        if ($quote->getShippingAddress()->getVatId()) {
            $taxService->buyerRegistration = $quote->getShippingAddress()->getVatId();
        }
        if ($this->origin) {
            $taxService->orderOriginCity = $this->origin['city'];
            $taxService->orderOriginPostalCode = $this->origin['postalCode'];
            $taxService->orderOriginState = $this->origin['region'];
            $taxService->orderOriginCountry = $this->origin['country'];
        }
        if ($this->acceptance) {
            $taxService->orderAcceptanceCity = $this->acceptance['city'];
            $taxService->orderAcceptancePostalCode = $this->acceptance['postalCode'];
            $taxService->orderAcceptanceState = $this->acceptance['region'];
            $taxService->orderAcceptanceCountry = $this->acceptance['country'];
        }
        return $taxService;
    }


    /**
     * Build the line items to match the complexType name="Item" at:
     * https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.115.xsd
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    protected function buildLineItems(Mage_Sales_Model_Quote $quote)
    {
        $items = $quote->getAllVisibleItems();
        $buyerVat = $quote->getCustomer()->getTaxvat();
        $lineItems = array();
        $taxConfig = Mage::helper('tax')->getConfig();
        /** @var $taxConfig Mage_Tax_Model_Config */
        $taxAfterDiscount = $taxConfig->applyTaxAfterDiscount();

        foreach ($items as $i => $item) {
            /** @var $item Mage_Sales_Model_Quote_Item */
            $lineItem = new \stdClass();
            $quantity = (int)$item->getQty();
            $unitPrice = (float)$item->getBasePrice();
            if ($taxAfterDiscount) {
                $product = $item->getProduct();
                $unitPrice = $product->getPriceModel()->getBasePrice($product, $quantity);
            }

            $sku = $item->getProduct()->getSku();
            $productName = $item->getProduct()->getName();

            $taxCode = $this->getTaxCode($item->getProduct());

            $lineItem->id = $i;
            $lineItem->unitPrice = $unitPrice;
            $lineItem->quantity = (string)$quantity;
            $lineItem->productCode = $taxCode;
            $lineItem->productName = $productName;
            $lineItem->productSKU = $sku;
            $lineItem->currency = $item->getQuote()->getBaseCurrencyCode();
            if ($buyerVat) {
                $lineItem->buyerRegistration = $buyerVat;
            }

            $lineItems[] = $lineItem;
        }
        if ($this->taxShippingEnabled) {
            $lineItem = $this->buildShippingLineItem($quote, $buyerVat);
            // May not create a line item if, for example, there is no shipping method selected
            if ($lineItem) {
                $lineItems[] = $lineItem;
            }
        }
        return $lineItems;
    }

    protected function buildShippingLineItem(Mage_Sales_Model_Quote $quote, $buyerVat)
    {
        if ($this->taxShippingEnabled) {
            if (!$this->taxShippingSKU || !$this->taxShippingProductCode) {
                Mage::throwException('Missing critical tax shipping information');
            }
            $shippingAmount = $quote->getShippingAddress()->getShippingAmount();

            // No need to calculate tax on a zero amount
            if (!$shippingAmount) {
                return null;
            }
            $lineItem = new \stdClass();
            $lineItem->id = $quote->getItemsCount() + 1;
            $lineItem->unitPrice = $shippingAmount;
            $lineItem->quantity = 1;
            $lineItem->productCode = $this->taxShippingProductCode;
            $lineItem->productName = $quote->getShippingAddress()->getShippingMethod();
            $lineItem->productSKU = $this->taxShippingSKU;
            $lineItem->currency = $quote->getBaseCurrencyCode();
            if ($buyerVat) {
                $lineItem->buyerRegistration = $buyerVat;
            }
            return $lineItem;
        }
        return null;
    }

    /**
     * Get the Cybersource tax code from Magento, if set, otherwise set the default tax code from Magento.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    protected function getTaxCode(Mage_Catalog_Model_Product $product)
    {
        if (!isset($this->taxClasses[$product->getTaxClassId()])) {
            $taxClass = Mage::getModel('tax/class')->load($product->getTaxClassId());
            $taxClassName = $taxClass->getClassName();
            // Default to the core Magento name, but use the Cybersource tax code if it's available
            if ($taxClass->getCsTaxCode()) {
                $taxClassName = $taxClass->getCsTaxCode();
            }
            $this->taxClasses[$product->getTaxClassId()] = $taxClassName;
        }
        return $this->taxClasses[$product->getTaxClassId()];
    }

    /**
     * Build an address node
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return stdClass
     */
    protected function buildAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $optionalItems = array(
            'firstName' => 'getFirstname',
            'lastName' => 'getLastname',
            'company' => 'getCompany',
            'customerID' => 'getCustomerId',
            'phoneNumber' => 'getTelephone',
            'email' => 'getEmail',
            'street1' => 'getStreet1',
            'street2' => 'getStreet2',
        );

        $payload = new stdClass();
        $payload->city = $address->getCity();
        $payload->state = $address->hasRegionId() ? $address->getRegionCode() : $address->getRegion();
        $payload->postalCode = $address->getPostcode();
        $payload->country = $address->getCountryId();

        foreach ($optionalItems as $parameter => $method) {
            if ($address->$method()) {
                $payload->$parameter = $address->$method();
            }
        }

        return $payload;
    }

    /**
     * Return some sweet stream options
     *
     * @return array
     */
    protected function getStreamOptions()
    {
        return array(
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true
            ),
            'http' => array(
                'user_agent' => 'PHP/Magento 1 Soap Client'
            )
        );
    }

    /**
     * Create the Soap params
     *
     * @param $opts
     * @return array
     */
    protected function getSoapParams($opts)
    {
        return array(
            'encoding' => 'UTF-8',
            'verifypeer' => false,
            'verifyhost' => false,
            'soap_version' => SOAP_1_1,
            'trace' => 1,
            'exceptions' => 1,
            "connection_timeout" => 180,
            'stream_context' => stream_context_create($opts)
        );
    }

    /**
     * Create a Soap header based on the configuration provided.
     *
     * @param $payload
     * @param $type
     * @param null $nodeName
     * @return SoapVar
     */
    protected function buildSoapHeader($payload, $type, $nodeName = null)
    {
        return new \SoapVar(
            $payload,
            $type,
            null,
            self::XML_NAMESPACE,
            $nodeName,
            self::XML_NAMESPACE
        );
    }

    /**
     * Create a SoapClient.
     *
     * @return SoapClient
     * @throws Exception
     */
    protected function getClient()
    {
        if (!$this->client instanceof SoapClient) {
            $opts = $this->getStreamOptions();
            $params = $this->getSoapParams($opts);

            try {
                $client = new \SoapClient($this->wsdl, $params);

                $soapUsername = $this->buildSoapHeader($this->merchantId, XSD_STRING);
                $soapPassword = $this->buildSoapHeader($this->transactionKey, XSD_STRING);

                $auth = new \stdClass();
                $auth->Username = $soapUsername;
                $auth->Password = $soapPassword;

                $soapAuth = $this->buildSoapHeader($auth, SOAP_ENC_OBJECT, 'UsernameToken');

                $token = new \stdClass();
                $token->UsernameToken = $soapAuth;
                $soapToken = $this->buildSoapHeader($token, SOAP_ENC_OBJECT, 'UsernameToken');
                $security = $this->buildSoapHeader($soapToken, SOAP_ENC_OBJECT, 'Security');

                $header = new \SoapHeader(self::XML_NAMESPACE, 'Security', $security, true);
                $client->__setSoapHeaders(array($header));
                $this->client = $client;

            } catch (\Exception $e) {
                Mage::logException($e);
                throw $e;
            }
        }
        return $this->client;
    }

}
