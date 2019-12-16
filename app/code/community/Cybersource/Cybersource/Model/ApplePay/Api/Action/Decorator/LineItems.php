<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_LineItems
{
    /**
     * Are we taxing shipping?
     *
     * @var bool
     */
    private $taxShippingEnabled = false;

    /**
     * What is the tax shipping SKU?
     *
     * @var null|string
     */
    private $taxShippingSKU = null;

    /**
     * What is the shipping product code?
     *
     * @var null|string
     */
    private $taxShippingProductCode = null;

    /**
     * Local cache of tax classes
     *
     * @var array
     */
    private $taxClasses = array();

    /**
     * Build the line items to match the complexType name="Item" at:
     * https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.115.xsd
     *
     * @param Mage_Core_Model_Abstract $entity
     * @return array
     * @throws Exception If the object provided does not match an expected type
     */
    public function decorate(Mage_Core_Model_Abstract $entity, stdClass $payload)
    {
        if (!$entity instanceof Mage_Sales_Model_Quote && !$entity instanceof Mage_Sales_Model_Order) {
            Mage::throwException($this->getHelper()->__('Invalid model type provided to decorator'));
        }
        $items = $entity->getAllVisibleItems();
        $buyerVat = null;
        $customer = $entity->getCustomer();
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $buyerVat = $customer->getTaxvat();
        }

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
            $lineItem->currency = $entity->getStoreCurrencyCode();
            if ($buyerVat) {
                $lineItem->buyerRegistration = $buyerVat;
            }

            $lineItems[] = $lineItem;
        }
        if ($this->taxShippingEnabled) {
            $lineItem = $this->buildShippingLineItem($entity, $buyerVat);
            // May not create a line item if, for example, there is no shipping method selected
            if ($lineItem) {
                $lineItems[] = $lineItem;
            }
        }
        $payload->item = $lineItems;
    }

    /**
     * Render the shipping line item
     *
     * @param Mage_Core_Model_Abstract $quote
     * @param $buyerVat
     * @return null|stdClass
     * @throws Exception When tax shipping information is not available
     */
    public function buildShippingLineItem(Mage_Core_Model_Abstract $quote, $buyerVat)
    {
        if ($this->taxShippingEnabled && ($quote instanceof Mage_Sales_Model_Quote || $quote instanceof Mage_Sales_Model_Order)) {
            if (!$this->taxShippingSKU || !$this->taxShippingProductCode) {
                Mage::throwException($this->getHelper()->__('Missing critical tax shipping information'));
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
    public function getTaxCode(Mage_Catalog_Model_Product $product)
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
     * Setter for the tax shipping settings.
     *
     * @param $taxShippingEnabled
     * @param $taxShippingSKU
     * @param $taxShippingProductCode
     */
    public function configureTaxShipping($taxShippingEnabled, $taxShippingSKU, $taxShippingProductCode)
    {
        $this->taxShippingEnabled = $taxShippingEnabled;
        $this->taxShippingSKU = $taxShippingSKU;
        $this->taxShippingProductCode = $taxShippingProductCode;
    }

    /**
     * @return Cybersource_Cybersource_Helper_ApplePay_Data
     */
    public function getHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME);
    }
}
