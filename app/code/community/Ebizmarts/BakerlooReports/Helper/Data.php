<?php


class Ebizmarts_BakerlooReports_Helper_Data extends Mage_Core_Helper_Data {


    protected $_cols;

    public function getLabelByCode($code) {
        if(!$this->_cols) {
            $this->_cols = $this->getAllColumnsByName();
        }

        if(isset($this->_cols[$code]))
            return $this->_cols[$code]['label'];

        return '';
    }

    public function getAllColumnsByName() {
        $columns = $this->getMandatoryColumns();
        $columns = array_merge($columns, $this->getOptionalColumnsConfig());

        //$columns = array_multisort($columns, 'sort_order', 'ASC');

        return $columns;
    }

    public function getAllColumns() {

        $columns = $this->getAllColumnsByName();

        return array_values($columns);
    }

    public function getAllColumnsWithoutSource() {

        $columns = $this->getAllColumns();
        $result = array();

        foreach($columns as $col) {
            $c = array(
                'name' => isset($col['name']) ? $col['name'] : $col['value'],
                'label' => $col['label'],
                'definition' => $col['definition']
            );

            $result[$c['name']] = $c;
        }


        return $result;
    }

    public function getPosOrderColumns(){
        $options = $this->getOptionalColumns();
        return $options;
    }

    public function getDefaultReports(){
        return Mage::getConfig()->getNode('default/pos_reports')->asArray();
    }

    public function isDefaultReport(Ebizmarts_BakerlooReports_Model_Report $report){
        $default = $this->getDefaultReports();

        foreach($default as $_rep)
            if(strcmp($_rep['report_name'], $report->getReportName()) == 0)
                return true;

        return false;
    }

    public function getDefaultReportsConfig() {
        $result = array();

        $selected = Mage::getStoreConfig('bakerloorestful/reports_update/default_reports', Mage::app()->getStore());

        $selected = empty($selected) ? array() : explode(',', $selected);

        $default = $this->getDefaultReports();

        foreach($selected as $s)
            $result[$s] = $default[$s];

        return $result;
    }

    public function loadReport($report) {

        if(!($report instanceof Ebizmarts_BakerlooReports_Model_Report))
            $report = Mage::getModel('bakerloo_reports/report')->load($report);

        if($this->isDefaultReport($report))
            $report = $this->getDefaultReportModel($report);

        return $report;
    }

    public function getDefaultReportModel(Ebizmarts_BakerlooReports_Model_Report $report) {
        $default = $this->getDefaultReports();
        $dReport = null;

        foreach($default as $_rep) {
            if (strcmp($_rep['report_name'], $report->getReportName()) == 0) {
                $dReport = Mage::getModel($_rep['report_model']);
                $dReport->setData($report->getData());
                break;
            }
        }

        return $dReport;
    }

    public function getReportConfig($selection){

        $columns = $this->getConfigData();
        $selectedColumns = array();
        $columnConfig = array();

        foreach($columns['mandatory'] as $_colname => $_coldef){
            $selectedColumns[$_colname] = $_coldef['definition'];
            $columnConfig[$_colname] = $_coldef['source'];
        }

        $optional = $columns['optional'];
        foreach($selection as $_select){
            if(!isset($optional[$_select]))
                continue;

            $selectedColumns[$_select] = $optional[$_select]['definition'];
            $columnConfig[$_select] = $optional[$_select]['source'];
        }

        return array($selectedColumns, $columnConfig);

    }

    private function getConfigData(){
        $data = array();
        $data['mandatory'] = $this->getMandatoryColumns();
        $data['optional'] = $this->getOptionalColumnsConfig();

        return $data;

    }

    public function getMandatoryColumns(){
        $cols = array(
            'id' => array(
                'name' => 'id',
                'label' => 'Id',
                'definition' => array('int(11) NOT NULL auto_increment', 'PRIMARY KEY'),
                'source' => null,
                'hidden' => true
            ),
            'order_id' => array(
                'name' => 'order_id',
                'label' => 'Order Id',
                'definition' => 'int(11) NOT NULL',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'order_id'
                ),
                'hidden' => true
            ),
            'order_increment_id' => array(
                'name' => 'order_increment_id',
                'label' => 'Increment Id',
                'definition' => 'varchar(50)',
                'source' => array(
                    'model' => 'bakerloo_restful/order',
                    'field' => 'order_increment_id'
                ),
                'hidden' => false,
                'after' => 'customer_name',
                'renderer' => 'bakerloo_reports/adminhtml_widget_grid_column_renderer_orderNumber',
                'filter' => false,
                'sortable' => false
            ),
            'currency_code' => array(
                'name' => 'currency_code',
                'label' => 'Currency',
                'definition' => "varchar(3) default ''",
                'source' => array(
                    'model' => 'jsonPayload',
                    'field' => 'currency_code'
                ),
                'hidden' => true
            ),
            'grand_total' => array(
                'name' => 'grand_total',
                'label' => 'Order Total',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'jsonPayload',
                    'field' => 'total_amount'
                ),
                'hidden' => false,
                'after' => 'order_increment_id',
                'type' => 'currency'
            ),
//            'subtotal_before_discount' => array(
//                'name' => 'subtotal_before_discount',
//                'label' => 'Subtotal',
//                'definition' => "decimal(12,4) default '0.0000'",
//                'source' => array(
//                    'calculation' => array(
//                        'op' => 'sum',
//                        'path' => array('product', 'order_line', 'subtotal')
//                    )
//                )
//            ),
            'total_discount' => array(
                'name' => 'total_discount',
                'label' => 'Total Discount Amount',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'calculation' => array(
                        'op' => 'sum',
                        'path' => array('product', 'order_line', 'total_discount')
                    )
                ),
                'hidden' => false,
                'after' => 'grand_total',
                'type' => 'currency'
            ),
            'subtotal' => array(
                'name' => 'subtotal',
                'label' => 'Subtotal',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'calculation' => array(
                        'op' => 'sum',
                        'path' => array('product', 'order_line', 'subtotal_after_discount')
                    )
                ),
                'hidden' => false,
                'after' => 'total_discount',
                'type' => 'currency'
            ),
            'tax_amount' => array(
                'name' => 'tax_amount',
                'label' => 'Total Tax Amount',
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'jsonPayload',
                    'field' => 'tax_amount'
                ),
                'hidden' => false,
                'after' => 'subtotal_after_discount',
                'type' => 'currency'
            )
        );

        return $cols;
    }
    public function getOptionalColumns(){
        $cols = array();

        $optional = $this->getOptionalColumnsFromXml();
        foreach($optional as $optName => $optConf){
            $cols[] = array(
                'label' => $optConf['label'],
                'value' => $optConf['value']
            );
        }

        $cols = array_merge($cols, $this->getPaymentMethodColumns());
        return $cols;
    }
    public function getOptionalColumnsConfig(){
        $optional = $this->getOptionalColumnsFromXml();
        $optional = array_merge($optional, $this->getPaymentMethodColumnsConfig());

        return $optional;
    }
    public function getPaymentMethodColumns(){
        $cols = array();

        $paymentMethods = Mage::getConfig()->getNode('default/payment')->asArray();

        foreach($paymentMethods as $_name => $_conf){

            if( 1 === preg_match('/^bakerloo_/i', $_name) ) {

                if(!isset($_conf['title']))
                    continue;

                $cols[$_name] = array(
                    'label' => $_conf['title'],
                    'value' => $_name
                );

            }
        }

        return $cols;
    }
    public function getPaymentMethodColumnsConfig(){
        $cols = $this->getPaymentMethodColumns();

        $config = array();
        foreach($cols as $_colname => $_coldef){

            $label = str_replace('POS ', '', $_coldef['label']);
            $config[$_colname] = array(
                'name' => $_coldef['value'],
                'label' => $label,
                'definition' => "decimal(12,4) default '0.0000'",
                'source' => array(
                    'model' => 'jsonPayload',
                    'field' => array('path' => array('payment', 'amount')),
                    'condition' => array(
                        'path' => array('payment', 'method'),
                        'cond' => array('eq', $_colname)
                    )
                ),
                'hidden' => false,
                'type' => 'currency'
            );

        }

        return $config;
    }

    protected function getOptionalColumnsFromXml(){
        $cols = Mage::getConfig()->getNode('default/pos_columns')->asArray();
        $cols = $cols['optional'];

        $cols['payment_method'] = array(
            'value' => 'payment_method',
            'label' => 'Payment Method',
            'definition' => "varchar(255) default ''",
            'source' => array(
                'model' => 'jsonPayload',
                'field' => array('path' => array('payment', 'method'))
            ),
            'hidden' => false,
            'after' => 'total_tax',
            'renderer' => 'bakerloo_reports/adminhtml_widget_grid_column_renderer_orderPaymentMethod',
            'filter' => 0,
            'sortable' => 0
        );

        return $cols;
    }

}
