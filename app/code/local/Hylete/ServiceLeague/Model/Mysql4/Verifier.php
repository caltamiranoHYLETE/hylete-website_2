<?php
class Hylete_ServiceLeague_Model_Mysql4_Verifier extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("serviceleague/verifier", "id");
    }
}