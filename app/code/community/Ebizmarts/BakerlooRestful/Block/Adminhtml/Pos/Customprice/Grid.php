<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Customprice_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('bakerlooCustomdiscounts');
        $this->setUseAjax(true);
        $this->setDefaultSort('id', 'desc');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('bakerloo_restful/customPrice')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('bakerloo_restful')->__('ID'),
            'index'  => 'id',
            'type'   => 'number',
        ));

        $this->addColumn('order_id', array(
            'header' => Mage::helper('bakerloo_restful')->__('Order ID'),
            'index'  => 'order_id',
            'type'   => 'number',
        ));

        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('bakerloo_restful')->__('Order #'),
            'index'  => 'order_increment_id',
            'type'   => 'number',
        ));

        $this->addColumn('admin_user', array(
            'header' => Mage::helper('bakerloo_restful')->__('Admin'),
            'index'  => 'admin_user'
        ));

        $this->addColumn('store_id', array(
            'header' => Mage::helper('bakerloo_restful')->__('Store ID'),
            'index'  => 'store_id',
            'type'   => 'number',
        ));

        $this->addColumn('grand_total_before_discount', array(
            'header' => Mage::helper('bakerloo_restful')->__('Total before discount'),
            'index'  => 'grand_total_before_discount',
            'type'   => 'number',
        ));

        $this->addColumn('total_discount', array(
            'header' => Mage::helper('bakerloo_restful')->__('Discount amount'),
            'index'  => 'total_discount',
            'type'   => 'number',
        ));

        $this->addColumn('grand_total_after_discount', array(
            'header' => Mage::helper('bakerloo_restful')->__('Total after discount'),
            'index'  => 'grand_total_after_discount',
            'type'   => 'number',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

}