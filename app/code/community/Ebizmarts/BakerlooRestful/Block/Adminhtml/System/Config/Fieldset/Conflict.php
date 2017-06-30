<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_System_Config_Fieldset_Conflict extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    protected $_template = 'bakerloo_restful/system/config/fieldset/conflict.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        return $this->toHtml();
    }

    public function getConflictList() {
        return Mage::helper('bakerloo_restful/rewrite')->getConflictList();
    }

}