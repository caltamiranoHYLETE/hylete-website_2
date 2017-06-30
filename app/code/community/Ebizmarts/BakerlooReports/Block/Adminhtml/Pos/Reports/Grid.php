<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Pos_Reports_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){

        parent::__construct();
        $this->setId('bakerloo_reports');
    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('bakerloo_reports/report')->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
    }

    public function _prepareColumns(){
        $h = Mage::helper('bakerloo_reports');

        $this->addColumn('report_name', array(
            'header' => 'Report name',
            'index' => 'report_name'
        ));

        $this->addColumn('updated_at', array(
            'header' => $h->__("Last updated"),
            'index' => 'updated_at'
        ));

        $this->addColumn('view', array(
            'header' => $h->__('View'),
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $h->__('View'),
                    'url' => array('base' => 'adminhtml/pos_reports_view/'),
                    'target' => '_blank',
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        $this->addColumn('update', array(
            'header' => $h->__("Update"),
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $h->__('Update'),
                    'url' => array('base' => 'adminhtml/pos_reports/update'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        $this->addColumn('delete', array(
            'header' => $h->__("Delete"),
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $h->__('Delete'),
                    'url' => array('base' => 'adminhtml/pos_reports/delete'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        parent::_prepareColumns();
    }

    public function getGridUrl(){
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

}