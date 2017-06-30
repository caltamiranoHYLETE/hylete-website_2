<?php


class Ebizmarts_BakerlooEmail_Block_Adminhtml_System_Config_Processall extends Mage_Adminhtml_Block_System_Config_Form_Field {

    public function __construct(){

        $this->_controller = 'adminhtml_pos_unsent';
        parent::__construct();
    }

    /**
     * Set template to itself
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('bakerloo_email/system/config/processemails.phtml');
        }

        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $originalData = $element->getOriginalData();

        $label = $originalData['button_label'];

        $this->addData(array(
            'button_label' => $this->helper('bakerloo_email')->__('Send all queued messages'),
            'button_url'   => "setLocation('{$this->getProcessUrl()}')",
            'html_id' => $element->getHtmlId(),
        ));
        return $this->_toHtml();
    }

    public function getProcessUrl() {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/pos_unsent/processall', array('_secure' => true));
    }
}