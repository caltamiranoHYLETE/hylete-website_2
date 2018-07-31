<?php
/**
 * Used in creating options for Enabled|Disabled config value selection
 *
 */
class TroopID_Connect_Model_System_Config_Source_Enabled
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array("value" => 1, "label" => Mage::helper("troopid_connect")->__("Enabled")),
            array("value" => 0, "label" => Mage::helper("troopid_connect")->__("Disabled")),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper("troopid_connect")->__("No"),
            1 => Mage::helper("troopid_connect")->__("Yes"),
        );
    }

}
