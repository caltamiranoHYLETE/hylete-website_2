<?php


class Ebizmarts_BakerlooReports_Block_Adminhtml_Pos_Reports_View_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();

        $this->setId('bakerloo_report_view');
        $this->setDefaultSort('order_date');
    }

    protected function _prepareCollection(){

        $current = Mage::registry('bakerloo_reports_current');

        if($current->getId())
            $collection = $current->getReportCollection();
        else
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bakerloo_reports')->__("Report doesn't exist.")
            );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function _prepareColumns(){
        $h = Mage::helper('bakerloo_reports');

        $current = Mage::registry('bakerloo_reports_current');
        $columns = $current->getColumnsConfig();

        foreach($columns as $column){

            if(isset($column['hidden']) and $column['hidden'] == true)
                continue;

            $colName = isset($column['name']) ? $column['name'] : $column['value'];

            $gridColumn = array(
                'header' => $column['label'],
                'index' => $colName
            );

            if(isset($column['type'])) {
                $gridColumn['type'] = $column['type'];

                if($column['type'] == 'currency')
                    $gridColumn['currency'] = 'currency_code';
            }
            if(isset($column['renderer']))
                $gridColumn['renderer'] = $column['renderer'];
            if(isset($column['filter']))
                $gridColumn['filter'] = (bool)$column['filter'];
            if(isset($column['sortable']))
                $gridColumn['sortable'] = (bool)$column['sortable'];

            if(isset($column['after']) and in_array($column['after'], $columns))
                $this->addColumnAfter($column, $gridColumn, $column['after']);
            else
                $this->addColumn($colName, $gridColumn);
        }

        $this->addColumn('delete', array(
            'header' => $h->__("Delete"),
            'type' => 'action',
            'align' => 'center',
            'width' => '40px',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $h->__('Delete'),
                    'url' => array('base' => 'adminhtml/pos_reports_view/delete', 'params' => array('_current' => true)),
                    'field' => 'row_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', $h->__('CSV'));

        return parent::_prepareColumns();
    }

    public function format($word){
        $word = preg_replace("/_/", " ", $word);
        $word = preg_replace("/^bakerloo /", "", $word);
        return ucwords($word);
    }

}