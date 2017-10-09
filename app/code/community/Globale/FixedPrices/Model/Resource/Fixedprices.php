<?php

/**
 * Class Globale_Base_Model_Resource_Fixedprices
 */
class Globale_FixedPrices_Model_Resource_Fixedprices extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('globale_fixedprices/fixedprices', 'id');
    }
}