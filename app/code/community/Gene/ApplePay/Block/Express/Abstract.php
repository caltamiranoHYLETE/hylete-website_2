<?php

/**
 * Class Gene_ApplePay_Block_Express_Abstract
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_ApplePay_Block_Express_Abstract extends Mage_Core_Block_Template
{
    /**
     * Retrieve the current quote
     *
     * @return \Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Get the current product
     *
     * @return bool|Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Is the express mode enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (!Mage::helper('gene_applepay')->hasDependencies()) {
            return false;
        }

        if (Mage::getStoreConfig('payment/gene_braintree_applepay/active')
            && Mage::getStoreConfig('payment/gene_braintree_applepay/express_active')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Is Express enabled on product pages?
     *
     * @return bool
     */
    public function isEnabledPdp()
    {
        if ($this->isEnabled() && Mage::getStoreConfig('payment/gene_braintree_applepay/express_pdp')) {
            return true;
        }

        return false;
    }

    /**
     * Is express enabled in the cart?
     *
     * @return bool
     */
    public function isEnabledCart()
    {
        if ($this->isEnabled() && Mage::getStoreConfig('payment/gene_braintree_applepay/express_cart')) {
            return true;
        }

        return false;
    }
}
