<?php

class Ebizmarts_BakerlooBackup_Model_Mysql4_Files_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct() {
        $this->_init('bakerloo_backup/files');
    }

}