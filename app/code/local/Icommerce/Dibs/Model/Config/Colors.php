<?php
/**
 * Not used, not very useful.... but I want to keep the code, just in case...
 */
class Icommerce_Dibs_Model_Config_Colors
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label' => Mage::helper('dibs')->__('Default')),
            array('value' => 'sand', 'label' => Mage::helper('dibs')->__('Sand')),
            array('value' => 'grey', 'label' => Mage::helper('dibs')->__('Grey')),
            array('value' => 'blue', 'label' => Mage::helper('dibs')->__('Blue')),
        );
    }
}
