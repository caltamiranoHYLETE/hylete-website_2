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
class Icommerce_Enhancedgrid_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $isenhanced = true;

    private $columnSettings = array();
    protected $_columnSettings = null;

    private $columnOptions = array();

    private $isenabled = true;

    public function __construct() {

        $this->setTemplate( 'icommerce/enhancedgrid/catalog/product/grid.phtml' );
        parent::__construct();
        $this->isenabled = Mage::getStoreConfig( 'enhancedgrid/general/isenabled' );

        $this->setId( 'productGrid' );

        $this->prepareDefaults();

        $this->setSaveParametersInSession( true );
        $this->setUseAjax( true );
        $this->setVarNameFilter( 'product_filter' );

        $this->prepareColumnSettings();

    }

    protected function prepareDefaults() {
        $this->setDefaultLimit( Mage::getStoreConfig( 'enhancedgrid/defaults/limit' ) );
        $this->setDefaultPage( Mage::getStoreConfig( 'enhancedgrid/defaults/page' ) );
        $this->setDefaultSort( Mage::getStoreConfig( 'enhancedgrid/defaults/sort' ) );
        $this->setDefaultDir( Mage::getStoreConfig( 'enhancedgrid/defaults/dir' ) );

    }

    protected function prepareColumnSettings() {
        $this->_columnSettings = Mage::getModel( 'enhancedgrid/product_grid_settings_columns' )->setStore( $this->_getStore() );
        $this->columnSettings = $this->_getColumnSettings()->getColumnSettingsArray();

        return $this;
    }

    /**
     * @return Icommerce_Enhancedgrid_Model_Product_Grid_Settings_Columns
     */
    protected function _getColumnSettings() {
        return $this->_columnSettings;
    }

    public function colIsVisible($code) {
        return isset( $this->columnSettings[$code] );
    }

    protected function _isSpecialCol($col) {
        return ($col == 'qty' || $col == 'websites' || $col == 'id' || $col == 'categories' || $col == 'price' || $col == 'visibility' || $col == 'status');
    }

    protected function _prepareLayout() {
        $this->setChild( 'export_button',
            $this->getLayout()
                ->createBlock( 'adminhtml/widget_button' )
                ->setData(
                array(
                    'label' => Mage::helper( 'adminhtml' )->__( 'Export' ),
                    'onclick' => $this->getJsObjectName() . '.doExport()',
                    'class' => 'task'
                ) ) );
        $this->setChild( 'reset_filter_button',
            $this->getLayout()
                ->createBlock( 'adminhtml/widget_button' )
                ->setData(
                array(
                    'label' => Mage::helper( 'adminhtml' )->__( 'Reset Filter' ),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()'
                ) ) );
        $this->setChild( 'search_button',
            $this->getLayout()
                ->createBlock( 'adminhtml/widget_button' )
                ->setData(
                array(
                    'label' => Mage::helper( 'adminhtml' )->__( 'Search' ),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'task'
                ) ) );
        return parent::_prepareLayout();
    }

    public function getQueryStr() {
        return urldecode( $this->getParam( 'q' ) );
    }

    /**
     * get collection object
     * //@nelkaake -a 13/11/10: this is just here for the codeassist function
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getCollection() {
        return parent::getCollection();
    }

    protected function _prepareCollection() {

        $collection = $this->getCollection();
        //@nelkaake -m 13/11/10: Just made it a little nicer
        $queryString = $this->getQueryStr();
        if ( $queryString ) {
            $collection = Mage::helper( 'enhancedgrid' )->getSearchCollection( $queryString, $this->getRequest() );
        }

        if ( ! $collection ) {
            //@nelkaake -a 15/12/10: To fix categories column issue this is a tempoary way we are going to load the modified collection class.
            $collection = new Icommerce_Enhancedgrid_Model_Resource_Eav_Mysql4_Product_Collection();
        }
        $store = $this->_getStore();

        //
        if(Icommerce_Default::isModuleActive("Icommerce_StoreStock")){
            $collection->joinField( 'qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id='.Mage::helper("storestock")->getStoreStockId($store->getId()), 'left' );
        }else{
            $collection->joinField( 'qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left' );
        }
        $collection->addAttributeToSelect( 'sku' );

        if ( $store->getId() ) {
            $collection->addStoreFilter( $store->getId() );
            $collection->joinAttribute( 'custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId() );
            $collection->joinAttribute( 'status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId() );
            $collection->joinAttribute( 'visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId() );
            $collection->joinAttribute( 'price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId() );
        } else {
            $collection->addAttributeToSelect( 'price' );
            $collection->addAttributeToSelect( 'status' );
            $collection->addAttributeToSelect( 'visibility' );
        }
        // EG: Select all needed columns.
        //id,name,type,attribute_set,sku,price,qty,visibility,status,websites,image
        foreach ($this->columnSettings as $col => $true) {
            if ( $this->_isSpecialCol( $col ) ) continue;
            $collection->addAttributeToSelect( $col );
        }

        if ( $this->colIsVisible( 'categories' ) ) {
            $this->setJoinCategories( true );
        }

        if ( $store->getId() ) {
            Mage::dispatchEvent('enhanced_grid_collection_set_before',array('collection' => $collection, 'store' => $store));
        }

        $this->setCollection( $collection );

        $this->addExportType( '*/*/exportCsv', Mage::helper( 'customer' )->__( 'CSV' ) );
        $this->addExportType( '*/*/exportXml', Mage::helper( 'customer' )->__( 'XML' ) );

        parent::_prepareCollection();
        $collection->addWebsiteNamesToResult();

        return $this;
    }

    /**
     * if the attribute has options an options entry will be
     * added to $columnOptions
     */
    protected function loadColumnOptions($attr_code) {

        $config    = Mage::getModel('eav/config');
        $attribute = $config->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attr_code);

        if ( $attribute && $attribute->getFrontendInput() == 'select' ) {

            $options = array();

            try {

                $values = $attribute->setStoreId($this->_getStore()->getId())->getSource()->getAllOptions();
                foreach ($values as $value) {
                    if($value["label"] != ""){
                        $options[$value["value"]] = $value["label"];
                    }
                }

            }catch(Exception $e){
                //if an exception occurs, keeping previous fix by @nelkaake but as an failsafe or just in case.
                $options = array(); //clearing the array.
                $values = Mage::getResourceModel( 'eav/entity_attribute_option_collection' )->setAttributeFilter(
                    $attribute->getId() )
                    ->setStoreFilter( $this->_getStore()
                    ->getId(), false )
                    ->load();

                foreach ($values as $value) {
                    $options[$value->getOptionId()] = $value->getValue();
                }

            }

            $this->columnOptions[$attr_code] = $options;

        }


    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam( 'store', 0 );
        $store = Mage::app()->getStore( $storeId );
        if ( $store->getId() != $storeId ) $store = Mage::app()->getStore( 0 );
        return $store;
    }

    protected function _addColumnFilterToCollection($column) {
        if ( $this->getCollection() ) {
            if ( $column->getId() == 'websites' ) {
                $this->getCollection()->joinField( 'websites', 'catalog/product_website', 'website_id', 'product_id=entity_id', null,
                    'left' );
            }
        }
        return parent::_addColumnFilterToCollection( $column );
    }

    protected function _prepareColumns() {
        // Loads all the column options for each applicable column.
        foreach ($this->columnSettings as $col => $true) {
            $this->loadColumnOptions( $col );
        }

        $store = $this->_getStore();
        if ( $this->colIsVisible( 'id' ) ) {
            $this->addColumn( 'id',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'ID' ),
                    'width' => '50px',
                    'type' => 'number',
                    'index' => 'entity_id'
                ) );
        }

        $imgWidth = Mage::getStoreConfig( 'enhancedgrid/images/width' ) + "px";

        if ( $this->colIsVisible( 'thumbnail' ) ) {
            $this->addColumn( 'thumbnail',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Thumbnail' ),
                    'type' => 'image',
                    'width' => $imgWidth,
                    'index' => 'thumbnail'
                ) );
        }
        if ( $this->colIsVisible( 'small_image' ) ) {
            $this->addColumn( 'small_image',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Small Img' ),
                    'type' => 'image',
                    'width' => $imgWidth,
                    'index' => 'small_image'
                ) );
        }
        if ( $this->colIsVisible( 'image' ) ) {
            $this->addColumn( 'image',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Image' ),
                    'type' => 'image',
                    'width' => $imgWidth,
                    'index' => 'image'
                ) );
        }

        if ( $this->colIsVisible( 'name' ) ) {
            $this->addColumn( 'name',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Name' ),
                    'index' => 'name'
                )//                    'width' => '150px'
                 );
        }
        if ( $this->colIsVisible( 'name' ) ) {
            if ( $store->getId() ) {
                $this->addColumn( 'custom_name',
                    array(
                        'header' => Mage::helper( 'catalog' )->__( 'Name In %s', $store->getName() ),
                        'index' => 'custom_name',
                        'width' => '150px'
                    ) );
            }
        }

        if ( $this->colIsVisible( 'type_id' ) ) {
            $this->addColumn( 'type',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Type' ),
                    'width' => '60px',
                    'index' => 'type_id',
                    'type' => 'options',
                    'options' => Mage::getSingleton( 'catalog/product_type' )->getOptionArray()
                ) );
        }

        if ( $this->colIsVisible( 'attribute_set_id' ) ) {
            $sets = Mage::getResourceModel( 'eav/entity_attribute_set_collection' )->setEntityTypeFilter(
                Mage::getModel( 'catalog/product' )->getResource()
                    ->getTypeId() )
                ->load()
                ->toOptionHash();

            $this->addColumn( 'set_name',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Attrib. Set Name' ),
                    'width' => '100px',
                    'index' => 'attribute_set_id',
                    'type' => 'options',
                    'options' => $sets
                ) );
        }

        if ( $this->colIsVisible( 'sku' ) ) {
            $this->addColumn( 'sku',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'SKU' ),
                    'width' => '80px',
                    'index' => 'sku'
                ) );

			$this->addColumn( 'parent_sku',
				array(
					'header' => Mage::helper( 'catalog' )->__( 'Parent SKU' ),
					'width' => '100px',
					'index' => 'parent_sku',
					'filter' => false,
					'align' => 'center',
					'renderer'  => 'Hylete_Adminhtml_Block_Catalog_Parent'
				) );
        }

        if ( $this->colIsVisible( 'price' ) ) {
            $this->addColumn( 'price',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Price' ),
                    'type' => 'price',
                    'currency_code' => $store->getBaseCurrency()
                        ->getCode(),
                    'index' => 'price'
                ) );
        }

        if ( $this->colIsVisible( 'qty' ) ) {
            $this->addColumn( 'qty',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Qty' ),
                    'width' => '100px',
                    'type' => 'number',
                    'index' => 'qty'
                ) );
        }

        if ( $this->colIsVisible( 'visibility' ) ) {
            $this->addColumn( 'visibility',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Visibility' ),
                    'width' => '70px',
                    'index' => 'visibility',
                    'type' => 'options',
                    'options' => Mage::getModel( 'catalog/product_visibility' )->getOptionArray()
                ) );
        }

        if ( $this->colIsVisible( 'status' ) ) {
            $this->addColumn( 'status',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Status' ),
                    'width' => '70px',
                    'index' => 'status',
                    'type' => 'options',
                    'options' => Mage::getSingleton( 'catalog/product_status' )->getOptionArray()
                ) );
        }

        if ( $this->colIsVisible( 'websites' ) ) {
            if ( ! Mage::app()->isSingleStoreMode() ) {
                $this->addColumn( 'websites',
                    array(
                        'header' => Mage::helper( 'catalog' )->__( 'Websites' ),
                        'width' => '100px',
                        'sortable' => false,
                        'index' => 'websites',
                        'type' => 'options',
                        'options' => Mage::getModel( 'core/website' )->getCollection()
                            ->toOptionHash()
                    ) );
            }
        }

        if ( $this->colIsVisible( 'categories' ) ) {
            $this->addColumn( 'categories',
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Categories' ),
                    'width' => '100px',
                    'sortable' => true,
                    'index' => 'categories',
                    'sort_index' => 'category',
                    'filter_index' => 'category'
                ) );
        }

        $this->_addVariableColumns();

        $this->addColumn( 'action',
            array(
                'header' => Mage::helper( 'catalog' )->__( 'Action' ),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper( 'catalog' )->__( 'Edit' ),
                        'id' => "editlink",
                        'url' => array(
                            'base' => 'adminhtml/catalog_product/edit',
                            'params' => array(
                                'store' => $this->getRequest()
                                    ->getParam( 'store' )
                            )
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores'
            ) );

        $this->addRssList( 'rss/catalog/notifystock', Mage::helper( 'catalog' )->__( 'Notify Low Stock RSS' ) );

        return parent::_prepareColumns();
    }

    /**
     * Adds all the columns that are not part of the fixed configuration settings prepared above.
     *
     */
    protected function _addVariableColumns() {

        $defaults = $this->_getColumnSettings()->getDefaults();

        // EG: Show all (other) needed columns.
        $ignoreCols = array(
            'id' => true,
            'websites' => true,
            'status' => true,
            'visibility' => true,
            'qty' => true,
            'price' => true,
            'sku' => true,
            'attribute_set_id' => true,
            'type_id' => true,
            'name' => true,
            'image' => true,
            'thumbnail' => true,
            'small_image' => true,
            'categories' => true
        );


        foreach ($this->columnSettings as $col => $true) {
            if ( isset( $ignoreCols[$col] ) ) continue;
            if ( isset( $defaults[$col] ) ) {
                $innerSettings = $defaults[$col];
            } else {
                $innerSettings = array(
                    'header' => Mage::helper( 'catalog' )->__( $col ),
                    'width' => '80px',
                    'type' => 'text'
                );
            }
            $innerSettings['index'] = $col;
            //echo print_r($this->columnOptions, true);
            if ( isset( $this->columnOptions[$col] ) ) {
                //die($col);
                $innerSettings['type'] = 'options';
                $innerSettings['options'] = $this->columnOptions[$col];
            }
            $this->addColumn( $col, $innerSettings );
        }

        return $this;
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField( 'entity_id' );
        $this->getMassactionBlock()->setFormFieldName( 'product' );

        $this->getMassactionBlock()->addItem( 'delete',
            array(
                'label' => Mage::helper( 'catalog' )->__( 'Delete' ),
                'url' => $this->getUrl( '*/*/massDelete' ),
                'confirm' => Mage::helper( 'catalog' )->__( 'Are you sure?' )
            ) );

        $statuses = Mage::getSingleton( 'catalog/product_status' )->getOptionArray();

        array_unshift( $statuses, array(
            'label' => '',
            'value' => ''
        ) );
        $this->getMassactionBlock()->addItem( 'status',
            array(
                'label' => Mage::helper( 'catalog' )->__( 'Change status' ),
                'url' => $this->getUrl( '*/*/massStatus', array(
                    '_current' => true
                ) ),
                'additional' => array(
                    'visibility' => array(
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper( 'catalog' )->__( 'Status' ),
                        'values' => $statuses
                    )
                )
            ) );

        $this->getMassactionBlock()->addItem( 'attributes',
            array(
                'label' => Mage::helper( 'catalog' )->__( 'Update attributes' ),
                'url' => $this->getUrl( 'adminhtml/catalog_product_action_attribute/edit', array(
                    '_current' => true
                ) )
            ) );

        // Divider
        $this->getMassactionBlock()->addItem( 'imagesDivider', $this->getMADivider( "Images" ) );

        // Show images...
        $imgWidth = Mage::getStoreConfig( 'enhancedgrid/images/width' );
        $imgHeight = Mage::getStoreConfig( 'enhancedgrid/images/height' );
        $this->getMassactionBlock()->addItem( 'showImages',
            array(
                'label' => $this->__( 'Show Selected Images' ),
                'url' => $this->getUrl( 'adminhtml/*/index', array(
                    '_current' => true
                ) ),
                'callback' => 'showSelectedImages(productGrid_massactionJsObject, ' . '{checkedValues}, \'<img src=\\\'{imgurl}\\\' width=' .
                     $imgWidth . ' height=' . $imgHeight . ' border=0 />\')'
            )
             );
        // Hide Images
        $this->getMassactionBlock()->addItem( 'hideImages',
            array(
                'label' => $this->__( 'Hide Selected Images' ),
                'url' => $this->getUrl( 'adminhtml/*/index', array(
                    '_current' => true
                ) ),
                'callback' => 'hideSelectedImages(productGrid_massactionJsObject, {checkedValues})'
            )
             );

        // Divider 3
        $this->getMassactionBlock()->addItem( 'otherDivider', $this->getMADivider( "Other" ) );

        // Opens all products


        // Refresh...
        $this->getMassactionBlock()->addItem( 'refreshProducts',
            array(
                'label' => $this->__( 'Refresh Products' ),
                'url' => $this->getUrl( 'adminhtml/*/massRefreshProducts', array(
                    '_current' => true
                ) )
            ) );

        // $this->getMassactionBlock()->addItem('saveEditables', array(
        //     'label' => $this->__('SAVE EDITABLES'),
        //     'url'   => $this->getUrl('*/*/saveEditables', array('_current'=>true)),
        //     'fields' => array('short_description2', '')
        // ));
        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));

        return $this;
    }

    public function getRowUrl($row) {
        //@nelkaake -m 16/11/10: Changed to use _getStore function
        //Fetched the id so the urls work.
        return $this->getUrl( 'adminhtml/catalog_product/edit',
            array(
                'store' => $this->_getStore()->getId(),
                'id' => $row->getId()
            ) );
    }

    public function getGridUrl() {
        return $this->getUrl( 'adminhtml/*/grid', array(
            '_current' => true
        ) );
    }

    protected function getMADivider($dividerHeading = "-------") {
        $dividerTemplate = array(
            'label' => '--------' . $this->__( $dividerHeading ) . '--------',
            'url' => $this->getUrl( '*/*/index', array(
                '_current' => true
            ) ),
            'callback' => "null"
        );
        return $dividerTemplate;
    }

    /**
     * @nelkaake -a 15/12/10: TODO move this to a decorator class.
     */
    protected function _preparePage() {
        if ( ! $this->getJoinCategories() ) {
            return parent::_preparePage();
        }

        $this->getCollection()
            ->getSelect()
            ->reset( Zend_Db_Select::GROUP );

        parent::_preparePage();

        $category_decorator = Mage::getModel( 'enhancedgrid/product_collection_category_decorator' );
        $category_decorator->setCollection( $this->getCollection() )
            ->addCategories();

        return $this;

    }

}