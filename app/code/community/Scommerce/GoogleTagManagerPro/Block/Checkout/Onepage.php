<?php
/**
 *
 * @category    Scommerce
 * @package     Scommerce_GoogleTagManagerPro
 * @author		Scommerce Mage (core@scommerce-mage.co.uk)
 */
class Scommerce_GoogleTagManagerPro_Block_Checkout_Onepage extends Mage_Core_Block_Template
{
    public function getCartItems()
    {
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        $cartItems = $quote->getAllVisibleItems();

        return $cartItems;
    }
}