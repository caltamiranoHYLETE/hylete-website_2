<?php

class Cryozonic_StripeExpress_Helper_Cart
{
    public $cart = null;
    public $helper = null;

    public function __construct()
    {
        $this->cart = Mage::getSingleton('checkout/cart');
        $this->helper = Mage::helper('cryozonic_stripeexpress');
    }

    public function addToCart($product, $params)
    {
        $cart = $this->cart;
        if (isset($params['qty'])) {
            $filter = new Zend_Filter_LocalizedToNormalized(
                array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );
            $params['qty'] = $filter->filter($params['qty']);
        }

        $related = $params['related_product'];

        /**
         * Check product availability
         */
        if (!$product)
        {
            throw new Exception("Cannot add the item to shopping cart.");
        }

        $cart->addProduct($product, $params);
        if (!empty($related)) {
            $cart->addProductsByIds(explode(',', $related));
        }

        $cart->save();

        $this->_getSession()->setCartWasUpdated(true);
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
