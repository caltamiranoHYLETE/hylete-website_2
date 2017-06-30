<?php

class Ebizmarts_BakerlooRestful_Model_Mysql4_CustomPrice extends Mage_Core_Model_Mysql4_Abstract{
    protected function _construct() {
        $this->_init('bakerloo_restful/customprice', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if ( !$object->getId() ) {
            $object->setCreatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));
        }

        $object->setUpdatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));

        return $this;
    }
}