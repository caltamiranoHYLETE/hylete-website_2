<?php
/**
 * Copyright © 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Enhancedgrid
 * @copyright   Copyright © 2009-2012 Icommerce Nordic AB
 */
class Icommerce_Enhancedgrid_Model_Product_Grid_Settings_Columns extends Varien_Object {
    
    protected $columnSettings = array();
    
    public function getStore() {
        if($this->getData('store')) return $this->getData('store');
        
        return Mage::app()->getStore();
    }
    
    public function getColumnSettingsArray() {
        $this->columnSettings = array();
        $storeSettings = Mage::getStoreConfig( 'enhancedgrid/columns/showcolumns' );
        
        $tempArr = explode( ',', $storeSettings );
        
        foreach ($tempArr as $showCol) {
            $this->columnSettings[trim( $showCol )] = true;
        }
        
        return $this->columnSettings;
    }
    
    
    public function getDefaults() {
        $truncate = Mage::getStoreConfig( 'enhancedgrid/general/truncatelongtextafter' );
        $currency = $this->getStore()->getBaseCurrency()->getCode();
        $defaults = array(
            'cost' => array(
                'type' => 'price', 
                'width' => '30px', 
                'header' => Mage::helper( 'catalog' )->__( 'Cost' ), 
                'currency_code' => $currency
            ), 
            'weight' => array(
                'type' => 'number', 
                'width' => '30px', 
                'header' => Mage::helper( 'catalog' )->__( 'Weight' )
            ), 
            'bss_weight' => array(
                'type' => 'number', 
                'width' => '30px', 
                'header' => Mage::helper( 'catalog' )->__( 'BSS Weight' )
            ), 
            'url_key' => array(
                'type' => 'text', 
                'width' => '100px', 
                'header' => Mage::helper( 'catalog' )->__( 'Url Key' )
            ), 
            'tier_price' => array(
                'type' => 'price', 
                'width' => '100px', 
                'header' => Mage::helper( 'catalog' )->__( 'Tier Price' ), 
                'currency_code' => $currency
            ), 
            'tax_class_id' => array(
                'type' => 'text', 
                'width' => '100px', 
                'header' => Mage::helper( 'catalog' )->__( 'Tax Class ID' )
            ), 
            'special_to_date' => array(
                'type' => 'date', 
                'width' => '100px', 
                'header' => Mage::helper( 'catalog' )->__( 'Spshl TO Date' )
            ), 
            //@nelkaake Tuesday April 27, 2010 :
            'created_at' => array(
                'type' => 'datetime', 
                'width' => '100px', 
                'header' => Mage::helper( 'catalog' )->__( 'Date Created' )
            ), 
            'updated_at' => array(
                'type' => 'datetime', 
                'width' => '100px', 
                'header' => Mage::helper( 'catalog' )->__( 'Date Updated' )
            ), 
            'special_price' => array(
                'type' => 'price', 
                'width' => '30px', 
                'header' => Mage::helper( 'catalog' )->__( 'Special Price' ), 
                'currency_code' => $currency
            ), 
            'special_from_date' => array(
                'type' => 'date', 
                'width' => '100px', 
                'header' => Mage::helper( 'catalog' )->__( 'Spshl FROM Date' )
            ), 
            'color' => array(
                'type' => 'text', 
                'width' => '70px', 
                'header' => Mage::helper( 'catalog' )->__( 'Color' )
            ), 
            'size' => array(
                'type' => 'text', 
                'width' => '70px', 
                'header' => Mage::helper( 'catalog' )->__( 'Size' )
            ), 
            'brand' => array(
                'type' => 'text', 
                'width' => '70px', 
                'header' => Mage::helper( 'catalog' )->__( 'Brand' )
            ), 
            'custom_design' => array(
                'type' => 'text', 
                'width' => '70px', 
                'header' => Mage::helper( 'catalog' )->__( 'Custom Design' )
            ), 
            'custom_design_from' => array(
                'type' => 'date', 
                'width' => '70px', 
                'header' => Mage::helper( 'catalog' )->__( 'Custom Design FRM' )
            ), 
            'custom_design_to' => array(
                'type' => 'date', 
                'width' => '70px', 
                'header' => Mage::helper( 'catalog' )->__( 'Custom Design TO' )
            ), 
            'default_category_id' => array(
                'type' => 'text', 
                'width' => '70px', 
                'header' => Mage::helper( 'catalog' )->__( 'Default Categry ID' )
            ), 
            'dimension' => array(
                'type' => 'text', 
                'width' => '75px', 
                'header' => Mage::helper( 'catalog' )->__( 'Dimensions' )
            ), 
            'manufacturer' => array(
                'type' => 'text', 
                'width' => '75px', 
                'header' => Mage::helper( 'catalog' )->__( 'Manufacturer' )
            ), 
            'meta_keyword' => array(
                'type' => 'text', 
                'width' => '200px', 
                'header' => Mage::helper( 'catalog' )->__( 'Meta Keywds' )
            ), 
            'meta_description' => array(
                'type' => 'text', 
                'width' => '200px', 
                'header' => Mage::helper( 'catalog' )->__( 'Meta Descr' )
            ), 
            'meta_title' => array(
                'type' => 'text', 
                'width' => '100px', 
                'header' => Mage::helper( 'catalog' )->__( 'Meta Title' )
            ), 
            'short_description' => array(
                'type' => 'text', 
                'width' => '150px', 
                'header' => Mage::helper( 'catalog' )->__( 'Short Description' ), 
                'string_limit' => $truncate
            ), 
            'description' => array(
                'type' => 'text', 
                'width' => '200px', 
                'header' => Mage::helper( 'catalog' )->__( 'Description' ), 
                'string_limit' => $truncate
            )
        );
        
        return $defaults;
    }
}