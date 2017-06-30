<?php

class Ebizmarts_BakerlooRestful_Model_Api_Notifications extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model   = "bakerloo_restful/notification";
    public $defaultSort = "date_added";

    protected function _getCollection() {

        $collection = parent::_getCollection();

        if($this->getStoreId()) {
            $collection->addStoreFilter($this->getStoreId());
        }

        $collection->addFieldToFilter('is_remove', array('neq' => 1));

        return $collection;
    }

    public function _createDataObject($id = null, $data = null) {

        $result = parent::_createDataObject($id, $data);

        if(isset($result['id'])) {
            $result['id'] = (int)$result['id'];
        }

        if(isset($result['is_read'])) {
            $result['is_read'] = (int)$result['is_read'];
        }

        if(isset($result['is_remove'])) {
            unset($result['is_remove']);
        }

        if(isset($result['severity'])) {
            $result['severity'] = Mage::helper('bakerloo_restful')->getSeverityOption($result['severity']);
        }

        return $result;

    }

    public function post() {
        parent::post();

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        if(!isset($data->title) or !Zend_Validate::is($data->title, 'NotEmpty')) {
            Mage::throwException('Invalid value for "title".');
        }

        if(!isset($data->description) or !Zend_Validate::is($data->description, 'NotEmpty')) {
            Mage::throwException('Invalid value for "description".');
        }

        if(!isset($data->severity) or !Zend_Validate::is($data->severity, 'Zend_Validate_Int')) {
            Mage::throwException('Invalid value for "severity".');
        }
        else {
            $severityLabel = Mage::helper('bakerloo_restful')->getSeverityOption($data->severity);
            if(is_null($severityLabel)) {
                Mage::throwException('"severity" value is not valid.');
            }
        }

        $data->stores = array($this->getStoreId());

        $notification = Mage::getModel('bakerloo_restful/notification')->addData(((array)$data));

        $notification->save();

        return $this->_createDataObject($notification->getId());
    }

}