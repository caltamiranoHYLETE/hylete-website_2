<?php

class Ebizmarts_BakerlooReports_Model_Observer extends Mage_Core_Model_Abstract {

    public function generateDefault(Varien_Event_Observer $observer){
        $block = $observer->getBlock();

        if($block instanceof Ebizmarts_BakerlooReports_Block_Adminhtml_Pos_Reports)
            Mage::getModel('bakerloo_reports/generator')->generateDefault();
    }

    protected function _profile($request) {

        $module = $request->getControllerModule() == 'Ebizmarts_BakerlooReports_Adminhtml';
        $action = $request->getActionName() == 'regenerate' || $request->getActionName() == 'update';
        $config = Mage::getStoreConfig('bakerloorestful/reports_update/profile', Mage::app()->getStore());

        return $module and $action and $config;
    }

    public function profilePre(Varien_Event_Observer $observer) {
        if($this->_profile($observer->getControllerAction()->getRequest())) {
            Mage::getSingleton('core/resource')->getConnection('core_write')->getProfiler()->setEnabled(TRUE);
            Varien_Profiler::enable();
        }
    }

    public function profilePost(Varien_Event_Observer $observer) {
        if($this->_profile($observer->getControllerAction()->getRequest())) {
            $this->logprofiler($observer->getControllerAction());
        }
    }

    public function logprofiler($action)
    {
        $logPath = Mage::getBaseDir('var') . DS . 'log' . DS . 'pos';
        $profilerPath = $logPath . DS . 'profiler';

        if (!is_dir($logPath)) {
            mkdir($logPath, 0755);
        }
        if (!is_dir($profilerPath)) {
            mkdir($profilerPath, 0755);
        }

        $timers = Varien_Profiler::getTimers();

        $request = $action->getRequest();
        $prefix = $request->getParam('vtxcode', $request->getParam('VPSTxId', null));
        $prefix = ($prefix ? $prefix . '_' : '');

        $longest = 10;
        $rows = array();
        foreach ($timers as $name => $timer) {

            $sum = Varien_Profiler::fetch($name, 'sum');
            $count = Varien_Profiler::fetch($name, 'count');
            $realmem = Varien_Profiler::fetch($name, 'realmem');
            $emalloc = Varien_Profiler::fetch($name, 'emalloc');
            if ($sum < .0010 && $count < 10 && $emalloc < 10000) {
                continue;
            }

            $rows [] = array((string)$name, (string)number_format($sum, 4), (string)$count, (string)number_format($emalloc), (string)number_format($realmem));
            $thislong = strlen($name);
            if ($thislong > $longest) {
                $longest = $thislong;
            }
        }

//        $longest = $longest == 0 ? 10 : $longest;

        //Create table
        $table = new Zend_Text_Table(array('columnWidths' => array($longest, 10, 6, 12, 12), 'decorator' => 'ascii'));

        //Memory
        $preheader = new Zend_Text_Table_Row();
        $real = memory_get_usage(true);
        $emalloc = memory_get_usage();
        $preheader->appendColumn(new Zend_Text_Table_Column('real Memory usage: ' . $real . ' ' . ceil($real / 1048576) . 'MB', 'center', 1));
        $preheader->appendColumn(new Zend_Text_Table_Column('emalloc Memory usage: ' . $emalloc . ' ' . ceil($emalloc / 1048576) . 'MB', 'center', 4));
        $table->appendRow($preheader);

        //Append Header
        $header = new Zend_Text_Table_Row();
        $header->appendColumn(new Zend_Text_Table_Column('Code Profiler', 'center'));
        $header->appendColumn(new Zend_Text_Table_Column('Time', 'center'));
        $header->appendColumn(new Zend_Text_Table_Column('Cnt', 'center'));
        $header->appendColumn(new Zend_Text_Table_Column('Emalloc', 'center'));
        $header->appendColumn(new Zend_Text_Table_Column('RealMem', 'center'));
        $table->appendRow($header);

        foreach ($rows as $row) {
            $table->appendRow($row);
        }

        //SQL profile
        $dbprofile = print_r(Varien_Profiler::getSqlProfiler(Mage::getSingleton('core/resource')->getConnection('core_write')), TRUE);
        $dbprofile = substr($dbprofile, 0, -4);
        $dbprofile = str_replace('<br>', "\n", $dbprofile);

        $preheaderlabel = new Zend_Text_Table_Row();
        $preheaderlabel->appendColumn(new Zend_Text_Table_Column('DATABASE', 'center', 5));
        $table->appendRow($preheaderlabel);
        $preheader = new Zend_Text_Table_Row();
        $preheader->appendColumn(new Zend_Text_Table_Column($dbprofile, 'left', 5));
        $table->appendRow($preheader);

        $date = Mage::getModel('core/date')->date('Y-m-d\.H-i-s');

        $file = new SplFileObject($profilerPath . DS . $prefix . $date . '_' . $action->getFullActionName() . '.txt', 'w');
        $file->fwrite($table);
    }
}