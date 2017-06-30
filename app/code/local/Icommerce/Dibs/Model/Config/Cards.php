<?php

class Icommerce_Dibs_Model_Config_Cards
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('dibs')->__('All Cards')),
            array('value' => 'AMEX', 'label' => Mage::helper('dibs')->__('American Express')),
            array('value' => 'DIN', 'label' => Mage::helper('dibs')->__('Diners Club')),
            array('value' => 'DK', 'label' => Mage::helper('dibs')->__('Dankort')),
            array('value' => 'ELEC', 'label' => Mage::helper('dibs')->__('VISA Electron')),
            array('value' => 'FFK', 'label' => Mage::helper('dibs')->__('Forbrugsforeningen')),
            array('value' => 'JCB', 'label' => Mage::helper('dibs')->__('JCB')),
            array('value' => 'MC', 'label' => Mage::helper('dibs')->__('MasterCard')),
            array('value' => 'MTRO', 'label' => Mage::helper('dibs')->__('Maestro')),
            array('value' => 'VISA', 'label' => Mage::helper('dibs')->__('VISA')),
        );
    }
}
