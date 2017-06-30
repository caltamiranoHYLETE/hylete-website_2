<?php

class Ebizmarts_BakerlooReports_Model_Generator extends Mage_Core_Model_Abstract
{

    /**
     *
     * Generate a report table according to the given configuration.
     * @param $name
     * @param array $columns
     * @param array $dataSources
     * @param Varien_Db_Adapter_Interface $writer
     *
     */
    public function generate($name, $columns = array(), $dataSources = array(), $filters = array(), Varien_Db_Adapter_Interface $writer)
    {
        Varien_Profiler::start('POS::' . __METHOD__);

        if (!is_string($name))
            Mage::throwException('Table name must be string.');

        if ($writer->isTableExists($name))
            Mage::throwException("A table with that name already exists.");

        $table = $this->getTableName($name);
        $query = $this->getCreateQuery($table, $columns);
        $writer->query($query);

        if ($writer->isTableExists($table)) {
            $dataSources = $this->prepareDataSources($dataSources);
            $report = $this->saveReport($name, $query, $dataSources, $filters);
        }

        Varien_Profiler::stop('POS::' . __METHOD__);
    }

    /**
     * Drop the report with the specified name.
     *
     * @param $name
     * @param Varien_Db_Adapter_Interface $writer
     */
    public function drop($name, Varien_Db_Adapter_Interface $writer)
    {
        if ($writer->isTableExists($name))
            $writer->dropTable($name);
    }

    public function getCreateQuery($tableName, $columns)
    {
        $query = "CREATE TABLE IF NOT EXISTS `{$tableName}` ";

        if (is_array($columns) and !empty($columns)) {

            $query .= '(';

            foreach ($columns as $col_name => $col_def) {
                $query .= $col_name;

                if (is_array($col_def)) {
                    foreach ($col_def as $_k => $_def) {

                        if ($_def == 'PRIMARY KEY')
                            $primaryKey = $col_name;
                        else
                            $query .= ' ' . $_def . ', ';
                    }
                } elseif (is_string($col_def))
                    $query .= ' ' . $col_def . ', ';
            }
        }

        if (isset($primaryKey))
            $query .= 'PRIMARY KEY (`' . $primaryKey . '`)';
        if (isset($columns['order_id']))
            $query .= ', UNIQUE KEY (order_id)';

        $query .= ');';

        return $query;
    }

    public function saveReport($name, $query, $dataSources, $filters = null)
    {
        $dataSources = serialize($dataSources);
        $tableName = $this->getTableName($name);

        $report = Mage::getModel('bakerloo_reports/report')
            ->setReportName($name)
            ->setTableName($tableName)
            ->setCreateSql($query)
            ->setDataSources($dataSources);

        if (!is_null($filters))
            $report->setFilters(serialize($filters));

        $report->save();

        return $report;
    }

    public function getTableName($name)
    {
        $prefix = 'bakerloo_report_';
        $name = $this->_underscore($name);
        $name = preg_replace('/\s+/', '_', $name);

        return $prefix . $name;
    }

    public function prepareDataSources($dataSources)
    {
        //total_to_gross calculation depends on selected payment methods
        if (array_key_exists('total_to_gross', $dataSources)) {
            $bakerlooPaymentMethods = Mage::helper('bakerloo_reports')->getPaymentMethodColumns();
            $selectedPaymentMethods = array();

            foreach ($bakerlooPaymentMethods as $key => $value) {
                if (array_key_exists($key, $dataSources)) {
                    $selectedPaymentMethods[] = array('op' => 'sum', 'path' => $key);
                }
            }

            if (!empty($selectedPaymentMethods))
                $selectedPaymentMethods[] = array('op' => 'subtract', 'path' => 'grand_total');

            $dataSources['total_to_gross']['calculation'] = $selectedPaymentMethods;
        }

        return $dataSources;
    }

    public function updateAll()
    {
        Varien_Profiler::start('POS::' . __METHOD__);

        $enabled = Mage::getStoreConfig('bakerloorestful/reports_update/enabled', Mage::app()->getStore());

        if ($enabled) {
            $collection = Mage::getModel('bakerloo_reports/report')->getCollection();
            $h = Mage::helper('bakerloo_reports');

            /** @var $writer Varien_Db_Adapter_Interface */
            $writer = $this->getWriter();

            foreach ($collection as $report) {
                try {

                    $report = $h->loadReport($report);

                    $report->populate($writer);
                    $report->save();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        Varien_Profiler::stop('POS::' . __METHOD__);
    }

    public function generateDefault()
    {
        $reports = Mage::helper('bakerloo_reports')->getDefaultReportsConfig();
        $writer = $this->getWriter();

        foreach ($reports as $report) {
            $model = Mage::getModel('bakerloo_reports/report')->load($report['report_name'], 'report_name');

            if (!$model->getId()) {

                $dReport = Mage::getModel($report['report_model']);
                $columns = $dReport->getColumns();

                foreach ($columns as $key => $col) {
                    $col_def = isset($col['definition']) ? $col['definition'] : null;

                    if (is_array($col_def))
                        $columns[$key] = array_values($col_def);
                    else
                        $columns[$key] = $col_def;
                }

                $this->generate($report['report_name'], $columns, $dReport->getDataSourceConfiguration(), array(), $writer);
            }
        }
    }

    public function getWriter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $reports = Mage::helper('bakerloo_reports')->getDefaultReports();

        $ret = array();
        $ret[] = array('value' => '', 'label' => '');

        foreach($reports as $k => $v)
            $ret[] = array('value' => $k, 'label' => $v['report_name']);

        return $ret;
    }
}