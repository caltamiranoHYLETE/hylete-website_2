<?php

class Globale_Base_Model_Adminhtml_System_Config_Source_Modules {

    /**
     * Create Option array of modules
     * @return array
     */
    public function toOptionArray(){

        $ModulesList = array();
        $Modules = Mage::getConfig()->getNode('modules')->children();

        foreach ($Modules as $ModuleName => $ModuleSettings) {
            if($ModuleSettings->is('active')){
                $ModulesList[] = array('value' => $ModuleName, 'label' => $ModuleName);
            }
        }

        return $ModulesList;
    }
}