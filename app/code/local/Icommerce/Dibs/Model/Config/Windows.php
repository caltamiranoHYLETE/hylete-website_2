<?php
/**
 * Copyright DIBS | Secure Payment Services, (c) 2009.
 */
class Icommerce_Dibs_Model_Config_Windows
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('dibs')->__('Original (old)')),
            array('value' => 1, 'label' => Mage::helper('dibs')->__('Dibs FlexWin')),
            array('value' => 2, 'label' => Mage::helper('dibs')->__('Redirect (no lightbox)')),
            array('value' => 3, 'label' => Mage::helper('dibs')->__('Dibs Payment Window')),
        );
    }
}
