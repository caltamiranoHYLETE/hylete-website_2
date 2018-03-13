<?php

/**
 * Class Mediotype_OffersTab_Model_Resource_Offer
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_Model_Resource_Offer extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('mediotype_offerstab/offer', 'offer_id');
	}

    /**
     * Setting the created_at and updated_at attributes
     * @param Mage_Core_Model_Abstract $object
     * @return array
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        $currentTime = Varien_Date::now();
        
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }

        $object->setUpdatedAt($currentTime);

        if (is_array($object->getCustomerGroupIds())) {
            $object->setCustomerGroupIds(implode(',', $object->getCustomerGroupIds()));
        }

        $data = parent::_prepareDataForSave($object);

        return $data;
    }
}

