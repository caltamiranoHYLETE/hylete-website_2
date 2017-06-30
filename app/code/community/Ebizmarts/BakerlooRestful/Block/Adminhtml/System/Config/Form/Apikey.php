<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_System_Config_Form_Apikey extends Mage_Adminhtml_Block_System_Config_Form_Field {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $element->setReadonly(true);

        $html = parent::render($element);

        $openInput = strpos($html, '<input id="bakerloorestful_general_api_key"');
        $closeInput = strpos($html, '/>', $openInput);

        $html = substr($html, 0, $closeInput) . ' autocomplete="off" ' . substr($html, $closeInput);
        return $html;
    }

}