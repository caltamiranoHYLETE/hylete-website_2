<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Debug_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('bakerloo_restful_debug');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('debug_at');
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('bakerloo_restful/debug')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('debug_at', array(
            'header' => Mage::helper('bakerloo_restful')->__('Time'),
            'index' => 'debug_at',
            'type' => 'datetime',
        ));
        $this->addColumn('request_method', array(
            'header' => Mage::helper('bakerloo_restful')->__('Method'),
            'index' => 'request_method',
        ));
        $this->addColumn('resource', array(
            'header' => Mage::helper('bakerloo_restful')->__('Resource'),
            'index' => 'resource',
        ));
        $this->addColumn('remote_addr', array(
            'header' => Mage::helper('bakerloo_restful')->__('IP'),
            'index' => 'remote_addr',
            'renderer'  => 'adminhtml/customer_online_grid_renderer_ip',
            'filter'    => false,
            'sort'      => false
        ));
        $this->addColumn('response_code', array(
            'header' => Mage::helper('bakerloo_restful')->__('Response Code'),
            'index' => 'response_code',
        ));
        $this->addColumn('user_agent', array(
            'header' => Mage::helper('bakerloo_restful')->__('User Agent'),
            'index' => 'user_agent',
            'filter'    => false,
            'sort'      => false
        ));
        $this->addColumn('call_time', array(
            'header' => Mage::helper('bakerloo_restful')->__('Call Time'),
            'index' => 'call_time',
            'align' => 'right',
            'filter' => false,
            //'default' => '--'
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('bakerloo_restful')->__('Action'),
            'width' => '80px',
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('bakerloo_restful')->__('view full'),
                    'url'     => array('base' => 'adminhtml/pos_debug/view'),
                    'field'   => 'id',
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
    public function getRowUrl($log) {
        return false;
    }

}