<?php

/**
 * Class Globale_Order_Model_Resource_Products
 */
class Globale_Order_Model_Resource_Products extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('globale_order/products', 'product_id');
    }
}