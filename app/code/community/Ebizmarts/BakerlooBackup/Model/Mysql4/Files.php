<?php

class Ebizmarts_BakerlooBackup_Model_Mysql4_Files extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('bakerloo_backup/files', 'id');
    }

}