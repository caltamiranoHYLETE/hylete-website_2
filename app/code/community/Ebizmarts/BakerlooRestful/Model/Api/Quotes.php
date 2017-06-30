<?php

class Ebizmarts_BakerlooRestful_Model_Api_Quotes extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = 'bakerloo_restful/quote';
    protected $_iterator = false;

    /*public function checkDeletePermissions() {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/orders/delete'));
    }

    public function checkPostPermissions() {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/orders/create'));
    }*/


    /*
     * No Store filter yet.
     * public function _beforePaginateCollection($collection, $page, $since) {
        $storeId = $this->getStoreId();

        if($storeId)
            $this->_collection->addFieldToFilter('store_id', $storeId);

        return $this;
    }*/

    protected function _getIndexId() {
        return 'id';
    }

    public function _applyFilters($filters, $useOR = false) {
        parent::_applyFilters($filters, true);
    }

    public function _createDataObject($id = null, $data = null) {
        if(is_null($data))
            return array();

        return json_decode($data->getJsonPayload());
    }

    /**
     * Create order in Magento.
     *
     */
    public function post() {

        parent::post();

        if(!$this->getStoreId())
            Mage::throwException('Please provide a Store ID.');

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        if(empty($data->order_guid))
            Mage::throwException('GUID cannot be empty.');

        $save = Mage::getModel($this->_model)
            ->setStoreId($this->getStoreId())
            ->setCustomerEmail($data->customer->email)
            ->setCustomerFirstname($data->customer->firstname)
            ->setCustomerLastname($data->customer->lastname)
            ->setUser($data->user)
            ->setUserAuth($data->auth_user)
            ->setOrderGuid($data->order_guid)
            ->setJsonPayload($this->getRequest()->getRawBody())
            ->save();

        if($save)
            return 'OK';
        else
            Mage::throwException('Could not save data.');

    }

    /**
     * Cancel order
     */
    public function delete() {
        parent::delete();

        $guid = $this->_getIdentifier(true);

        $quote = Mage::getModel($this->_model)->load($guid, 'order_guid');

        if($quote->getId()) {
            $quote->delete();

            return 'OK';
        }
        else
            Mage::throwException("Quote does not exist.");

    }

}