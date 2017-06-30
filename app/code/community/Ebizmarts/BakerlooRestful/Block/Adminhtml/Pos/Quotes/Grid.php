<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Quotes_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('bakerlooQuotes');
        $this->setUseAjax(true);
        $this->setDefaultSort('id', 'desc');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('bakerloo_restful/quote')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('bakerloo_restful')->__('ID'),
            'index'  => 'id',
            'type'   => 'number',
        ));

        $this->addColumn('order_guid', array(
            'header' => Mage::helper('bakerloo_restful')->__('GUID'),
            'index'  => 'order_guid',
        ));

        $this->addColumn('customer_email', array(
            'header' => Mage::helper('bakerloo_restful')->__('Email'),
            'index'  => 'customer_email',
        ));
        $this->addColumn('customer_firstname', array(
            'header' => Mage::helper('bakerloo_restful')->__('Firstname'),
            'index'  => 'customer_firstname',
        ));
        $this->addColumn('customer_lastname', array(
            'header' => Mage::helper('bakerloo_restful')->__('Lastname'),
            'index'  => 'customer_lastname',
        ));
        $this->addColumn('user', array(
            'header' => Mage::helper('bakerloo_restful')->__('Sales rep.'),
            'index'  => 'user',
        ));
        $this->addColumn('user_auth', array(
            'header' => Mage::helper('bakerloo_restful')->__('Sales rep. (override)'),
            'index'  => 'user_auth',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('bakerloo_restful')->__('Date Added'),
            'index'     => 'created_at',
            'type'      => 'datetime'
        ));

        $this->addColumn('action', array(
            'header'  => Mage::helper('bakerloo_restful')->__('Action'),
            'width'   => '180px',
            'type'    => 'action',
            'align'   => 'center',
            'getter'  => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('bakerloo_restful')->__('Delete'),
                    'url'     => array('base' => 'adminhtml/pos_quotes/delete'),
                    'field'   => 'id',
                    'confirm' => Mage::helper('bakerloo_restful')->__('Are you sure?')
                ),
            ),
            'filter'    => false,
            'sortable'  => false,
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
    public function getRowUrl($log) {
        return false;
    }

}