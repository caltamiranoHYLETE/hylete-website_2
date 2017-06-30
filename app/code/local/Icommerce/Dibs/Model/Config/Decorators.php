<?php
/**
 * Not used, not very useful.... but I want to keep the code, just in case...
 */
class Icommerce_Dibs_Model_Config_Decorators
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label' => Mage::helper('dibs')->__('Default')),
            array('value' => 'basal', 'label' => Mage::helper('dibs')->__('Basal')),
            array('value' => 'rich', 'label' => Mage::helper('dibs')->__('Rich')),
            array('value' => 'own', 'label' => Mage::helper('dibs')->__('Own')),
        );
    }
}
