<?php

class Cryozonic_StripeExpress_Block_Button extends Mage_Core_Block_Template
{
    /**
     * @var Cryozonic_Stripe_Model_Standard
     */
    public $stripe;

    protected function _construct()
    {
        parent::_construct();

        $this->stripe = $this->helper('cryozonic_stripeexpress')->getStripe();
    }

    /**
     * Check Is Block enabled
     * @return bool
     */
    public function isEnabled($location)
    {
        return Mage::helper('cryozonic_stripeexpress')->isEnabled($location);
    }

    /**
     * Get Publishable Key
     * @return string
     */
    public function getPublishableKey()
    {
        $mode = $this->stripe->store->getConfig('payment/cryozonic_stripe/stripe_mode');
        $path = "payment/cryozonic_stripe/stripe_{$mode}_pk";
        return trim($this->stripe->store->getConfig($path));
    }

    /**
     * Get Quote
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        return $quote;
    }

    /**
     * Get Country Code
     * @return string
     */
    public function getCountry()
    {
        $countryCode = $this->getQuote()->getBillingAddress()->getCountryId();
        if (empty($countryCode)) {
            $countryCode = Mage::helper('core')->getDefaultCountry();
        }
        return $countryCode;
    }

    /**
     * Get Label
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('cryozonic_stripeexpress')->getLabel($this->getQuote());
    }

    /**
     * Get Button Config
     * @return array
     */
    public function getButtonConfig()
    {
        return array(
            'type' => Mage::getStoreConfig('payment/cryozonic_stripeexpress/button_type'),
            'theme' => Mage::getStoreConfig('payment/cryozonic_stripeexpress/button_theme'),
            'height' => Mage::getStoreConfig('payment/cryozonic_stripeexpress/button_height')
        );
    }

    /**
     * Get Payment Request Params
     * @return array
     */
    public function getApplePayParams()
    {
        return array_merge(
            array(
                'country' => $this->getCountry(),
                'requestPayerName' => true,
                'requestPayerEmail' => true,
                'requestPayerPhone' => true,
                'requestShipping' => !$this->getQuote()->isVirtual(),
            ),
            Mage::helper('cryozonic_stripeexpress')->getCartItems($this->getQuote())
        );
    }

    /**
     * Get Payment Request Params for Single Product
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getProductApplePayParams()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product');
        if (!$product) {
            return array();
        }

        $currency = $this->getQuote()->getQuoteCurrencyCode();
        if (empty($currency)) {
            $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        }

        /** @var Cryozonic_StripeExpress_Helper_Data $helper */
        $helper = Mage::helper('cryozonic_stripeexpress');

        // Get Current Items in Cart
        $params = $helper->getCartItems($this->getQuote());
        $amount = $params['total']['amount'];
        $items = $params['displayItems'];

        /** @var Mage_Tax_Helper_Data $taxHelper */
        $taxHelper = Mage::helper('tax');

        $shouldInclTax = $helper->shouldCartPriceInclTax($this->getQuote()->getStore());
        if (Mage::getStoreConfig('payment/cryozonic_stripe/use_store_currency')) {
            $_convertedFinalPrice = $product->getStore()->roundPrice($product->getStore()->convertPrice($product->getFinalPrice()));
            $price = $taxHelper->getPrice($product, $_convertedFinalPrice, $shouldInclTax);
        } else {
            $price = $taxHelper->getPrice($product, $product->getFinalPrice(), $shouldInclTax);
        }

        // Append Current Product
        $productTotal = $helper->getAmountCents($price, $currency);
        $amount += $productTotal;

        $items[] = array(
            'label' => $product->getName(),
            'amount' => $productTotal,
            'pending' => false
        );

        // Failcase for grouped products with an initial price of $0
        if ($amount <= 0)
            $amount += 1;

        return array(
            'country' => $this->getCountry(),
            'currency' => strtolower($currency),
            'total' => array(
                'label' => $this->getLabel(),
                'amount' => $amount,
                'pending' => true
            ),
            'displayItems' => $items,
            'requestPayerName' => true,
            'requestPayerEmail' => true,
            'requestPayerPhone' => true,
            'requestShipping' => $this->shouldRequestShipping($product),
        );
    }

    public function shouldRequestShipping($product)
    {
        // If this is not a virtual product, ask or shipping details
        if (!$product->getIsVirtual())
            return true;

        // Otherwise, assuming that there are more items in the quote, ensure that all of them are virtual
        foreach ($this->getQuote()->getAllItems() as $quoteItem)
        {
            if (!$quoteItem->getIsVirtual())
                return true;
        }

        return false;
    }
}
