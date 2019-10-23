<?php
/**
 * OnePica_AvaTax
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2015 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Class OnePica_AvaTax_Model_Action_BagItems
 */
class OnePica_AvaTax_Model_Action_BagItems extends OnePica_AvaTax_Model_Action_Abstract
{
    /**
     * Tries to ping AvaTax service with provided credentials
     *
     * @param int|null $storeId
     * @return \Varien_Data_Collection|\Varien_Object
     * @throws \Mage_Core_Model_Store_Exception
     */
    public function getAllParameterBagItems($storeId = null)
    {
        $storeId = Mage::app()->getStore($storeId)->getStoreId();
        $this->setStoreId($storeId);

        $items = $this->_getService()->getAllParameterBagItems($storeId);

        $collection = new Varien_Data_Collection();
        foreach ($items as $item) {
            $collection->addItem(new Varien_Object((array)$item));
        }

        return $collection;
    }

    protected function _convertToVarien($array)
    {
        $result = array();

        return $array;
    }

    protected function _sortItems($array, $field = 'Name')
    {

    }
}
