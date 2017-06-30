<?php


class Ebizmarts_BakerlooRestful_Model_Api_LastSoldProducts extends Ebizmarts_BakerlooRestful_Model_Api_Api {
    protected $_model = "reports/order_collection";
    protected $_iterator = false;

    public $defaultSort          = "entity_id";
    public $defaultDir           = "DESC";
    public $pageSize             = 5;

    protected function _getCollection(){
        $latestOrders = Mage::getResourceModel($this->_model)
            ->orderByCreatedAt()
            ->setPageSize($this->pageSize * 2)
            ->setCurPage(1);

        $productIds = array();

        foreach($latestOrders as $order){

            foreach($order->getAllVisibleItems() as $item) {

                $product = $item->getProduct();

                if(!in_array($product->getId(), $productIds))
                    $productIds[] = $product->getId();
            }

            if(count($productIds) >= $this->pageSize)
                break;
        }

        $this->_collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addFieldToFilter('entity_id', array('in' => $productIds));

        return $this->_collection;
    }

    public function _createDataObject($id = null, $data = null){
        if(is_null($id))
            $id = $data->getProductId();

        $model = Mage::getModel('bakerloo_restful/api_products');
        $dto = $model->_createDataObject($id, $data);

        return $dto;
    }
}