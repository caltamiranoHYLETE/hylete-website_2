<?php

/**
 * Helper for remove the paypal templates from product catalog/details page
 * Class Globale_Browsing_Helper_Catalog
 */
class Globale_Browsing_Helper_Catalog extends Mage_Core_Helper_Abstract{

    /**
     * Remove the add to cart paypal button below the add to cart button
     * @param string $CurrentTemplate The current template file name from layout
     * @return bool|string $Template
     */
    public function unsetTemplateAddToCartPaypal($CurrentTemplate) {

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        if($IsOperatedByGlobale){
            $Template = $CurrentTemplate;
        }else{
            $Template = false;
        }
        return $Template;
    }
}