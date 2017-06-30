<?php

class Ebizmarts_BakerlooBackup_Model_System_Config_Source_Storage {
    public function toOptionArray(){
        $helper = Mage::helper('bakerloo_backup');
        return array(
            array('value' => 0, 'label' => $helper->__('Magento')),
            array('value' => 1, 'label' => $helper->__('Dropbox')),
            array('value' => 2, 'label' => $helper->__('Drive')),
        );
    }

    public function toArray(){
        $helper = Mage::helper('bakerloo_backup');
        return array(
            0 => $helper->__('Magento'),
            1 => $helper->__('Dropbox'),
            2 => $helper->__('Drive'),
        );
    }
}