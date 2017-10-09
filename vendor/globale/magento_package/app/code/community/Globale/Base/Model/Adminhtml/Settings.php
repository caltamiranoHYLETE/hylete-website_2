<?php
class Globale_Base_Model_Adminhtml_Settings extends Mage_Adminhtml_Model_System_Config_Backend_File {

    const SETTINGS_PREFIX = 'globale_settings';

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return array
     */
    protected function _getAllowedExtensions() {
        return array('json');
    }

    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    public function _afterSave() {

        $Data = json_decode(file_get_contents($this->_getUploadDir() . DIRECTORY_SEPARATOR  . $this->getValue()));
        if(!empty($Data)){
            $this->importToDB($Data);
        }

    }

    /**
     * Push the settings one by one into Magento `core_config_data` table.
     * @param $Data
     */
    protected function importToDB($Data){

        foreach ($Data as $Group => $Settings){
            foreach ($Settings as $Field => $Value){
                $Path = self::SETTINGS_PREFIX . '/' . $Group . '/' . $Field;
                Mage::getConfig()->saveConfig($Path, $Value);
            }
        }
    }
}
