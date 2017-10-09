<?php

/**
 * Class Globale_Order_Model_Resource_Shipping
 */
class Globale_Order_Model_Resource_Shipping extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('globale_order/shipping','id');
    }
}