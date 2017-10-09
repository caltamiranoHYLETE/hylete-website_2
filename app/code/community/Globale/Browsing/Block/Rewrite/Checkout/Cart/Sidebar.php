<?php

/**
 * Class Globale_Browsing_Block_Checkout_OnpageLink
 */
class Globale_Browsing_Block_Rewrite_Checkout_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar {

    /**
     * Get Globale Checkout URL (Rewrite "Proceed to Checkout" Link)
     * @return string
     */
    public function getCheckoutUrl()
    {
        /** @var $GeLink Globale_Browsing_Helper_Checkout */
        $GeLink = Mage::helper('globale_browsing/checkout')->getBrowsingCheckoutUrl();
        if($GeLink){
            $Link = $GeLink;
        }else {
            $Link = parent::getCheckoutUrl();
        }
        return $Link;
    }

}