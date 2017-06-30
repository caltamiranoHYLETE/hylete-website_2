<?php

class Ebizmarts_BakerlooPayment_Model_Mysql4_Installment extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('bakerloo_payment/installment', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object){
        if(!$object->getId())
            $object->setCreatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));

        $object->setUpdatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));
        return parent::_beforeSave($object);
    }
}