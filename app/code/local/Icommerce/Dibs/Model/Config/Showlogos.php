<?php

class Icommerce_Dibs_Model_Config_Showlogos
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'DIBS', 'label' => Mage::helper('dibs')->__('DIBS trusted'),'image'=>'dibslogo','type'=>1),
            array('value' => 'VISA_SECURE', 'label' => Mage::helper('dibs')->__('Verified by VISA'),'image'=>'verified_visa','type'=>1),
            array('value' => 'MC_SECURE', 'label' => Mage::helper('dibs')->__('MasterCard SecureCode'),'image'=>'mc_securecode','type'=>1),
            array('value' => 'JCB_SECURE', 'label' => Mage::helper('dibs')->__('JCB J/Secure'),'image'=>'jcbsecure','type'=>1),
            array('value' => 'PCI', 'label' => Mage::helper('dibs')->__('PCI'),'image'=>'pci','type'=>1),
            array('value' => 'AMEX', 'label' => Mage::helper('dibs')->__('American Express'),'image'=>'amex'),
            array('value' => 'BAX', 'label' => Mage::helper('dibs')->__('BankAxess'),'image'=>'bax'),
            array('value' => 'DIN', 'label' => Mage::helper('dibs')->__('Diners Club'),'image'=>'diners'),
            array('value' => 'DK', 'label' => Mage::helper('dibs')->__('Dankort'),'image'=>'dankort'),
            array('value' => 'FFK', 'label' => Mage::helper('dibs')->__('Forbrugsforeningen Card'),'image'=>'forbrugforeningen'),
            array('value' => 'JCB', 'label' => Mage::helper('dibs')->__('JCB (Japan Credit Bureau)'),'image'=>'jcb'),
            array('value' => 'MC', 'label' => Mage::helper('dibs')->__('MasterCard'),'image'=>'mastercard'),
            array('value' => 'MTRO', 'label' => Mage::helper('dibs')->__('Maestro'),'image'=>'maestro'),
            array('value' => 'MOCA', 'label' => Mage::helper('dibs')->__('Mobilcash'),'image'=>'mobilcash'),
            array('value' => 'VISA', 'label' => Mage::helper('dibs')->__('Visa'),'image'=>'visa'),
            array('value' => 'ELEC', 'label' => Mage::helper('dibs')->__('Visa Electron'),'image'=>'visaelectron'),
            array('value' => 'AKTIA', 'label' => Mage::helper('dibs')->__('Aktia Web Payment'),'image'=>'aktia'),
            array('value' => 'DNB', 'label' => Mage::helper('dibs')->__('Danske Netbetaling (Danske Bank)'),'image'=>'danskenetbetaling'),
            array('value' => 'EDK', 'label' => Mage::helper('dibs')->__('eDankort'),'image'=>'edankort'),
            array('value' => 'ELV', 'label' => Mage::helper('dibs')->__('Bank Einzug (eOLV)'),'image'=>'bankeinzug'),
            array('value' => 'EW', 'label' => Mage::helper('dibs')->__('eWire'),'image'=>'ewire'),
            array('value' => 'FSB', 'label' => Mage::helper('dibs')->__('Swedbank Direktbetalning'),'image'=>'swedbank'),
            array('value' => 'GIT', 'label' => Mage::helper('dibs')->__('Getitcard'),'image'=>'getitcard'),
            array('value' => 'ING', 'label' => Mage::helper('dibs')->__('ING iDeal Payment'),'image'=>'ideal'),
            array('value' => 'SEB', 'label' => Mage::helper('dibs')->__('SEB Direktbetalning'),'image'=>'seb'),
            array('value' => 'SHB', 'label' => Mage::helper('dibs')->__('SHB Direktbetalning'),'image'=>'shb'),
            array('value' => 'SOLO', 'label' => Mage::helper('dibs')->__('Nordea'),'image'=>'nordea'),
            array('value' => 'VAL', 'label' => Mage::helper('dibs')->__('Valus'),'image'=>'valus'),
        );
    }
}
