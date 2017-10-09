<?php

/**
 * Class Globale_Order_Model_Resource_Addresses
 */
class Globale_Order_Model_Resource_Addresses extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('globale_order/addresses', 'address_id');
    }
}