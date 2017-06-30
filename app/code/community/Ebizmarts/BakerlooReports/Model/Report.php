<?php

class Ebizmarts_BakerlooReports_Model_Report extends Mage_Core_Model_Abstract {

    protected $_identifier  = 'order_id';
    protected $_reportName  = 'Report';
    protected $_groupBy     = '';

    protected $_columns;
    protected $_dataSources;
    protected $_filters;

    protected $_model       = 'bakerloo_restful/order';
    protected $_lastRecord  = 0;
    protected $_pageSize    = 50;

    public function _construct() {
        $this->_init('bakerloo_reports/report');
    }

    public function getReportCollection(){
        $tableName = $this->getTableName();
        $reader = Mage::getSingleton('core/resource')->getConnection('core_read');

        if(!$reader->isTableExists($tableName))
            return new Varien_Data_Collection();

        $collection = Mage::getModel('bakerloo_reports/collection', $reader);
        $collection->getSelect()->from(array('main_table' => $tableName));
        return $collection;
    }

    public function getColumns(){
        if(!isset($this->_columns)) {

            $dataSources = unserialize($this->getDataSources());

            if (!is_array($dataSources))
                $this->_columns = array();
            else
                $this->_columns = array_keys($dataSources);

        }

        return $this->_columns;
    }

    public function getColumnTitles() {
        $columnData = Mage::helper('bakerloo_reports')->getAllColumnsWithoutSource();
        $columns = $this->getColumns();

        $result = array();

        foreach($columns as $col) {
            if(isset($columnData[$col]))
                $result[] = $columnData[$col]['label'];
        }

        return $result;
    }

    public function getColumnsConfig() {

        $config = Mage::helper('bakerloo_reports')->getAllColumnsByName();

        $columns = array();

        foreach($this->getColumns() as $key => $val) {

            if(!is_array($val))
                $key = $val;

            if(array_key_exists($key, $config))
                $columns[$key] = $config[$key];

        }

        return $columns;
    }

    public function regenerate() {
        /** @var $writer Varien_Db_Adapter_Interface */
        $writer = $this->getWriter();
        $writer->truncateTable($this->getTableName());

        $this->populate($writer);

        return $this;
    }

    /**
     * Remove duplicate records from POS orders' reports selecting by order_id.
     *
     * @return mixed the number of deleted rows
     */
    public function checkDuplicates() {

        /** @var $writer Varien_Db_Adapter_Interface */
        $writer = $this->getWriter();
        $query = $this->_getDeleteDuplicateQuery();
        $result = $writer->query($query);

//        Mage::log("Deleted {$result->rowCount()} duplicate rows from table {$this->getTableName()}.", null, 'BakerlooReports.log', true);

        return $result->rowCount();
    }

    protected function _getDeleteDuplicateQuery() {
        return "DELETE r1 FROM {$this->getTableName()} r1, {$this->getTableName()} r2 WHERE r1.{$this->_identifier}=r2.{$this->_identifier} AND r1.id < r2.id";
    }

    public function getPopulateCollection() {
        return Mage::getModel($this->_model)->getCollection();
    }

    public function populate(Varien_Db_Adapter_Interface $writer){

        Varien_Profiler::start('POS::' . __METHOD__);

        $dataSources = unserialize($this->getDataSources());
        $filters = unserialize($this->getFilters());
        if(!isset($dataSources) or empty($dataSources))
            Mage::throwException(Mage::helper('bakerloo_reports')->__("No data sources defined for report {$this->_reportName}, ID: {$this->getId()}. "));

        /* @var $_iterator Mage_Core_Model_Resource_Iterator */
        $iterator = Mage::getSingleton('core/resource_iterator');
        $collection = $this->getPopulateCollection();

        if($filters) {
            foreach($filters as $filter)
                $collection->addFieldToFilter($filter[0], $filter[1]);
        }

        $this->_dataSources = $dataSources;
        $this->_columns = array_keys($this->_dataSources);

        $table = $this->getTableName();

        //filter orders that have already been inserted in the report
        $existing = $writer->fetchCol("SELECT {$this->_identifier} FROM {$table}");
        if(!empty($existing))
            $collection->addFieldToFilter($this->_identifier, array('nin' => $existing));

        //Paginate orders collection to speed up process
        $collection->setPageSize($this->_pageSize)
            ->setCurPage(1);

        $iterator->walk($collection->getSelect(), array(array($this, 'loadData')), array('writer' => $writer, 'table' => $table));

        $this->_hasDataChanges = true;
        $this->save();

        Varien_Profiler::stop('POS::' . __METHOD__);
    }

    public function loadData($args = array()){

        Varien_Profiler::start('POS::' . __METHOD__);

        try {
            $row = $args['row'];
            if (is_null($row[$this->_identifier]) or $row[$this->_identifier] < 1) //skip orders that failed to enter Magento
                return;

            $id = $row[$this->_identifier];

            $table = $args['table'];
            $writer = $args['writer'];
            if (!$writer or !$table)
                return;

            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->load($id);
            if (!$order->getId())
                return;

            $select = $writer->select()
                ->from($table)
                ->where('order_id = ?', (int)$order->getId());

            $repeat = $writer->fetchRow($select);
            if (isset($repeat[$this->_identifier]))
                return;

            $payload = Mage::helper('core')->decrypt($row['json_payload_enc']);
            $payload = json_decode($payload);

            $hasMultiplePayments = $order->getPayment()->getMethod() == 'bakerloo_multiple'; //Ebizmarts_BakerlooPayment_Model_Multiple;
            $payments = array();
            if ($hasMultiplePayments and isset($payload->payment))
                $payments = !is_null($payload->payment->addedPayments) ? $payload->payment->addedPayments : array();

            $data = array('id' => 'NULL');

            foreach ($this->_columns as $_col) {

                if ($_col != 'id') {

                    $definition = $this->_dataSources[$_col];
                    $model = isset($definition['model']) ? $definition['model'] : null;
                    $column = isset($definition['field']) ? $definition['field'] : null;
                    $condition = isset($definition['condition']) ? $definition['condition'] : null;
                    $calculated = isset($definition['calculation']) ? $definition['calculation'] : null;

                    if (!is_null($model)) {

                        if ($model == $this->_model) {

                            $data[$_col] = $row[$column];

                        } elseif ($model == 'jsonPayload') {
                            if (is_null($payload))
                                continue;

                            $payment_condition = isset($condition['path']) and is_array($condition['path']) ? array_search('payment', $condition['path']) : false;
                            $payment_column = isset($column['path']) and is_array($column['path']) ? array_search('payment', $column['path']) : false;

                            if ($hasMultiplePayments and $payment_condition !== false and $payment_column !== false)
                                $data[$_col] = $this->getValueFromMultiplePayments($payments, $condition, $column);

                            else {
                                $add = true;

                                if (!is_null($condition)) {
                                    $range = $this->getValueFromJson($payload, $condition['path']);
                                    list($comp, $value) = $condition['cond'];

                                    if ($range != $value)
                                        $add = false;
                                }

                                if ($add === true) {
                                    if (is_string($column))
                                        $data[$_col] = $payload->$column;
                                    else
                                        $data[$_col] = $this->getValueFromJson($payload, $column['path']);
                                }
                            }
                        } elseif ($model == 'sales/order') {
                            $get = $this->getCamelized($column);
                            $data[$_col] = $order->$get();
                        } else {
                            $model = Mage::getModel($model);
                            $searchBy = isset($definition['searchby']) ? $definition['searchby'] : null;

                            if (!is_null($searchBy))
                                $model = $model->load($id, $searchBy);
                            else
                                $model = $model->load($id);

                            if ($model->getId()) {
                                $get = $this->getCamelized($column);
                                $data[$_col] = $model->$get();
                            }
                        }
                    } elseif (!is_null($calculated)) {
                        $result = 0;

                        if (array_key_exists('op', $calculated)) {
                            $calculated = array(
                                array('op' => $calculated['op'], 'path' => $calculated['path'])
                            );
                        }

                        foreach ($calculated as $_cal) {
                            $op = $_cal['op']; //array_keys($_cal)[0];
                            $operand = $_cal['path'];

                            if (is_array($operand))
                                $amt = $this->getValueFromJson($payload, $operand);
                            else
                                $amt = isset($data[$operand]) ? $data[$operand] : 0;

                            switch ($op) {
                                case 'sum':
                                    $result += $amt;
                                    break;
                                case 'subtract':
                                    $result -= $amt;
                                    break;
                            }
                        }

                        $data[$_col] = $result;
                    }
                }
            }

            if (!empty($data))
                $writer->insert($table, $data);

            $this->_lastRecord = (int)$id;
        }
        catch(Exception $ex) {
            Mage::log("Insert failed on table {$table}: " . $ex->getMessage(), null, 'BakerlooReports.log', true);
        }

        Varien_Profiler::stop('POS::' . __METHOD__);
    }

    protected function getValueFromJson($json, $path){
        $value = null;

        if(is_string($path))
            $value = $json->$path;
        elseif(is_array($path)){
            $_v = $json;

            foreach($path as $_p) {
                if($_p == 'product') {
                    $offset = array_search($_p, $path) + 1;
                    $subPath = array_slice($path, $offset, null, true);
                    $_v = $this->getValueFromProducts($_v, $subPath);
                    break;
                }
                else
                    $_v = isset($_v->$_p) ? $_v->$_p : null;
            }

            $value = $_v;
        }

        return $value;
    }

    protected function getValueFromProducts($json, $path){
        if(!isset($json->products))
            return;

        $products = $json->products;
        $sum = 0;

        foreach($products as $_prod)
            $sum += $this->getValueFromJson($_prod, $path);

        return $sum;
    }

    public function getValueFromMultiplePayments($payments = array(), $condition, $path){
        $condition_key = array_search('payment', $condition['path']);
        if($condition_key !== false)
            $condition['path'] = array_slice($condition['path'], $condition_key+1);

        if(is_array($path)) {
            $path = $path['path'];
            $path_key = array_search('payment', $path);
            if ($path_key !== false)
                $path = array_slice($path, $path_key+1);
        }

        $result = 0;
        foreach($payments as $payment){

            if(!is_null($condition)){
                $field = $condition['path'];
                $range = $payment;

                foreach($field as $_f)
                    $range = isset($range->$_f) ? $range->$_f : null;

                list($comp, $_val) = $condition['cond'];

                if($range != $_val)
                    continue;
            }

            $value = 0;

            if (is_string($path))
                $value = $payment->$path;
            else {
                $value = $payment;

                foreach ($path as $_path)
                    $value = isset($value->$_path) ? $value->$_path : null;
            }

            $result += floatval($value);
        }

        return $result;
    }

    public function getCamelized($word){
        $word = $this->_camelize($word);
        return 'get' . $word;
    }

    public function drop(){
        $writer = $this->getWriter();
        Mage::getModel('bakerloo_reports/generator')->drop($this->getTableName(), $writer);
    }

    public function deleteRow($rowId) {
        $collection = $this->getReportCollection()
            ->addFieldToFilter('id', array('eq' => $rowId));
        $row = $collection->getFirstItem();

        //Mage::log((string)$collection->getSelect(), null, 'BakerlooReports.log', true);

        if(!$row->getId())
            Mage::throwException(Mage::helper('bakerloo_reports')->__("Report item %s does not exist.", $rowId));

        /** @var $writer Varien_Db_Adapter_Interface */
        $writer = Mage::getSingleton('core/resource')->getConnection('core_write');
        $condition = $writer->prepareSqlCondition('id', array('eq' => $rowId));
        $writer->delete($this->getTableName(), $condition);
    }

    public function getCsvFile($fileName = null, $filters = array()){
        $collection = $this->getReportCollection();

        if(!empty($filters))
            $this->_applyFilters($collection, $filters);

//        Mage::log((string)$collection->getSelect(), null, 'BakerlooReports.log', true);

        $path = Mage::getBaseDir('var') . DS . 'pos_reports';

        if(isset($fileName))
            $file = $path . DS . $fileName . '.csv';
        else
            $file = $path . DS . $this->getFileName() . '.csv';

        $io = new Varien_Io_File();

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWriteCsv($this->getTableHeaders());

        /* @var $_iterator Mage_Core_Model_Resource_Iterator */
        $iterator = Mage::getSingleton('core/resource_iterator');
        $iterator->walk($collection->getSelect(), array(array($this, 'csvRow')), array('io' => $io));

        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true
        );

    }

    public function getTableHeaders() {
        return $this->getColumnTitles();
    }

    public function csvRow($args){
        $io = $args['io'];
        if(!$io) return;

        $row = $args['row'];
        $io->streamWriteCsv($row);
    }

    public function getFileName() {
        return $this->getTableName();
    }

    public function setFilters($filters) {
        $this->_filters = $filters;
    }


    protected function _applyFilters($collection, $filters) {

        foreach($filters as $attribute => $_filter) {

            if($attribute == 'order_date') {
                $from = isset($_filter['from']) ? $_filter['from'] : false;
                $to = isset($_filter['to']) ? $_filter['to'] : false;
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_FULL);

                if(!$from and !$to)
                    continue;

                if($from) {
                    $date = Mage::app()->getLocale()->date($from, $format, null, false);
                    $_filter['from'] = $date->toString(Varien_Date::DATE_INTERNAL_FORMAT);
                }

                if($to) {
                    $date = Mage::app()->getLocale()->date($to, $format, null, false);
                    $date->addDay(1);
                    $_filter['to'] = $date->toString(Varien_Date::DATE_INTERNAL_FORMAT);
                }
            }
            elseif(!is_array($_filter))
                $_filter = array('like' => '%'.$_filter.'%');

            $collection->addFieldToFilter($attribute, $_filter);
        }
    }

    public function getWriter(){
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    public function getReader(){
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }


}