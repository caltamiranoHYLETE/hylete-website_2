<?php

class Ebizmarts_BakerlooReports_Model_Mysql4_Report extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('bakerloo_reports/report', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object){
        $now = Mage::getModel('core/date')->gmtTimestamp();

        if(!$object->getId())
            $object->setCreatedAt($this->formatDate($now));

        $object->setUpdatedAt($this->formatDate($now));

        parent::_beforeSave($object);
    }
}