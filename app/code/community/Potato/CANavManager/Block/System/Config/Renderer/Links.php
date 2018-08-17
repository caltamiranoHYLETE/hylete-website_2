<?php

class Potato_CANavManager_Block_System_Config_Renderer_Links
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $linksBlock = Mage::app()->getLayout()->createBlock('po_canm/system_config_type_links');
        $linksBlock->setDisabled($element->getDisabled());
        $linksBlock->setLinksSetting($element->getValue());
        return $linksBlock->toHtml();
    }
}