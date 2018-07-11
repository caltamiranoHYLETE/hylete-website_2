<?php
class TroopID_Connect_Block_Adminhtml_System_Config_Custom extends Mage_Adminhtml_Block_System_Config_Form_Field {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $html = '<div class="' . $this->_getFrontendClass($element) . '">';
        $html .= '<div class="heading"><strong>' . $element->getLabel() . '</strong>';
        $html .= $this->_getCommentHtml($element);
        $html .= $this->_getContentHtml($element);
        $html .= '</div></div>';

        return $html;
    }

    protected function _getCommentHtml($element) {
        $html = "";

        if ($element->getComment())
            $html .= '<span class="comment">' . $element->getComment() . '</span>';

        return $html;
    }

    protected function _getContentHtml($element) {
        return "";
    }

    protected function _getFrontendClass($element) {
        $config = $element->getData("field_config")->asArray();
        $frontendClass = (string) $config["frontend_class"];

        return 'section-custom' . (empty($frontendClass) ? '' : (' ' . $frontendClass));
    }

}