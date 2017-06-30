<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerloodiscount_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('bakerlooDiscounts');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('bakerloo_restful/discount')->getCollection();
        $collection->setFirstStoreFlag(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('bakerloo_restful')->__('ID'),
            'index' => 'id',
            'type' => 'number',
        ));

        $this->addColumn('discount_description', array(
            'header' => Mage::helper('bakerloo_restful')->__('Discount Desc'),
            'index' => 'discount_description',
        ));

        $this->addColumn('discount_max', array(
            'header' => Mage::helper('bakerloo_restful')->__('Discount Max'),
            'index' => 'discount_max',
        ));

        $this->addColumn('discount_type', array(
            'header' => Mage::helper('bakerloo_restful')->__('Discount Type'),
            'index' => 'discount_type',
            'type' => 'options',
            'options' => Mage::getModel('bakerloo_restful/source_discounttype')->toOptions(),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('bakerloo_restful')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter'        => false,
                /*'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),*/
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('bakerloo_restful')->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));
        $this->addColumn('updated_at', array(
            'header' => Mage::helper('bakerloo_restful')->__('Updated At'),
            'index' => 'updated_at',
            'type' => 'datetime',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('bakerloo_restful')->__('Action'),
            'width' => '80px',
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('bakerloo_restful')->__('Delete'),
                    'url' => array('base' => 'adminhtml/bakerloodiscount/delete'),
                    'field' => 'id',
                    'confirm' => Mage::helper('bakerloo_restful')->__('Are you sure?')
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Return row url for js event handlers
     *
     * @param Varien_Object
     * @return string
     */
    public function getRowUrl($item) {
        return $this->getUrl('*/*/edit', array('id' => $item->getId()));
    }

    protected function _afterLoadCollection() {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

}