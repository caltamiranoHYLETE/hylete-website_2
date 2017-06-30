<?php


class Ebizmarts_BakerlooShipping_Block_Onepage_Shipping_Method_Additional extends Mage_Checkout_Block_Onepage_Abstract {

    protected $_config     = array();
    protected $_stores     = array();
    protected $_storesJson = array();

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct() {
        parent::_construct();

        $this->_config = $carrierConfig = Mage::getStoreConfig('carriers/bakerloo_store_pickup');
    }

    public function getConfig($value = 'active', $default = null) {

        if(isset($this->_config[$value])) {
            return $this->_config[$value];
        }

        return $default;
    }

    public function getEnabledStores() {

        if(empty($this->_stores)) {

            $storesIds = $this->getConfig('stores', '');

            $storesIds = explode(',', $storesIds);

            $stores = array();
            $storesJson = array();

            if(!empty($storesIds)) {
                for($i=0; $i<count($storesIds);$i++) {
                    $storeId = $storesIds[$i];

                    $_store = Mage::getModel('core/store')->load($storeId);

                    $storeName = $_store->getName();

                    $nameConfig = $_store->getConfig('general/store_information/name');
                    if(!empty($nameConfig)) {
                        $storeName = $nameConfig;
                    }

                    $storeData = array(
                        'id'      => $_store->getId(),
                        'name'    => $storeName,
                        'value'   => $storeName . '_' . $_store->getId(),
                        'address' => nl2br($_store->getConfig('general/store_information/address')),
                    );

                    $stores []= new Varien_Object($storeData);

                    $storesJson []= $storeData;
                }

                usort($stores, array($this, '__sortStores'));

                $this->_stores = $stores;
                $this->_storesJson = json_encode($storesJson);
            }
        }

        return $this->_stores;
    }

    public function getStoresJson() {
        return $this->_storesJson;
    }

    public function getShouldRender() {
        $isEnabled      = (int)$this->getConfig('active');
        $storesSelected = $this->getConfig('stores', array());

        return ($isEnabled and $storesSelected);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {

        if(!$this->getShouldRender()) {
            return '';
        }

        return parent::_toHtml();
    }

    public function __sortStores($a, $b) {
        if($a->getName() == $b->getName()) {
            return 0;
        }

        return strcmp($a->getName(), $b->getName());
    }

}