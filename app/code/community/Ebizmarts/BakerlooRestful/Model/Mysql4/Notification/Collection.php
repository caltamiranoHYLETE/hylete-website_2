<?php

class Ebizmarts_BakerlooRestful_Model_Mysql4_Notification_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected $_previewFlag;

    protected function _construct() {
        $this->_init('bakerloo_restful/notification');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    public function setFirstStoreFlag($flag = false) {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @return Ebizmarts_BakerlooRestful_Model_Mysql4_Discount_Collection
     */
    public function addStoreFilter($store, $withAdmin = true) {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }
        $this->addFilter('store', array('in' => ($withAdmin ? array(0, $store) : $store)), 'public');
        return $this;
    }

    protected function _afterLoad() {
        if ($this->_previewFlag) {
            $items = $this->getColumnValues('id');
            if (count($items)) {
                $select = $this->getConnection()->select()
                    ->from($this->getTable('bakerloo_restful/notificationstore'))
                    ->where($this->getTable('bakerloo_restful/notificationstore').'.notification_id IN (?)', $items);
                if ($result = $this->getConnection()->fetchPairs($select)) {
                    foreach ($this as $item) {
                        if (!isset($result[$item->getData('id')])) {
                            continue;
                        }
                        if ($result[$item->getData('id')] == 0) {
                            $stores = Mage::app()->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        }
                        else {
                            $storeId = $result[$item->getData('id')];
                            $storeCode = Mage::app()->getStore($storeId)->getCode();
                        }
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);
                    }
                }
            }
        }

        parent::_afterLoad();
    }

    /**
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore() {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('bakerloo_restful/notificationstore')),
                'main_table.id = store_table.notification_id',
                array()
            )->group('main_table.id');
        }
        return parent::_renderFiltersBefore();
    }

}