<?php

/**
 * Class Globale_Base_Model_Resource_Fixedprices_Collection
 */
class Globale_FixedPrices_Model_Resource_Fixedprices_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected function _construct()
    {
        $this->_init('globale_fixedprices/fixedprices');
    }

	public function getAllIds()
	{
		$idsSelect = clone $this->getSelect();
		$idsSelect->reset(Zend_Db_Select::ORDER);
		$idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
		$idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
		$idsSelect->reset(Zend_Db_Select::COLUMNS);

		$idsSelect->columns('product_code', 'main_table');
		return $this->getConnection()->fetchCol($idsSelect);
	}
}
