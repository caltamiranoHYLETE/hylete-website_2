<?php

class Ebizmarts_BakerlooBackup_Block_Adminhtml_System_Config_Driveoauth extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Set template to itself
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('bakerloo_backup/system/config/oauth.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();

        $label = $originalData['label'];

        $this->addData(array(
            'button_label' => $this->helper('bakerloo_backup')->__($label),
            'button_url' => $this->helper('bakerloo_backup/oauth')->getDriveOAuthUrl(),
            'html_id' => $element->getHtmlId(),
        ));
        return $this->_toHtml();
    }
}