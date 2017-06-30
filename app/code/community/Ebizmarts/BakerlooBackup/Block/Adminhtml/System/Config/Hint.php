<?php

class Ebizmarts_BakerlooBackup_Block_Adminhtml_System_Config_Hint extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    protected $_template = 'bakerloo_backup/system/config/hint.phtml';

    public function getModuleVersion() {
        return (string) Mage::getConfig()->getNode('modules/Ebizmarts_BakerlooBackup/version');
    }

    private function getAdminEmail() {
        return Mage::getSingleton('admin/session')->getUser()->getEmail();
    }

    public function getHelpDeskUrl() {
        $url = "http://tickets.ebizmarts.com/formsupport/posmagento/index.php?";

        $url .= "magever=" . Mage::getVersion() . "&modulever=BakerlooBackup_" . $this->getModuleVersion() . "&email=" . $this->getAdminEmail();

        return $url;
    }

    public function getStorageName(){
        $destination = Mage::helper('bakerloo_backup')->getCurrentStorage();
        switch($destination){
            case 'dropbox':
                $name = 'in Dropbox';
                break;
            case 'drive':
                $name = 'in Google Drive';
                break;
            default:
                $name = 'locally.';
                break;
        }
        return $name;
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