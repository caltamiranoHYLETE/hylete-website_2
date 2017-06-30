<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_System_Config_Fieldset_Hint extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    protected $_template = 'bakerloo_restful/system/config/fieldset/hint.phtml';

    private function getModuleCodeName() {
        $current = $this->getRequest()->getParam('section');

        switch ($current) {
            case "bakerloo_payment":
                $module = "BakerlooPayment";
                break;
            case "bakerloo_shipping":
                $module = "BakerlooShipping";
                break;
            default:
                $module = "BakerlooRestful";
                break;
        }

        return $module;
    }

    public function getModuleVersion() {
        return (string) Mage::getConfig()->getNode('modules/Ebizmarts_' . $this->getModuleCodeName() . '/version');
    }

    private function getAdminEmail() {
        return Mage::getSingleton('admin/session')->getUser()->getEmail();
    }

    public function getHelpDeskUrl() {
        $url = "http://tickets.ebizmarts.com/formsupport/posmagento/index.php?";

        $url .= "magever=" . Mage::getVersion() . "&modulever=" . $this->getModuleCodeName() . "_" . $this->getModuleVersion() . "&email=" . $this->getAdminEmail();

        return $url;
    }

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        return $this->toHtml();
    }

}