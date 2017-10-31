<?php

/**
 * Class Globale_Order_Model_Addresses
 */
class Globale_FixedPrices_Model_Fixedprices extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('globale_fixedprices/fixedprices'); // this is location of the resource file.
    }

    /**
     * Filter fixed prices items for insert/update
     * @param string $Code
     * @param string $CountryCode
     * @param string $CurrencyCode
     * @access public
     */
    public function loadByFixedProduct($Code, $CountryCode, $CurrencyCode) {
        if(empty($CountryCode)) $CountryCode = array('null' => true);
        /** @var  Globale_FixedPrices_Model_Resource_Fixedprices_Collection $matches */
        $Matches = $this->getResourceCollection()
            ->addFieldToFilter('product_code', $Code)
            ->addFieldToFilter('country_code', $CountryCode)
            ->addFieldToFilter('currency_code', $CurrencyCode);
        /** @var Globale_FixedPrices_Model_Fixedprices $match */
        foreach ($Matches as $Match) {
            $RowId = $Match->getId();
        }

        if(!empty($RowId)){
            $this->setData('id', $RowId);
        }
    }

    /**
     * Collect all data from product request for each fixed prices items
     * and manipulate update/inser/delete fixed prices items
     * @param Varien_Event_Observer $Observer
     * @access public
     */
    public function updateFixedPrices(Varien_Event_Observer $Observer) {

        $Validation = array();
        // Get the requested product
        $Product = $Observer->getEvent()->getRequest()->getPost('product');
        if(isset($Product['fixedprices']) && !empty($Product['fixedprices'])){

            $Data = array();
            // Collect the input from each fixed price item of the product
            foreach ($Product['fixedprices'] as $Item) {

                $ProductCode = $Product['sku'];
                // identifier for delete fixed price item for this product
                $delete = (isset($Item['delete']) && ((int)$Item['delete'])) ? true : false;
                // check validation for country/currency dropdown
                $Country = (isset($Item['country']) ? $Item['country'] : $Item['countryhidden']);
                $Currency = (isset($Item['currency']) ? $Item['currency'] : $Item['currencyhidden']);
                if(!$this->isValidCurrency($Currency)){
                    continue;
                }
                // check validation for special price text input
                if($this->isValidSpecialPrice($Item['specialprice'])){
                    $SpecialPrice = (!empty($Item['specialprice'])) ? (float)$Item['specialprice'] : null;
                }else{
                    $Validation[] = $Item['specialprice'];
                    continue;
                }
                // check validation for price text input
                if($this->isValidPrice($Item['price'])){
                    $Price = (float)$Item['price'];
                }else{
                    $Validation[] = $Item['price'];
                    continue;
                }
                // check validation for special price text input
                $SpecialPriceFrom = (!empty($Item['specialpricefromdate'])) ? $Item['specialpricefromdate'] : null;
                $SpecialPriceTo = (!empty($Item['specialpricetodate'])) ? $Item['specialpricetodate'] : null;

                // collect fixed price item values for insert/update in DB
                $Data[] = array(
                    "product_code"  => $ProductCode,
                    "country_code"  => ($Country) ? $Country : null,
                    "currency_code" => $Currency,
                    "price"         => $Price,
                    "special_price" => $SpecialPrice,
                    "date_from"     => $SpecialPriceFrom,
                    "date_to"       => $SpecialPriceTo,
                    "delete"        => $delete
                );
            }

            // update (insert/update/delete) product fixed prices in database
            $UpdateInfo = $this->updateFixedPricesItemsForProduct($Data);
            // Add Notice messages
            /** @var Mage_Core_Model_Session $Session */
            $Session = Mage::getSingleton('core/session');
            if($UpdateInfo['RowsUpdated'] > 0) {
                $Session->addNotice($UpdateInfo['RowsUpdated'] . Mage::helper('core')->__(' fixed price were updated'));
            }if($UpdateInfo['RowsInsert'] > 0) {
                $Session->addNotice($UpdateInfo['RowsInsert']  . Mage::helper('core')->__(' fixed price were inserted'));
            }
            if($UpdateInfo['RowsDeleted'] > 0) {
                $Session->addNotice($UpdateInfo['RowsDeleted']  . Mage::helper('core')->__(' fixed price were deleted'));
            }
            // validation notice message
            if((count($Validation) > 0)){
                foreach ($Validation as $item){
                    $Session->addNotice(Mage::helper('core')->__('the input price ') . $item . Mage::helper('core')->__(' not contains a price number'));
                }
            }
        }
    }

    /**
     * Update fixed prices items for product
     * @param array $Data
     * @return array $UpdateInfo
     * @access public
     */
    public function updateFixedPricesItemsForProduct($Data) {

        $UpdateInfo = array("RowsUpdated" => 0,
                            "RowsInsert"  => 0,
                            "RowsDeleted"  => 0
                      );
        foreach ($Data as $Row){
            // in case of delete fixed price item
            if($Row['delete']) {
                $this->deleteFixedPricesDB($Row);
                $UpdateInfo["RowsDeleted"]++;
            }else {
                // in case of insert/update fixed price item
                $this->setData($Row);
                $this->loadByFixedProduct($Row['product_code'],
                                          $Row['country_code'],
                                          $Row['currency_code']
                                          );
                if ($this->getId()) {
                    $UpdateInfo["RowsUpdated"]++;
                } else {
                    $UpdateInfo["RowsInsert"]++;
                }
                $this->save();
            }
        }
        return $UpdateInfo;
    }

    /**
     * Delete fixed prices items for product by
     * product code, country code, currency code
     * @param array $Row
     * @access public
     */
    public function deleteFixedPricesDB($Row){

        // Currency code or Country code can be set with NULL value
		if(empty($Row['currency_code'])) {
			$Row['currency_code'] = array('null' => true);
		}
        if(empty($Row['country_code'])) {
            $Row['country_code'] = array('null' => true);
        }
        /** @var  Globale_FixedPrices_Model_Resource_Fixedprices_Collection $matches */
        $Matches = $this->getResourceCollection()
            ->addFieldToFilter('product_code', $Row['product_code'])
            ->addFieldToFilter('country_code', $Row['country_code'])
            ->addFieldToFilter('currency_code', $Row['currency_code']);

        /** @var Globale_FixedPrices_Model_Fixedprices $Item */
        foreach ($Matches as $Item){
            $Item->delete();
        }
    }

    /**
     * Add Custom field for "fixed prices" widget into the product block form Edit
     * @param Varien_Event_Observer $Observer
     * @return Varien_Data_Form
     * @access public
     */
    public function buildFieldWithFixedPricesWidget(Varien_Event_Observer $Observer) {

        /** @var Varien_Data_Form $Form */
        $Form = $Observer->getForm();
        // build this field once, only under Group price field
        /** @var Globale_FixedPrices_Model_Observers_FixedPrices $GroupPrice */
        $GroupPrice  = $Form->getElement('group_price');
        if($GroupPrice) {
            // Build fieldset for fixed prices
            $Fieldset = $Form->addFieldset('fixed_prices', array(
                'legend' => Mage::helper('catalog')->__('Global-e Fixed Prices'),
                'class' => 'fieldset-wide'
            ));
            // Build field for fixed prices
            /** @var Varien_Data_Form_Element_Fieldset $FieldElement */
            $FieldElement = $Fieldset->addField('fixedprices', 'text',
                array(
                    'name'      => 'fixedprices',
                    'label'     => Mage::helper('catalog')->__('Fixed Prices'),
                    'class'     => 'fixed-prices',
                    'required'  => false,
                    'note'      => Mage::helper('catalog')->__('Add fixed prices for product')
                )
            );
            // Render the fixed prices
            $FieldElement->setRenderer(
                Mage::app()->getLayout()->createBlock('globale_fixedprices/adminhtml_catalog_product_edit_tab_price_fixedPrices')
            );
        }
        return $Form;
    }

    /**
     * create a filter key for fixed priced product based on Global-e CCC
     * @param array $arr
     * @return string
     * @access protected
     */
    protected function generateDataKey($arr){
        return "{$arr['product_code']}_{$arr['country_code']}_{$arr['currency_code']}";
    }


    /**
     * Check validation for country/currency (cannot accept 0 value)
     * @param string $Country
     * @param string $Currency
     * @return bool
     * @access protected
     */
    protected function isValidCurrency($Currency) {

        if(!$Currency){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Check validation for price
     * @param string $Price
     * @return bool
     * @access protected
     */
    protected function isValidPrice($Price) {

        if(isset($Price) && !empty($Price) && is_numeric($Price)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Check validation for special price
     * @param string $SpecialPrice
     * @return bool
     * @access protected
     */
    protected function isValidSpecialPrice($SpecialPrice) {

        if(!empty($SpecialPrice) && !is_numeric($SpecialPrice)){
            return false;
        }else{
            return true;

        }
    }

}