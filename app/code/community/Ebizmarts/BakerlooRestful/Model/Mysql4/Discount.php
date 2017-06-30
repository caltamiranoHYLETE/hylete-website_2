<?php

class Ebizmarts_BakerlooRestful_Model_Mysql4_Discount extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('bakerloo_restful/discount', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if ( !$object->getId() ) {
            $object->setCreatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));
        }

        $object->setUpdatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));

        return $this;
    }

    /**
     * Assign discount to store views
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        $condition = $this->_getWriteAdapter()->quoteInto('discount_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('bakerloo_restful/discountstore'), $condition);

        foreach ((array)$object->getData('stores') as $store) {
            $storeArray                 = array();
            $storeArray['discount_id']  = $object->getId();
            $storeArray['store_id']     = $store;
            $this->_getWriteAdapter()->insert($this->getTable('bakerloo_restful/discountstore'), $storeArray);
        }

        return parent::_afterSave($object);
    }

    /**
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('bakerloo_restful/discountstore'))
            ->where('discount_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
    }
}