<?php
class Cminds_Coupon_Model_Mysql4_log extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('cminds_coupon/error_log', 'id');
    }
}