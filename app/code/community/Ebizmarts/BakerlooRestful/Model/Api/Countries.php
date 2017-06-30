<?php

class Ebizmarts_BakerlooRestful_Model_Api_Countries extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model   = "directory/country";
    public $defaultSort = "country_id";

    protected function _getIndexId() {
        return 'country_id';
    }

    public function _createDataObject($id = null, $data = null) {
        $result = null;

        if(is_null($data)) {
            $_item = Mage::getModel($this->_model)->load($id);
        }
        else {
            $_item = $data;
        }

        if($_item->getId()) {
            $result = array(
                'id'   => (string)$_item->getCountryId(),
                'name' => (string)$_item->getName(),
            );
        }

        return $result;
    }

    protected function _getCollection() {
        Mage::app()->setCurrentStore($this->getStoreId());

        return Mage::getModel($this->_model)->getResourceCollection()->loadByStore();
    }

}