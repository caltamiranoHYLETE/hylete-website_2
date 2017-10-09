<?php

/**
 * Handle the Fixed price widget on the form event
 * Class Globale_FixedPrices_Model_Observers_Admin
 */
class Globale_FixedPrices_Model_Observers_Admin {

    /**
     * Add Custom field for "fixed prices" widget into the product block form Edit
     * EVENT ==> adminhtml_catalog_product_edit_prepare_form
     * @param Varien_Event_Observer $Observer
     * @return Varien_Data_Form $Form
     * @access public
     */
    public function addFixedPriceField(Varien_Event_Observer $Observer){

        /** @var Globale_FixedPrices_Model_Fixedprices $FixedPrices */
        $FixedPrices = Mage::getModel('globale_fixedprices/fixedprices');
        $Form = $FixedPrices->buildFieldWithFixedPricesWidget($Observer);
        return $Form;
    }

    /**
     * Update (insert/update/delete) fixed prices for product
     * EVENT ==> catalog_product_prepare_save
     * @param Varien_Event_Observer $Observer
     * @access public
     */
    public function updateFixedPrices(Varien_Event_Observer $Observer) {

        // update (insert/update/delete) product fixed prices in database
        /** @var Globale_FixedPrices_Model_Fixedprices $FixedPrices */
        $FixedPrices = Mage::getModel('globale_fixedprices/fixedprices');
        $FixedPrices->updateFixedPrices($Observer);
    }
}