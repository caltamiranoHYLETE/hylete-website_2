<?php


class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Shifts_Edit_Tab_Transactions_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('shift_transactions');
        $this->setUseAjax(true);
        $this->setDefaultSort('activity_date');
    }

    protected function _prepareCollection() {
        $shift = Mage::registry('pos_shift');

        $collection = Mage::getModel('bakerloo_restful/shift_activity')
            ->getCollection()
            ->addFieldToFilter('shift_id', array('eq' => $shift->getId()))
            ->addFieldToFilter('type', array('eq' => Ebizmarts_BakerlooRestful_Model_Activity::TYPE_TRANSACTION))
            ->setOrder('activity_date', 'asc');

        foreach($collection as $_act) {
            $_movs = Mage::getModel('bakerloo_restful/shift_movement')
                ->getCollection()
                ->addFieldToFilter('activity_id', array('eq' => $_act->getId()))
                ->getItems();

            $_act->setMovements($_movs);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $h = Mage::helper('bakerloo_restful');

        $this->addColumn('type', array(
            'header' => $h->__('Method'),
            'index'  => 'type',
            'renderer' => 'bakerloo_restful/adminhtml_widget_grid_column_renderer_activityType',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('transaction_date', array(
            'header'    => $h->__('Date'),
            'index'     => 'activity_date',
            'type'      => 'datetime'
        ));

        $this->addColumn('movements', array(
            'header' => $h->__('Amounts'),
            'index'  => 'movements',
            'renderer' => 'bakerloo_restful/adminhtml_widget_grid_column_renderer_shiftMovements',
            'filter' => false,
            'sortable' => false
        ));

        $this->addColumn('comments', array(
            'header' => $h->__('Comments'),
            'index'  => 'comments',
            'renderer' => 'bakerloo_restful/adminhtml_widget_grid_column_renderer_comment'
        ));

        return parent::_prepareColumns();
    }

}