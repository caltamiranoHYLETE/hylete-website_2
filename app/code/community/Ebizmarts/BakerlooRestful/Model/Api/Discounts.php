<?php

class Ebizmarts_BakerlooRestful_Model_Api_Discounts extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = "bakerloo_restful/discount";

    protected function _getIndexId() {
        return 'id';
    }

    protected function _getCollection() {

        $collection = parent::_getCollection();

        if($this->getStoreId()) {
            $collection->addStoreFilter($this->getStoreId());
        }

        return $collection;
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
                'id'          => (int)$_item->getId(),
                'description' => $_item->getDiscountDescription(),
                'max'         => (float)$_item->getDiscountMax(),
                'type'        => $_item->getDiscountType(),
            );

        }

        return $result;
    }

}