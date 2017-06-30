<?php


class Ebizmarts_BakerlooReports_Model_Default_PaymentType extends Ebizmarts_BakerlooReports_Model_Report {
    protected $_reportName = 'Payment Type';
    protected $_identifier = 'payment_method';
    protected $_groupBy = array('date(order_date)', 'payment_method', 'order_currency_code');


    public function __construct() {
        parent::__construct();
        $this->_columns = $this->getColumnsConfig();
    }

    public function getColumnsConfig() {

        $cols = array(
            'id' => array(
                'name' => 'id',
                'label' => 'Id',
                'definition' => array('int(11) NOT NULL auto_increment', 'PRIMARY KEY'),
                'source' => null,
                'hidden' => true
            ),
            'payment_method' => array(
                'name' => 'payment_method',
                'label' => 'Payment Method',
                'definition' => 'varchar(255)',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'payment_method'
                ),
                'renderer' => 'bakerloo_reports/adminhtml_widget_grid_column_renderer_orderPaymentMethod'
            ),
            'order_date' => array(
                'name' => 'order_date',
                'label' => 'Date',
                'definition' => 'date',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'order_date'
                ),
                'type' => 'date'
            ),
            'grand_total' => array(
                'name' => 'grand_total',
                'label' => 'Total',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'grand_total'
                ),
                'type' => 'currency'
            ),
            'subtotal' => array(
                'name' => 'subtotal',
                'label' => 'Subtotal',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'subtotal'
                ),
                'type' => 'currency'
            ),
            'tax_amount' => array(
                'name' => 'tax_amount',
                'label' => 'Tax Amount',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'tax_amount'
                ),
                'type' => 'currency'
            ),
            'base_grand_total' => array(
                'name' => 'base_grand_total',
                'label' => 'Base Total',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'base_grand_total'
                ),
                'type' => 'currency',
                'hidden' => true
            ),
            'base_subtotal' => array(
                'name' => 'base_subtotal',
                'label' => 'Base Subtotal',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'base_subtotal'
                ),
                'type' => 'currency',
                'hidden' => true
            ),
            'base_tax_amount' => array(
                'name' => 'base_tax_amount',
                'label' => 'Base Tax Amount',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'base_tax_amount'
                ),
                'type' => 'currency',
                'hidden' => true
            ),
            'base_currency_code' => array(
                'name' => 'base_currency_code',
                'label' => 'Base Currency Code',
                'definition' => 'varchar(3)',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'base_currency_code'
                ),
                'hidden' => true
            ),
            'currency_code' => array(
                'name' => 'currency_code',
                'label' => 'Currency Code',
                'definition' => 'varchar(3)',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'currency_code'
                ),
                'hidden' => true
            ),
            'order_qty' => array(
                'name' => 'order_qty',
                'label' => 'Order Quantity',
                'definition' => 'int(11)',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'order_qty'
                )
            )
        );

        return $cols;
    }

    public function getPopulateCollection() {

        $collection = Mage::getModel($this->_model)->getCollection()
            ->addFieldToFilter('order_id', array('gt' => 0))
            ->addFieldToFilter('order_currency_code', array('neq' => 'NULL'))
            ->addFieldToFilter('payment_method', array('neq' => 'NULL'));


        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'payment_method',
                'date(order_date) as order_date',
                'sum(grand_total) as grand_total',
                'sum(subtotal) as subtotal',
                'sum(tax_amount) as tax_amount',
                'sum(base_grand_total) as base_grand_total',
                'sum(base_subtotal) as base_subtotal',
                'sum(base_tax_amount) as base_tax_amount',
                'base_currency_code',
                'order_currency_code as currency_code',
                'count(order_id) as order_qty'
            ))
            ->group($this->_groupBy);

        Mage::log((string)$collection->getSelect(), null, 'BakerlooReports.log', true);

        return $collection;
    }

    public function getDataSourceConfiguration() {
        $ds = array();

        foreach ($this->_columns as $name => $def)
            $ds[$name] = $def['source'];

        return $ds;
    }

    public function populate(Varien_Db_Adapter_Interface $writer){

        $dataSources = unserialize($this->getDataSources());
        if(!isset($dataSources) or empty($dataSources))
            Mage::throwException("No data sources defined for this report.");

        $collection = $this->getPopulateCollection();

        $this->_dataSources = $dataSources;
        $table = $this->getTableName();

        foreach($collection as $row) {

            try {
                $data = array('id' => 'NULL');

                foreach($dataSources as $id => $ds) {
                    $data[$id] = isset($row[$ds['field']]) ? $row[$ds['field']] : 'NULL';
                }

                $select = $writer->select()
                    ->from($table)
                    ->where('order_date = ?', $row['order_date'])
                    ->where('currency_code = ?', $row['currency_code'])
                    ->where('payment_method = ?', $row['payment_method']);


                $repeat = $writer->fetchRow($select);

                if (!isset($repeat[$this->_identifier]))
                    $writer->insert($table, $data);

            }
            catch(Exception $e) {
                Mage::logException($e);
            }
        }

        $this->save();
    }

    public function loadData($args = array()) {

        try {
            $row = $args['row'];
            if (is_null($row[$this->_identifier])) //skip orders that failed to enter Magento
                return;

            if ($row[$this->_identifier] <= $this->_lastRecord)
                return;

            $table = $args['table'];
            $writer = $args['writer'];
            if (!$writer or !$table)
                return;

            $select = $writer->select()
                ->from($table)
                ->where('order_date = ?', $row['order_date'])
                ->where('currency_code = ?', $row['currency_code'])
                ->where('payment_method = ?', $row['payment_method']);

            $repeat = $writer->fetchRow($select);
            if (isset($repeat[$this->_identifier]) && isset($repeat['currency_code'])&& isset($repeat['order_date']))
                return;

            $data = array('id' => 'NULL');

            foreach($this->_dataSources as $id => $ds) {
                $data[$id] = isset($row[$ds['field']]) ? $row[$ds['field']] : 'NULL';
            }

            if (!empty($data))
                $writer->insert($table, $data);

            $this->_lastRecord = (int)$id;

        }
        catch(Exception $ex) {
            Mage::log("Insert failed on table {$table}: " . $ex->getMessage(), null, 'BakerlooReports.log', true);
        }
    }

    protected function _getDeleteDuplicateQuery() {
        return "DELETE r1 FROM {$this->getTableName()} r1, {$this->getTableName()} r2 WHERE r1.order_date=r2.order_date AND r1.payment_method=r2.payment_method AND r1.currency_code=r2.currency_code AND r1.id < r2.id";
    }
}