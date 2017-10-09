<?php

use GlobalE\SDK\SDK;
use GlobalE\SDK\Models\Common\Response\Data;

/**
 * Collect all data for the fixed prices widget
 * @method setProductFixedPrices($Data)
 * @method setCurrencies($GECurrencies)
 * @method setCountries($GECountries)
 * Class Globale_FixedPrices_Block_Adminhtml_Catalog_Product_Edit_Tab_Price_FixedPrices
 */
class Globale_FixedPrices_Block_Adminhtml_Catalog_Product_Edit_Tab_Price_FixedPrices extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group_Abstract {

    /**
     * Initialize block
     * @param array $args
     */
    function __construct(array $args) {

        parent::__construct($args);
        $this->setTemplate('globale/fixed_prices.phtml');
        $this->initTemplateData();
    }

    /**
     * Check group price attribute scope is global
     * @return bool
     */
    public function isScopeGlobal(){
        return true;
    }

    /**
     * Init Countries, Currencies drop-down from SDK
     * and get fixed prices items for product, in order to display them in the widget template
     * @access protected
     */
    protected function initTemplateData() {

        // Init SDK OnPageLoad method in order to get all countries from SDK
        /** @var SDK $GlobaleSDK */
        $GlobaleSDK = Mage::registry('globale_sdk');
        $GlobaleSDK->Browsing()->OnPageLoad();

        // Init Countries/currencies for being display in drop-down
        $this->getAllCountries($GlobaleSDK);
        $this->getAllCurrencies($GlobaleSDK);
        $this->getFixedPricesForProduct();
    }

    /**
     * Collect all fixed prices items, in order to display them in the widget template
     * @access protected
     */
    protected function getFixedPricesForProduct() {

        $Product = $this->getProduct();
        if(!empty($Product)) {

            $Data = array();
            /** @var Globale_FixedPrices_Model_Fixedprices $Model */
            $FixedPrices = Mage::getModel('globale_fixedprices/fixedprices');
            /** @var  Globale_FixedPrices_Model_Resource_Fixedprices_Collection $Matches */
            $Matches = $FixedPrices->getResourceCollection()
                                   ->addFieldToFilter('product_code', $Product->getData('sku'));
            // Collect fixed prices items with values, in order to display them in template
            /** @var Globale_FixedPrices_Model_Fixedprices $Match */
            foreach ($Matches as $Match) {
                $RowId = $Match->getId();
                $Data[$RowId]['country_code']  = $Match->getData('country_code');
                $Data[$RowId]['currency_code'] = $Match->getData('currency_code');
                $Data[$RowId]['price']         = $Match->getData('price');
                $Data[$RowId]['special_price'] = $Match->getData('special_price');
                $FromDate = $Match->getData('date_from');
                $ToDate = $Match->getData('date_to');
                $Data[$RowId]['date_from']     = (empty($FromDate)) ? $FromDate : date('m/d/Y', strtotime($FromDate));
                $Data[$RowId]['date_to']       = (empty($ToDate)) ? $ToDate : date('m/d/Y', strtotime($ToDate));
            }
            $this->setProductFixedPrices($Data);
        }

    }

    /**
     * Get all countries from the Glboal-e SDK,
     * in order to fill the countries dropdown select
     * @param SDK $GlobaleSDK
     * @access protected
     */
    protected function getAllCurrencies($GlobaleSDK) {

        /** @var Data $GECurrenciesResponse */
        $GECurrenciesResponse = $GlobaleSDK->Browsing()->GetCurrencies();
        if($GECurrenciesResponse->getSuccess()){
            $GECurrencies = $GECurrenciesResponse->getData()->getCurrencies();
        }else{
            $GECurrencies = array();
        }
        $this->setCurrencies($GECurrencies);
    }

    /**
     * Get all currencies from the Glboal-e SDK,
     * in order to fill the currencies drop-down select
     * @param SDK $GlobaleSDK
     * @access protected
     */
    protected function getAllCountries($GlobaleSDK) {

        /** @var Data $CountriesResponse */
        $CountriesResponse = $GlobaleSDK->Browsing()->GetCountries();
        if($CountriesResponse->getSuccess()){
            $GECountries = $CountriesResponse->getData()->getCountries();
        }else{
            $GECountries = array();
        }
        $this->setCountries($GECountries);
    }

    /**
     * Prepare global layout
     * Add "Add Fixed Prices" button to layout
     * @return Globale_FixedPrices_Block_Adminhtml_Catalog_Product_Edit_Tab_Price_FixedPrices
     * @access protected
     */
    protected function _prepareLayout()
    {
        /** @var Mage_Adminhtml_Block_Widget_Button $Button */
        $Button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('catalog')->__('Add Fixed Price'),
                'onclick' => 'return fixedPriceControl.addItem()',
                'class' => 'add'
            ));
        $Button->setName('add_fixed_price_item_button');
        $this->setChild('add_button', $Button);
        return parent::_prepareLayout();
    }
}