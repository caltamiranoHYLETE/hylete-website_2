<?php

class Icommerce_Scheduler_Block_Adminhtml_Operation_Edit_Tab_History extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('history_grid');

        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

        $this->setId('scheduler_history_grid');
        $this->setTitle(Mage::helper('scheduler')->__('Task History'));
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /** @var $collection Icommerce_Scheduler_Model_Resource_History_Collection */
        $collection = Mage::getModel('scheduler/history')->getCollection();
        $collection->addFieldToSelect(array('created_at', 'finished_at', 'status', 'message'));
        $collection->addFieldToFilter('operation_id', Mage::registry('operation_data')->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('scheduler')->__('Id'),
            'align'     => 'right',
            'width'     => '100px',
            'index'     => 'id',

        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('scheduler')->__('Started'),
            'align'     => 'left',
            'width'     => '200px',
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('finished_at', array(
            'header'    => Mage::helper('scheduler')->__('Finished'),
            'align'     => 'left',
            'width'     => '200px',
            'index'     => 'finished_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('message', array(
            'header'    => Mage::helper('scheduler')->__('Message'),
            'align'     => 'left',
            'index'     => 'message',
        ));

        $this->addColumn('history_status', array(
            'header'    => Mage::helper('scheduler')->__('Status'),
            'align'     => 'left',
            'width'     => '120px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::helper('scheduler')->getHistoryStatusesOptionArray(),
            'frame_callback' => array($this, 'decorateStatus')
        ));

        return parent::_prepareColumns();
    }

    public function decorateStatus($value, $row, $column, $isExport)
    {
        return Mage::helper('scheduler')->getLastStatusHtml($row->getStatus());
    }

    public function getRowClickCallback()
    {
        return <<<JS
function rowClick(grid, event) {
    var element = Event.findElement(event, 'tr');
    SchedulerTools.openDialog(element.title);
}
JS;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/historyView', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/history', array('_current' => true));
    }

    public function getAdditionalJavaScript()
    {
        return <<<JS
SchedulerTools = {
    openDialog: function(url) {
        var win = new Window({
            className:'magento',
            url:url,
            title:'History Result',
            width:1000,
            height:600,
            destroyOnClose:true
        });
        win.showCenter(true);
        new Ajax.Updater('modal_dialog_message', url, {evalScripts: true});
    }
}
JS;
    }
}