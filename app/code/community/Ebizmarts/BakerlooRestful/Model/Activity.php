<?php

class Ebizmarts_BakerlooRestful_Model_Activity extends Mage_Core_Model_Abstract {

    const TYPE_OPEN_SHIFT = 'open_shift';
    const TYPE_CLOSE_SHIFT = 'close_shift';
    const TYPE_ADD_TO_TILL = 'add_to_till';
    const TYPE_TRANSACTION = 'transaction';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_SALE = 'sale';


    public function _construct() {
        $this->_init('bakerloo_restful/shift_activity');
    }





}
