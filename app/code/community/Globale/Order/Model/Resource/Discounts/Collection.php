<?php

/**
 * Class Globale_Order_Model_Resource_Discounts_Collection
 */
class Globale_Order_Model_Resource_Discounts_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected function _construct()
    {
        $this->_init('globale_order/discounts');
    }
}
