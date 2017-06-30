<?php

class Ebizmarts_BakerlooRestful_Model_Mysql4_Debug extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('bakerloo_restful/debug', 'debug_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object) {

        if ( !$object->getId() ) {
            $object->setDebugAt($this->formatDate(Mage::getModel('core/date')->gmtTimestamp()));
        }
        else {
            $now = microtime(true);
            $requestTime = (float)Mage::registry('brest_request_time');

            $callTime = $now - $requestTime;

            $object->setCallTime($callTime);
        }

        return $this;
    }

    public function truncateTable() {
        $this->_getWriteAdapter()->truncateTable($this->getMainTable());
        return $this;
    }

}