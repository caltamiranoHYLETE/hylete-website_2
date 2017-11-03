<?php
class Cminds_Coupon_Model_Mysql4_Error_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('cminds_coupon/error');
    }
}