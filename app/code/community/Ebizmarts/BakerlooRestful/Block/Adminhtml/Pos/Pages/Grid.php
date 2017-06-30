<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Pages_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('bakerlooNotifications');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at', 'desc');
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('bakerloo_restful/staticfile')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('bakerloo_restful')->__('ID'),
            'index'  => 'id',
            'type'   => 'number',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('bakerloo_restful')->__('Title'),
            'index'  => 'title',
        ));

        $this->addColumn('description', array(
            'header' => Mage::helper('bakerloo_restful')->__('Description'),
            'index'  => 'description',
            'width'  => '300px',
        ));

        $this->addColumn('severity', array(
            'header'   => Mage::helper('bakerloo_restful')->__('Severity'),
            'index'    => 'severity',
            'renderer' => 'adminhtml/notification_grid_renderer_severity',
        ));

        /*if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('bakerloo_restful')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
            ));
        }*/

        $this->addColumn('date_added', array(
            'header'    => Mage::helper('bakerloo_restful')->__('Date Added'),
            'index'     => 'date_added',
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
                    'url'     => array('base' => 'adminhtml/pos_notifications/delete'),
                    'field'   => 'id',
                    'confirm' => Mage::helper('bakerloo_restful')->__('Are you sure?')
                ),
                array(
                    'caption' => Mage::helper('bakerloo_restful')->__('Mark as Read'),
                    'url'     => array('base' => 'adminhtml/pos_notifications/markread'),
                    'field'   => 'id',
                    'confirm' => Mage::helper('bakerloo_restful')->__('Are you sure?')
                ),
                array(
                    'caption' => Mage::helper('bakerloo_restful')->__('Read Details'),
                    'url'     => array('base' => 'adminhtml/pos_notifications/delete'),
                    'field'   => 'id',
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
     * Prepare mass action
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('notification_id');
        $this->getMassactionBlock()->setFormFieldName('notification');

        $this->getMassactionBlock()->addItem('mark_as_read', array(
            'label'    => Mage::helper('bakerloo_restful')->__('Mark as Read'),
            'url'      => $this->getUrl('*/*/massMarkAsRead', array('_current'=>true)),
        ));

        $this->getMassactionBlock()->addItem('remove', array(
            'label'    => Mage::helper('bakerloo_restful')->__('Remove'),
            'url'      => $this->getUrl('*/*/massRemove'),
            'confirm'  => Mage::helper('bakerloo_restful')->__('Are you sure?')
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
        return $this->getUrl('*/*/edit', array('id' => $item->getId()));
    }

    public function getRowClass(Varien_Object $row) {
        return $row->getIsRead() ? 'read' : 'unread';
    }

}