<?php
class Globale_Base_Model_Adminhtml_System_Config_Source_Internationalpayment {

    /**
     * First "Empty" option in option array
     * @var array
     */
    private static $EmptyOption = array(
        "value" => "",
        "label" => ""
    );

    /**
     * Filter options for the system.xml International Payment order statuses
     * @return array
     */
    public function toOptionArray()
    {
        // get all order statuses filtered by not include "Complete" and "Close" status
        $OrderStatuses = Mage::getResourceModel('sales/order_status_collection')
            ->addFieldToFilter('status', array('nlike' => 'complete%'))
            ->addFieldToFilter('status', array('nlike' => 'close%'))
            ->toOptionHash();
        $OrderStatuses = array_merge(array(self::$EmptyOption), $OrderStatuses);
        return $OrderStatuses;
    }
}