<?php
/**
 * Not used, not very useful.... but I want to keep the code, just in case...
 */
class Icommerce_Dibs_Model_Config_Languages
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'auto', 'label' => Mage::helper('dibs')->__('Automatic')),
            array('value' => 'da', 'label' => Mage::helper('dibs')->__('Danish')),
            array('value' => 'nl', 'label' => Mage::helper('dibs')->__('Dutch')),
            array('value' => 'en', 'label' => Mage::helper('dibs')->__('English')),
            array('value' => 'fo', 'label' => Mage::helper('dibs')->__('Faroese')),
            array('value' => 'fi', 'label' => Mage::helper('dibs')->__('Finnish')),
            array('value' => 'fr', 'label' => Mage::helper('dibs')->__('French')),
            array('value' => 'de', 'label' => Mage::helper('dibs')->__('German')),
            array('value' => 'it', 'label' => Mage::helper('dibs')->__('Italian')),
            array('value' => 'nb', 'label' => Mage::helper('dibs')->__('Norwegian')),
            array('value' => 'pl', 'label' => Mage::helper('dibs')->__('Polish')),
            array('value' => 'es', 'label' => Mage::helper('dibs')->__('Spanish')),
            array('value' => 'sv', 'label' => Mage::helper('dibs')->__('Swedish')),
        );
    }
}
