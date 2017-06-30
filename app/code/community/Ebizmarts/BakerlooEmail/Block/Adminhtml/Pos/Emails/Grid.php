<?php

class Ebizmarts_BakerlooEmail_Block_Adminhtml_Pos_Emails_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('bakerloo_emails');
        $this->setUseAjax(true);
        $this->setDefaultSort('updated_at', 'desc');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('bakerloo_email/queue')->getCollection();
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

        $this->addColumn('order_id', array(
            'header' => $h->__('Order #'),
            'index' => 'order_id',
            'renderer' => 'bakerloo_restful/adminhtml_widget_grid_column_renderer_orderNumber',
            'filter' => false
        ));

        $this->addColumn('to_email', array(
            'header' => $h->__('Email'),
            'index'  => 'to_email',
        ));

        $this->addColumn('email_type', array(
            'header' => $h->__('Email type'),
            'index'  => 'email_type',
            'type' => 'options',
            'options' => Mage::getModel('bakerloo_restful/adminhtml_system_config_source_receipts')->toArray()
        ));

        $optionsYesNoNA = array(
            '' => $h->__('N/A'),
            '0' => $h->__('No'),
            '1' => $h->__('Yes'),
        );

        $this->addColumn('subscribe_to_newsletter', array(
            'header' => $h->__('Subscribe to newsletter'),
            'index'  => 'subscribe_to_newsletter',
            'type' => 'options',
            'options' => $optionsYesNoNA
        ));

        $this->addColumn('email_result', array(
            'header' => $h->__('Email sent'),
            'index'  => 'email_result',
            'type' => 'options',
            'options' => $optionsYesNoNA
        ));

        $this->addColumn('error_message', array(
            'header' => $h->__('Error'),
            'index'  => 'error_message',
            'width' => '50px',
        ));

        $this->addColumn('created_at', array(
            'header'    => $h->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime'
        ));

        $this->addColumn('updated_at', array(
            'header'    => $h->__('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime'
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
    public function getRowUrl($queue) {
        return false;
    }

}