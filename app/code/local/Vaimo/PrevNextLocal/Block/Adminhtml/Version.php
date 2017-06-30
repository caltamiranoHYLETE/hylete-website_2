<?php
class Vaimo_PrevNextLocal_Block_Adminhtml_Version extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Show the module version number in admin module settings
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        return (string) Mage::helper('prevnextlocal/information')->getExtensionVersion();
    }

}