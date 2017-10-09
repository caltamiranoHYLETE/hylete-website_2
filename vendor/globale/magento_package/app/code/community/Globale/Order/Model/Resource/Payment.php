<?php

/**
 * Class Globale_Order_Model_Resource_Payment
 */
class Globale_Order_Model_Resource_Payment extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('globale_order/payment','id');
    }
}