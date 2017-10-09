<?php

/**
 * Class Globale_Browsing_Block_Checkout_OnpageLink
 */
class Globale_Browsing_Block_Rewrite_Checkout_Onepage_Link extends Mage_Checkout_Block_Onepage_Link {

    /**
     * Get Globale Checkout URL (Rewrite "Proceed to Checkout" Link)
     * @return string
     */
    public function getCheckoutUrl()
    {
        $GeLink = Mage::helper('globale_browsing/checkout')->getBrowsingCheckoutUrl();
        if($GeLink){
            $Link = $GeLink;
        }else {
            $Link = parent::getCheckoutUrl();
        }

        /*$session = Mage::getSingleton('customer/session');
        $session->setBeforeAuthUrl($url);
        // redirect to login
        $this->_redirect('customer/account/login');*/

        return $Link;
    }

}