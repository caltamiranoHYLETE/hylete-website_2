<?php

class Icommerce_Attributebinder_Model_Mysql4_Attributebinder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('attributebinder/attributebinder');
    }
}