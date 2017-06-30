<?php

class Ebizmarts_BakerlooEmail_Model_Queue extends Mage_Core_Model_Abstract {

    public function _construct() {
        $this->_init('bakerloo_email/queue');
    }

}