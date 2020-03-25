<?php
class Hylete_CustomerGroupLog_Model_Mysql4_Customergrouplog extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("customergrouplog/customergrouplog", "customer_group_log_id");
    }
}