<?php

class Ebizmarts_BakerlooRestful_Model_Api_Reports extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = 'bakerloo_reports/report';
    protected $_router = 'reports';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_reports';

    protected function _getIndexId() {
        return 'id';
    }

    public function _createDataObject($id = null, $data = null) {
        $report = array();

        if(is_null($data))
            $data = Mage::getModel($this->_model)->load($id);

        if($data->getId()) {
            $report = array(
                "report_id" => (int)$data->getId(),
                "report_name" => $data->getReportName(),
                "report_fields" => $data->getColumns(),
                "report_field_titles" => $data->getColumnTitles(),
                "created_at" => $data->getCreatedAt(),
                "updated_at" => $data->getUpdatedAt()
            );
        }

        return $this->returnDataObject($report);
    }

    protected function _getReportData($id = null, $data = null) {

        if(is_null($data))
            $data = Mage::getModel($this->_model)->load($id);

        if(!$data->getId())
            return array();

        $params = $this->parameters;

        if(isset($params[$this->_router]))
            unset($params[$this->_router]); //unset the identifier since we want to return the collection

        $params['report'] = $data;
        $report = Mage::getModel('bakerloo_restful/report', $params);

        return $report->get();
    }

    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get() {

        $this->checkGetPermissions();

        $identifier = $this->_getIdentifier();

        //if queried by id, return report data
        if($identifier) {

            if(is_numeric($identifier))
                return $this->_getReportData((int)$identifier);
            else
                throw new Exception('Incorrect request.');
        }
        //else return collection as usual
        else {

            $page = $this->_getQueryParameter('page');
            if(!$page) {
                $page = 1;
            }

            $filters     = $this->_getQueryParameter('filters');
            $resultArray = $this->_getAllItems($page, $filters);

            return $resultArray;

        }

    }

    protected function _getIdentifier($asString = false) {

        $identifier = isset($this->parameters[$this->_router]) ? $this->parameters[$this->_router] : null;

        if(!$asString and is_numeric($identifier))
            $identifier = (int)$identifier;

        return $identifier;
    }

    public function getAllAvailableFields(){
        $columns = Mage::helper('bakerloo_reports')->getAllColumnsWithoutSource();

        if(isset($columns['id']))
            unset($columns['id']);

        return $columns;
    }

    public function getDefault(){
        $by = $this->_getQueryParameter('by');

        $reports = Mage::helper('bakerloo_reports')->getDefaultReports();
        $model = Mage::getModel($this->_model);

        switch($by){
            case "user":
                $name = $reports['totals_by_user']['report_name'];
                $report = $model->load($name,'report_name');
                $resultData = $this->_createDataObject((int)$report->getId());
                break;
            case "day":
                $name = $reports['daily_totals']['report_name'];
                $report = $model->load($name,'report_name');
                $resultData = $this->_createDataObject((int)$report->getId());
                break;
            case "payMethod":
                $name = $reports['payment_type']['report_name'];
                $report = $model->load($name,'report_name');
                $resultData = $this->_createDataObject((int)$report->getId());
                break;
            default:

                $filter = 'id,in,';

                foreach($reports as $report){
                    $name = $report['report_name'];
                    $report = $model->load($name,'report_name');
                    $filter .= $report->getId() . ',';
                }

                $resultData = $this->_getAllItems(1, array($filter));
                break;
        }

        return $resultData;
    }
}