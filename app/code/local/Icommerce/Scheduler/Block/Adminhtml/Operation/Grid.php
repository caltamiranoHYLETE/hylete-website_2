<?php

class Icommerce_Scheduler_Block_Adminhtml_Operation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('operation_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('scheduler/operation')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $schedulerOperations = Mage::helper('scheduler')->getDefinedSchedulerOperations();
        $items = $this->getCollection()->getItems();
        foreach ($items as &$item) {
            $code = $item->getCode();
            if (isset($schedulerOperations[$code]['label'])) {
                $item->setCode($schedulerOperations[$code]['label']);
            }
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('scheduler')->__('ID'),
            'align'  => 'right',
            'width'  => '100px',
            'index'  => 'id',
            'column_css_class' => 'id',
        ));

        $this->addColumn('code', array(
            'header' => Mage::helper('scheduler')->__('Task'),
            'align'  => 'left',
            'index'  => 'code',
            'frame_callback' => array($this, 'decorateCode')
        ));

//        $this->addColumn('name', array(
//            'header' => Mage::helper('scheduler')->__('Name'),
//            'align'  => 'left',
//            'index'  => 'name',
//        ));

        $this->addColumn('frequency', array(
            'header' => Mage::helper('scheduler')->__('Frequency'),
            'align'  => 'left',
            'width'  => '200px',
            'index'  => 'frequency',
            'frame_callback' => array($this, 'decorateFrequency'),
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('next_run', array(
            'header' => Mage::helper('scheduler')->__('Next Run'),
            'align'  => 'left',
            'width'  => '200px',
            'index'  => 'next_run',
            'type'   => 'datetime',
            'frame_callback' => array($this, 'decorateNextRun'),
            'column_css_class' => 'next-run',
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Status'),
            'width'     => '120px',
            'align'     => 'left',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::helper('scheduler')->getOperationStatusesOptionArray(),
            'frame_callback' => array($this, 'decorateStatus'),
            'column_css_class' => 'status',
        ));

        $this->addColumn('last_run', array(
            'header' => Mage::helper('scheduler')->__('Last Run'),
            'align'  => 'left',
            'width'  => '200px',
            'index'  => 'last_run',
            'type'   => 'datetime',
            'frame_callback' => array($this, 'decorateLastRun'),
            'column_css_class' => 'last-run',
        ));

        $this->addColumn('last_status', array(
            'header'    => $this->__('Run Status'),
            'width'     => '120px',
            'align'     => 'left',
            'index'     => 'last_status',
            'type'      => 'options',
            'options'   => Mage::helper('scheduler')->getHistoryStatusesOptionArray(),
            'frame_callback' => array($this, 'decorateLastStatus'),
            'column_css_class' => 'last-status',
        ));

        $actions = array();

        if (Mage::getIsDeveloperMode() && Mage::getSingleton('admin/session')->isAllowed('icommerce/scheduler/operations/actions/run')) {
            $actions[] = array(
                'caption' => Mage::helper('scheduler')->__('Run'),
                'url' => array('base' => '*/*/run'),
                'field' => 'id',
            );
        }

        if (Mage::getSingleton('admin/session')->isAllowed('icommerce/scheduler/operations/actions/edit')) {
            $actions[] = array(
                'caption' => Mage::helper('scheduler')->__('Schedule to run ASAP'),
                'url' => array('base' => '*/*/schedule'),
                'field' => 'id',
            );
        }

        if ($actions) {
            $this->addColumn('action', array(
                'header' => Mage::helper('scheduler')->__('Action'),
                'width' => '80',
                'align' => 'center',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => $actions,
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        }

        return parent::_prepareColumns();
    }

    public function decorateCode($value, $row, $column, $isExport)
    {
        $title = new Varien_Object();
        $title->setMainTitle($this->escapeHtml($row->getCode()));
        $title->setSubTitle($this->escapeHtml($row->getComment()));

        Mage::dispatchEvent('scheduler_create_grid_operation_title', array('title' => $title));

        $result = $title->getMainTitle();

        if ($row->getComment()) {
            $result .= '<p style="font-size: .9em; color:#67767E"><span>' . $title->getSubTitle() . '</span></p>';
        }

        return $result;
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        return Mage::helper('scheduler')->getStatusHtml($row);
    }

    public function decorateLastStatus($value, $row, $column, $isExport)
    {
        return Mage::helper('scheduler')->getLastStatusHtml($row->getLastStatus());
    }

    public function decorateNextRun($value, $row, $column, $isExport)
    {
        if ($row->getRunAsap()) {
            $cell = $this->__('ASAP');
        } else if ($row->getNextRun() != '0000-00-00 00:00:00') {
            $cell = $value;
        } else {
            $cell = '';
        }

        return $cell;
    }

    public function decorateLastRun($value, $row, $column, $isExport)
    {
        if ($row->getLastRun() != '0000-00-00 00:00:00') {
            $cell = $value;
        } else {
            $cell = '';
        }

        return $cell;
    }

    public function decorateFrequency($value, $row, $column, $isExport)
    {
        $recurrenceInfo = Mage::helper('scheduler')->addTimezoneOffsetToRecurrence($row->getRecurrenceInfo());

        switch ($recurrenceInfo['frequency']) {
            case Icommerce_Scheduler_Helper_Data::FREQUENCY_ONCE:
                $result = '';
                break;
            case Icommerce_Scheduler_Helper_Data::FREQUENCY_MINUTE:
                if ($recurrenceInfo['n'] == 1) {
                    $result = Mage::helper('scheduler')->__('Every minute');
                } else {
                    $result = Mage::helper('scheduler')->__('Every %d minutes', $recurrenceInfo['n']);
                }
                break;
            case Icommerce_Scheduler_Helper_Data::FREQUENCY_HOUR:
                if ($recurrenceInfo['n'] == 1) {
                    $result = Mage::helper('scheduler')->__('Every hour on %02d minute(s)', $recurrenceInfo['minute']);
                } else {
                    $result = Mage::helper('scheduler')->__('Every %d hours on %02d minute(s)', $recurrenceInfo['n'], $recurrenceInfo['minute']);
                }
                break;
            case Icommerce_Scheduler_Helper_Data::FREQUENCY_DAY:
                if ($recurrenceInfo['n'] == 1) {
                    $result = Mage::helper('scheduler')->__('Every day at %d:%02d', $recurrenceInfo['hour'], $recurrenceInfo['minute']);
                } else {
                    $result = Mage::helper('scheduler')->__('Every %d days at %d:%02d', $recurrenceInfo['n'], $recurrenceInfo['hour'], $recurrenceInfo['minute']);
                }
                break;
            case Icommerce_Scheduler_Helper_Data::FREQUENCY_WEEK:
                $weekday = Mage::helper('scheduler')->getOperationRecurrenceOptionArray('weekday');
                if ($recurrenceInfo['n'] == 1) {
                    $result = Mage::helper('scheduler')->__('Every week on %s at %d:%02d', $weekday[$recurrenceInfo['weekday']], $recurrenceInfo['hour'], $recurrenceInfo['minute']);
                } else {
                    $result = Mage::helper('scheduler')->__('Every %d weeks on %s at %d:%02d', $recurrenceInfo['n'], $weekday[$recurrenceInfo['weekday']], $recurrenceInfo['hour'], $recurrenceInfo['minute']);
                }
                break;
            case Icommerce_Scheduler_Helper_Data::FREQUENCY_MONTH:
                if ($recurrenceInfo['n'] == 1) {
                    $result = Mage::helper('scheduler')->__('Every month on day %d at %d:%02d', $recurrenceInfo['day'], $recurrenceInfo['hour'], $recurrenceInfo['minute']);
                } else {
                    $result = Mage::helper('scheduler')->__('Every %d months on day %d at %d:%02d', $recurrenceInfo['n'], $recurrenceInfo['day'], $recurrenceInfo['hour'], $recurrenceInfo['minute']);
                }
                break;
            case Icommerce_Scheduler_Helper_Data::FREQUENCY_YEAR:
                $month = Mage::helper('scheduler')->getOperationRecurrenceOptionArray('month');
                if ($recurrenceInfo['n'] == 1) {
                    $result = Mage::helper('scheduler')->__('Every year on %s %d at %d:%02d', $month[$recurrenceInfo['month']], $recurrenceInfo['day'], $recurrenceInfo['hour'], $recurrenceInfo['minute']);
                } else {
                    $result = Mage::helper('scheduler')->__('Every %d years on %s %d at %d:%02d', $recurrenceInfo['n'], $month[$recurrenceInfo['month']], $recurrenceInfo['day'], $recurrenceInfo['hour'], $recurrenceInfo['minute']);
                }
                break;
            default:
                $result = '';
                break;
        }
        if ($row->getMasterId()>0) {
            $result = Mage::helper('scheduler')->__('After task %s, order %s', $row->getMasterId(), $row->getMasterOrder());
        }

        return $result;
    }

    protected function _prepareMassaction()
    {
        if (Mage::getSingleton('admin/session')->isAllowed('icommerce/scheduler/operations/actions/delete')) {
            $this->setMassactionIdField('id');
            $this->getMassactionBlock()->setFormFieldName('scheduler');

            $this->getMassactionBlock()->addItem('delete', array(
                'label' => Mage::helper('scheduler')->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('scheduler')->__('Are you sure?')
            ));
        }

        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('icommerce/scheduler/operations/actions/edit')) {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        } else {
            return '';
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getAdditionalJavaScript()
    {
        if ($interval = (int)Mage::getStoreConfig('scheduler/settings/refresh_interval')) {
            return 'setInterval(function(){refreshOperations("' . $this->getUrl('*/*/refresh') . '")}, ' . $interval * 1000 . ');';
        }

        return '';
    }
}