<?php

class Ebizmarts_BakerlooRestful_Model_Api_BestSellingProducts extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = "sales/report_bestsellers_collection";

    public $defaultSort          = "qty_ordered";
    public $defaultDir           = "DESC";
    public $pageSize             = 5;
    protected $_iterator         = false;

    protected function _getCollection(){
        $this->_collection = Mage::getResourceModel($this->_model)
            ->setModel('catalog/product');

        if($this->getStoreId())
            $this->_collection->addStoreFilter($this->getStoreId());

        return $this->_collection;
    }

    public function _createDataObject($id = null, $data = null){

        if($data instanceof Mage_Catalog_Model_Product)
            $id = $data->getProductId();

        $model = Mage::getModel('bakerloo_restful/api_products');
        $dto = $model->_createDataObject($id, $data);

        return $dto;
    }

    public function getCollectionSize(){
        if(isset($this->_collection))
            return $this->_collection->count();

        return 0;
    }
}