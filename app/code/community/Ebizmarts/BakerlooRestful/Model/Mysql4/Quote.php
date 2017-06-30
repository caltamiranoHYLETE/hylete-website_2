<?php

class Ebizmarts_BakerlooRestful_Model_Mysql4_Quote extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('bakerloo_restful/quote', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if ( !$object->getId() )
            $object->setCreatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));

        $object->setUpdatedAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));

        $object->setJsonPayloadEnc(Mage::helper('core')->encrypt($object->getJsonPayload()));

        $object->unsetData('json_payload');
        $object->unsetData('json_request_headers');
        $object->unsetData('customer_signature');

        return parent::_beforeSave($object);
    }

}