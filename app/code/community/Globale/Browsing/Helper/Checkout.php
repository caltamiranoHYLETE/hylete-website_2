<?php

/**
 * Helper for set Global-e templates and remove the paypal templates
 * Class Globale_Browsing_Helper_Checkout
 */
class Globale_Browsing_Helper_Checkout extends Mage_Core_Helper_Abstract{

    /**
     * Change the checkout cart total template with the Global-e totals template
     * @param string $CurrentTemplate The current template file name from layout
     * @param string $GlobaleTemplate Globale template file name from layout
     * @return string $Template
     */
    public function setTemplateCartTotal($CurrentTemplate, $GlobaleTemplate) {

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        if($IsOperatedByGlobale){
            $Template = $GlobaleTemplate;
        }else{
            $Template = $CurrentTemplate;
        }
        return $Template;
    }

    /**
     * Remove the top paypal button above the checkout cart total
     * @param string $CurrentTemplate The current template name from layout
     * @return bool|string $Template
     */
    public function unsetTemplateCartPaypalTop($CurrentTemplate) {

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        if($IsOperatedByGlobale){
            $Template =  $CurrentTemplate;
        }else{
            $Template = false;
        }
        return $Template;
    }

    /**
     * Remove the bottom paypal button below the checkout cart total
     * @param string $CurrentTemplate The current template name from layout
     * @return bool|string $Template
     */
    public function unsetTemplateCartPaypalBottom($CurrentTemplate) {

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        if($IsOperatedByGlobale){
            $Template =  $CurrentTemplate;
        }else{
            $Template = false;
        }
        return $Template;
    }

    /**
     * Remove the paypal button below the checkout mini cart
     * @param string $CurrentTemplate The current template name from layout
     * @return bool|string $Template
     */
    public function unsetTemplateMinicartPaypal($CurrentTemplate) {

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        if($IsOperatedByGlobale){
            $Template =  $CurrentTemplate;
        }else{
            $Template = false;
        }
        return $Template;
    }

    /**
     * Remove the estimate shipping and tax block in the checkout page
     * @param string $CurrentTemplate The current template name from layout
     * @return bool|string $Template
     */
    public function unsetTemplateCartShipping($CurrentTemplate) {

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        if($IsOperatedByGlobale){
            $Template =  $CurrentTemplate;
        }else{
            $Template = false;
        }
        return $Template;

    }

    /**
     * Get the Global-e checkout page url for Global-e supported users,
     * or the login/register page before redirect to Global-e checkout page if configured by Global-e settings
     * @return bool|string
     */
    public function getBrowsingCheckoutUrl(){

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        if($IsOperatedByGlobale){
            // get the frontName for Browsing module from the config.xml
            // @TODO: change getGlobaleCheckoutPageURL() to Magento basic getUrl()
            return $this->getGlobaleCheckoutPageURL();
        }else {
            return false;
        }
    }

    public function getClearCart(){
        //get the frontName for Browsing module from the config.xml
        $FrontName = (string)Mage::getConfig()->getNode('frontend/routers/browsing/args/frontName');
        return Mage::getUrl("{$FrontName}/cart/clear", array('_secure' => true));
    }

    /**
     * Get the Global-e checkout page URL from the config.xml
     * @return string
     */
    public function getGlobaleCheckoutPageURL() {

        $FrontName = (string)Mage::getConfig()->getNode('frontend/routers/browsing/args/frontName');
        return Mage::getUrl("{$FrontName}/checkout", array('_secure' => true));
    }
}