<?php


class Ebizmarts_BakerlooReports_Model_Default_Orders extends Ebizmarts_BakerlooReports_Model_Report {

    protected $_paymentMethods = array(
        'bakerloo_cash',
        'bakerloo_manualcreditcard',
        'bakerloo_purchaseorder',
        'bakerloo_storecredit'
    );
    protected $_reportName = 'Orders';

    public function __construct(){
        parent::__construct();

        $cols = Mage::helper('bakerloo_reports')->getMandatoryColumns();
        $this->_columns = array_merge($cols, $this->getOptionalColumns());

    }

    public function getDataSourceConfiguration(){
        $ds = array();

        foreach($this->_columns as $name => $def)
            $ds[$name] = $def['source'];

        return $ds;
    }

    private function getOptionalColumns(){

        $paymentMethods = Mage::helper('bakerloo_reports')->getPaymentMethodColumnsConfig();
        $selectedMethods = array();
        foreach($this->_paymentMethods as $methodName)
            $selectedMethods[$methodName] = $paymentMethods[$methodName];

        $cols = array(
            'order_date' => array(
                'name' => 'order_date',
                'label' => 'Order Date',
                'definition' => 'datetime',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'order_date'
                )
            ),
            'device_order_id' => array(
                'name' => 'device_order_id',
                'label' => 'Device Order ID',
                'definition' => 'varchar(255)',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'device_order_id'
                )
            ),
            'customer_id' => array(
                'name' => 'customer_id',
                'label' => 'Customer ID',
                'definition' => 'int(11)',
                'source' => array(
                    'model' => 'sales/order',
                    'field' => 'customer_id',
                    'searchby' => 'entity_id'
                )
            ),
            'customer_name' => array(
                'name' => 'customer_name',
                'label' => 'Customer Name',
                'definition' => 'varchar(255)',
                'source' => array(
                    'model' => 'sales/order',
                    'field' => 'customer_lastname',
                    'searchby' => 'entity_id'
                )
            ),
            'payment_method' => array(
                'name' => 'payment_method',
                'label' => 'Payment Method',
                'definition' => 'varchar(255)',
                'source' => array(
                    'model' => 'jsonPayload',
                    'field' => array(
                        'path' => array('payment', 'method')
                    )
                )
            )
        );

        $cols = array_merge($cols, $selectedMethods);

        $cols['total_to_gross'] = array(
            'name' => 'total_to_gross',
            'label' => 'Total to Gross',
            'definition' => "decimal(12,4) default '0.0000'",
            'source' => array(
                'calculation' => array(
                    array('op' => 'sum', 'path' => 'bakerloo_cash'),
                    array('op' => 'sum', 'path' => 'bakerloo_manualcreditcard'),
                    array('op' => 'sum', 'path' => 'bakerloo_purchaseorder'),
                    array('op' => 'sum', 'path' => 'bakerloo_storecredit'),
                    array('op' => 'subtract', 'path' => 'grand_total'),
                )
            )
        );

        return $cols;
    }

    public function getFileName() {
        $collection = $this->getReportCollection();
        if($this->_filters)
            $this->_applyFilters($collection, $this->_filters);

        $collection->setOrder('order_date', 'ASC');
        $from = $collection->getFirstItem();
        $to = $collection->getlastItem();

        $name = 'Orders';

        if($from) {
            $fromDate = explode(' ', $from->getOrderDate());
            $fromDate = $fromDate[0];
            $fromDate = str_replace('-', '', $fromDate);
            $name .= '_' . $fromDate;
        }
        if($to) {
            $toDate = explode(' ', $to->getOrderDate());
            $toDate = $toDate[0];
            $toDate = str_replace('-', '', $toDate);
            $name .= '_' . $toDate;
        }

        return $name;
    }

    public function getTableHeaders() {
        return array(
            'id',
            'order_date',
            'device_order_id',
            'customer_id',
            'customer_name',
            'order_increment_id',
            'currency_code',
            'grand_total',
            'total_discount',
            'subtotal',
            'tax_amount',
            'payment_method',
            'bakerloo_cash',
            'bakerloo_manualcreditcard',
            'bakerloo_purchaseorder',
            'bakerloo_storecredit',
            'total_to_gross'
        );
    }

    public function csvRow($args){
        $io = $args['io'];
        if(!$io) return;

        $row = $args['row'];
        $theaders = $this->getTableHeaders();
        $rowData = array();

        foreach($theaders as $thead)
            $rowData[$thead] = $row[$thead];

        $io->streamWriteCsv($rowData);
    }

}