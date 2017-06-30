<?php

class Ebizmarts_BakerlooRestful_Model_Api_Backup extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model   = "bakerloo_backup/files";
    public $defaultSort = "upload_date";

    public function getModelName() {
        return __CLASS__;
    }

    /**
     * @param Zend_Controller_Response_Abstract $response
     * @return array
     * @throws Mage_Core_Exception
     *
     * Get all available backups
     */
    public function getBackup(Zend_Controller_Response_Abstract $response){

        if(!$this->getStoreId())
            Mage::throwException('Please provide a Store ID.');
        $this->parameters['store_id'] = $this->getStoreId();

        $deviceId = $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getDeviceIdHeader());
        if( !$deviceId )
            Mage::throwException('Please specify a Device ID.');
        $this->parameters['device_id'] = $deviceId;

        $deviceName = $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getDeviceNameHeader());
        if( !$deviceName )
            Mage::throwException('Please specify a Device Name.');
        $this->parameters['device_name'] = $deviceName;

        $id = $this->_getIdentifier(true);
        $params = $this->parameters;

        if((array_key_exists('backup', $params) === true) and !is_null($params['backup']) and !empty($params['backup']))
            $params[$params['backup']] = 0;
        unset($params['backup']);

        $handler = Mage::getModel('bakerloo_backup/handler_factory')->getHandler($params);

        if($id) {

            $file = $handler->getById($id, $this->getStoreId());
            $this->_returnBinary($file, $response, 'application/zip');

        }
        else {

            $page = $this->_getQueryParameter('page');
            if(!$page)
                $page = 1;

            $filters = $this->_getQueryParameter('filters');
            $resultArray = $this->_getAllItems($page, $filters);

            $this->_returnList($resultArray, $response);
        }

    }

    private function _returnList($list, Zend_Controller_Response_Abstract $response){
        $response
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'no-cache', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->clearBody();
        $response->setBody(Mage::helper("bakerloo_restful")->encodeResponse($list));
        $response->sendHeaders();
    }

    private function _returnBinary($fileContents, Zend_Controller_Response_Abstract $response, $type){
        $response
            ->setHttpResponseCode(200)
            ->setHeader('Content-Type', $type, true)
//            ->setHeader('Content-Disposition', 'attachment; filename=' . $name, true)
            ->setHeader('Pragma', 'no-cache', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->clearBody();

        $response->setBody($fileContents);
        $response->sendHeaders();
    }

    /**
     * Store backup file.
     *
     */
    public function post(){

        parent::post();

        if (!$this->getStoreId())
            Mage::throwException('Please provide a Store ID.');
        $this->parameters['store_id'] = $this->getStoreId();

        $deviceId = $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getDeviceIdHeader());
        if (!$deviceId)
            Mage::throwException('Please specify a Device ID.');
        $this->parameters['device_id'] = $deviceId;

        $deviceName = $this->_getRequestHeader(Mage::helper('bakerloo_restful')->getDeviceNameHeader());
        if (!$deviceName)
            Mage::throwException('Please specify a Device Name.');
        $this->parameters['device_name'] = $deviceName;

        $handler = Mage::getModel('bakerloo_backup/handler_factory')->getHandler($this->parameters);
        return $handler->post();
    }

    protected function _getIndexId() {
        return 'id';
    }
}