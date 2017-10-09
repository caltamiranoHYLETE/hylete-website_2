<?php

/**
 * Class Globale_Order_Model_Resource_Discounts
 */
class Globale_Order_Model_Resource_Discounts extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('globale_order/discounts', 'discount_id');
    }
}