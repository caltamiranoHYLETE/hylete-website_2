<?php

class Ebizmarts_BakerlooPayment_Model_Mysql4_Installment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct(){
        $this->_init('bakerloo_payment/installment');
    }
}