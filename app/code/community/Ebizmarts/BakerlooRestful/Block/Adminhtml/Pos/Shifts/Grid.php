<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Shifts_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('bakerlooShifts');
        $this->setUseAjax(true);
        $this->setDefaultSort('id', 'desc');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('bakerloo_restful/shift')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        
        $h = Mage::helper('bakerloo_restful');
        
        $this->addColumn('id', array(
            'header' => $h->__('ID'),
            'index'  => 'id',
            'type'   => 'number',
        ));
        $this->addColumn('device_id', array(
            'header' => $h->__('Device ID'),
            'index'  => 'device_id',
        ));
        $this->addColumn('user', array(
            'header' => $h->__('User'),
            'index'  => 'user',
        ));
        $this->addColumn('state', array(
            'header' => $h->__('State'),
            'index'  => 'state',
            'type'  => 'options',
            'width' => '70px',
            'options' => $h->getShiftStates(),
        ));

        $this->addColumn('open_notes', array(
            'header' => $h->__('Open Notes'),
            'index'  => 'open_notes',
        ));
        $this->addColumn('open_date', array(
            'header'    => $h->__('Open Date'),
            'index'     => 'open_date',
            'type'      => 'datetime'
        ));
        $this->addColumn('close_notes', array(
            'header' => $h->__('Close Notes'),
            'index'  => 'close_notes',
        ));
        $this->addColumn('close_date', array(
            'header'    => $h->__('Close Date'),
            'index'     => 'close_date',
            'type'      => 'datetime'
        ));
        $this->addColumn('sales_amount', array(
            'header' => $h->__('Sales Amount'),
            'index'  => 'sales_amount',
            'type'  => 'currency',
            'currency' => 'sales_amount_currency'
        ));

        $this->addColumn('action', array(
            'header' => $h->__('Action'),
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $h->__('View'),
                    'url' => array('base' => 'adminhtml/pos_shifts/edit'),
                    'field' => 'id',
                    'target' => '_blank'
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

}