<?php

class Ebizmarts_BakerlooBackup_Model_System_Config_Source_Account {

    public function toOptionArray(){
        return array(array('value' => '', 'label' => Mage::helper('bakerloo_backup')->__('--- Enter your ACTIVATION CODE here ---')));
    }
}