<?php
class Vaimo_PrevNextLocal_Block_Adminhtml_Code extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Show link in admin module settings to the bitbucket code repository
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        return (string) Mage::helper('prevnextlocal/information')->getExtensionCode();
    }

}