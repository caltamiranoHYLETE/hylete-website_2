<?php

class Ebizmarts_BakerlooReports_Model_Default_UserTotals extends Ebizmarts_BakerlooReports_Model_Report {

    protected $_reportName = 'Sales Totals by User';
    protected $_identifier = 'admin_user';
    protected $_groupBy = array(
        'admin_user',
        'order_currency_code',
        'date(order_date)'
    );

    public function __construct(){
        parent::__construct();

        $this->_columns = $this->_getColumns();
    }

    public function getColumnsConfig() {
        return $this->_getColumns();
    }

    public function getTableHeaders() {
        return array(
            'id',
            'admin_user',
            'order_date',
            'currency_code',
            'grand_total',
            'subtotal',
            'total_tax',
        );
    }

    public function getPopulateCollection() {

        $collection = Mage::getModel($this->_model)->getCollection()
            ->addFieldToFilter('order_id', array('gt' => 0))
            ->addFieldToFilter('order_currency_code', array('neq' => 'NULL'));

        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'admin_user',
                'date(order_date) as order_date',
                'order_currency_code',
                'base_currency_code',
                'sum(grand_total) as grand_total',
                'sum(subtotal) as subtotal',
                'sum(tax_amount) as tax_amount',
                'sum(base_grand_total) as base_grand_total',
                'sum(base_subtotal) as base_subtotal',
                'sum(base_tax_amount) as base_tax_amount',
                'count(order_id) as order_qty'
            ))
            ->group($this->_groupBy);

        return $collection;
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
                    ->where('admin_user = ?', $data['admin_user'])
                    ->where('order_date = ?', $data['order_date'])
                    ->where('currency_code = ?', $data['currency_code']);

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

    public function getDataSourceConfiguration(){
        $ds = array();

        foreach($this->_columns as $name => $def)
            $ds[$name] = $def['source'];

        return $ds;
    }

    protected function _getDeleteDuplicateQuery() {
        return "DELETE r1 FROM {$this->getTableName()} r1, {$this->getTableName()} r2 WHERE r1.admin_user=r2.admin_user AND r1.order_date=r2.order_date AND r1.currency_code=r2.currency_code AND r1.id < r2.id";
    }

    private function _getColumns() {
        return array(
            'id' => array(
                'name' => 'id',
                'label' => 'Id',
                'definition' => array('int(11) NOT NULL auto_increment', 'PRIMARY KEY'),
                'source' => null,
                'hidden' => true
            ),
            'admin_user' => array(
                'name' => 'admin_user',
                'label' => 'User',
                'definition' => "varchar(255) default ''",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'admin_user'
                ),
                'hidden' => false,
                'renderer' => 'bakerloo_reports/adminhtml_widget_grid_column_renderer_user'
            ),
            'order_date' => array(
                'name' => 'order_date',
                'label' => 'Date',
                'definition' => 'datetime',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'order_date'
                ),
                'hidden' => false,
                'type' => 'date'
            ),
            'currency_code' => array(
                'name' => 'currency_code',
                'label' => 'Currency',
                'definition' => "varchar(3) default ''",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'order_currency_code'
                ),
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
            'grand_total' => array(
                'name' => 'grand_total',
                'label' => 'Order Total',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'grand_total'
                ),
                'hidden' => false,
                'after' => 'admin_user',
                'type' => 'currency'
            ),
            'subtotal' => array(
                'name' => 'subtotal',
                'label' => 'Order Subtotal',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'subtotal'
                ),
                'hidden' => false,
                'after' => 'grand_total',
                'type' => 'currency'
            ),
            'tax_amount' => array(
                'name' => 'tax_amount',
                'label' => 'Tax',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'tax_amount'
                ),
                'hidden' => false,
                'after' => 'subtotal',
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
    }

}