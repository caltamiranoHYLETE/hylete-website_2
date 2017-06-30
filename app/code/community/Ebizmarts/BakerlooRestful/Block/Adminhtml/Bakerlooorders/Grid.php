<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Bakerlooorders_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('bakerlooOrders');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('id');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('bakerloo_restful/order')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('bakerloo_restful')->__('ID'),
            'index' => 'id',
            'type' => 'number',
        ));
        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('bakerloo_restful')->__('Order #'),
            'index' => 'order_increment_id',
            'renderer' => 'bakerloo_restful/adminhtml_widget_grid_column_renderer_orderNumber',
        ));
        $this->addColumn('device_order_id', array(
            'header' => Mage::helper('bakerloo_restful')->__('Device Order #'),
            'index' => 'device_order_id',
        ));
        /*$this->addColumn('device_id', array(
            'header' => Mage::helper('bakerloo_restful')->__('Device'),
            'index' => 'device_id',
        ));*/
        $this->addColumn('remote_ip', array(
            'header' => Mage::helper('bakerloo_restful')->__('Remote IP'),
            'index' => 'remote_ip',
        ));
        $this->addColumn('order_payment_method', array(
            'header'   => Mage::helper('bakerloo_restful')->__('Payment Method'),
            'index'    => 'order_payment_method',
            'renderer' => 'bakerloo_restful/adminhtml_widget_grid_column_renderer_orderPaymentMethod',
            'filter' => false,
            'sortable' => false,
        ));
        $this->addColumn('subtotal', array(
            'header' => Mage::helper('bakerloo_restful')->__('Gross'),
            'index' => 'subtotal',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        $this->addColumn('discount_amount', array(
            'header' => Mage::helper('bakerloo_restful')->__('Discount'),
            'index' => 'discount_amount',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        $this->addColumn('tax_amount', array(
            'header' => Mage::helper('bakerloo_restful')->__('Tax'),
            'index' => 'tax_amount',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        $this->addColumn('grand_total', array(
            'header' => Mage::helper('bakerloo_restful')->__('Total'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        $this->addColumn('refunded', array(
            'header'   => Mage::helper('bakerloo_restful')->__('Refunded'),
            'index'    => 'refunded',
            'renderer' => 'bakerloo_restful/adminhtml_widget_grid_column_renderer_orderIsRefunded',
            'filter'   => false,
            'sortable' => false,
        ));
        $this->addColumn('admin_user', array(
            'header' => Mage::helper('bakerloo_restful')->__('User'),
            'index' => 'admin_user',
        ));
        /*$this->addColumn('admin_user_auth', array(
            'header' => Mage::helper('bakerloo_restful')->__('Super User'),
            'index' => 'admin_user_auth',
        ));*/
        $this->addColumn('created_at', array(
            'header' => Mage::helper('bakerloo_restful')->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'format' => 'yyyy-MM-dd HH:mm:ss'
        ));
        /*$this->addColumn('updated_at', array(
            'header' => Mage::helper('bakerloo_restful')->__('Updated At'),
            'index' => 'updated_at',
            'type' => 'datetime',
        ));*/

        $this->addColumn('action', array(
            'header' => Mage::helper('bakerloo_restful')->__('Action'),
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                /*array(
                    'caption' => Mage::helper('bakerloo_restful')->__('Try Again'),
                    'url' => array('base' => 'adminhtml/bakerlooorders/place'),
                    'field' => 'id',
                    'confirm' => Mage::helper('bakerloo_restful')->__('Are you sure? This will try to create a new order in Magento.')
                ),*/
                array(
                    'caption' => Mage::helper('bakerloo_restful')->__('View'),
                    'url' => array('base' => 'adminhtml/bakerlooorders/edit'),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('bakerloo_restful')->__('CSV'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Prepare mass action
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('order_id');
        $this->getMassactionBlock()->setFormFieldName('order');

        $this->getMassactionBlock()->addItem('try_again', array(
            'label'    => Mage::helper('bakerloo_restful')->__('Try Again'),
            'url'      => $this->getUrl('*/*/massPlace', array('_current'=>true)),
        ));

        return $this;
    }

    /**
     * Return row url for js event handlers
     *
     * @param Varien_Object
     * @return string
     */
    public function getRowUrl($item) {
        return false;
    }

    /**
     * Set a different color for not saved orders.
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowClass(Varien_Object $row) {
        return $row->getOrderId() ? 'read' : 'unread';
    }

}