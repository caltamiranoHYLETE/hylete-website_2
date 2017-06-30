<?php

class Ebizmarts_BakerlooRestful_Model_Mysql4_Shift_Activity_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct() {
        $this->_init('bakerloo_restful/shift_activity');
    }

//    public function addMovements() {
//        $this->getSelect()->join(
//            array('movements' => $this->getTable('bakerloo_restful/shift_movement')),
//            'main_table.id = movements.activity_id',
//            array('amount', 'balance', 'refunds', 'currency_code')
//        )->group('main_table.id');
//
//    }
}