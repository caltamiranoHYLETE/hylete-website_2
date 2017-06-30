<?php

class Ebizmarts_BakerlooRestful_Model_Report extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_report;
    protected $_router = 'reports';
    public $defaultSort = "id";
    protected $_model = 'bakerloo_reports/report';
    protected $_iterator = false;

    public function __construct($params) {
        $this->_report = isset($params['report']) ? $params['report'] : null;
        unset($params['report']);

        parent::__construct($params);
    }

    protected function _getCollection() {
        return $this->_report->getReportCollection();
    }

    protected function _getIndexId() {
        return 'id';
    }

    protected function _reassembleRequestUrl() {
        $params = array();
        $params[$this->_router] = $this->_report->getId();

        $params = array_merge($params, $this->parameters);
        $this->parameters = $params;

        return parent::_reassembleRequestUrl();
    }

    /**
     * Applying array of filters to collection
     *
     * @param array $filters
     * @param bool $useOR
     */
    public function _applyFilters($filters, $useOR = false) {

        //parse filters
        $attrFieldValue = array();

        foreach($filters as $filter) {
            list($attributeCode, $condition, $value) = $this->explodeFilter($filter);

            if($attributeCode == 'order_date')
                $this->_collection->addFieldToFilter($attributeCode, array($condition => $value));
            else {

                if (!isset($attrFieldValue[$attributeCode]))
                    $attrFieldValue[$attributeCode] = array(array($condition => $value));
                else
                     array_push($attrFieldValue[$attributeCode],array($condition => $value));

            }
        }

        foreach($attrFieldValue as $attribute =>$val) {
            $this->_collection->addFieldToFilter($attribute, $attrFieldValue[$attribute]);
        }

        Mage::log((string)$this->_collection->getSelect(), null, 'BakerlooReports.log', true);

    }

    protected function _getIdentifier($asString = false) {

        $identifier = isset($this->parameters[$this->_router]) ? $this->parameters[$this->_router] : null;

        if(!$asString and is_numeric($identifier))
            $identifier = (int)$identifier;

        return $identifier;
    }
}