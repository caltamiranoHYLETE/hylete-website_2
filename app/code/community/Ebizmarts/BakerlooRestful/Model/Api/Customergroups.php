<?php

class Ebizmarts_BakerlooRestful_Model_Api_Customergroups extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    public $defaultSort = "customer_group_code";
    protected $_model   = "customer/group";

    protected function _getIndexId() {
        return 'customer_group_id';
    }

    public function _createDataObject($id = null, $data = null) {
        $result = array();

        if(is_null($data)) {
            $_item = Mage::getModel($this->_model)->load($id);
        }
        else {
            $_item = $data;
        }

        if($_item->getCustomerGroupCode()) {
            $result = $_item->toArray();
        }

        return $result;
    }

}