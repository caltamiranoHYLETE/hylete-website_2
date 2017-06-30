<?php

class Icommerce_Attributebinder_Model_Mysql4_Attributebinder extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('attributebinder/attributebinder', 'id');
    }
}