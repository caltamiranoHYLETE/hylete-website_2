<?php

/**
 * Class Globale_Base_Model_Adminhtml_System_Config_Validation_Logpath
 */
class Globale_Base_Model_Adminhtml_System_Config_Validation_Logpath extends Mage_Core_Model_Config_Data
{

    const WARNING = "Log path '%s' is not writable";

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {

        if (!$this->isWritable($this->getValue())) {
            Mage::getSingleton('core/session')->addWarning(sprintf(self::WARNING, $this->getValue()));
        }
        return parent::_afterSave();
    }

    /**
     * @param $log
     * @return bool
     */
    private function isWritable($log){

        $log = Mage::getBaseDir() . DIRECTORY_SEPARATOR . $log;
        return is_writable($log);

    }

}