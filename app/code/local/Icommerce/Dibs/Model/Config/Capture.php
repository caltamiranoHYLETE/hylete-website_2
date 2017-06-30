<?php
/**
 * Copyright DIBS | Secure Payment Services, (c) 2009.
 */
class Icommerce_Dibs_Model_Config_Capture
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('dibs')->__('Manual Capture')),
            array('value' => 2, 'label' => Mage::helper('dibs')->__('Capture on Invoice')),
            array('value' => 1, 'label' => Mage::helper('dibs')->__('Direct Capture')),
        );
    }
}
