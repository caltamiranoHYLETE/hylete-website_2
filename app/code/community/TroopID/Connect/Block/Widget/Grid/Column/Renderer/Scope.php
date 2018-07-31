<?php

class TroopID_Connect_Block_Widget_Grid_Column_Renderer_Scope extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $value = $this->_getValue($row);
        $label = $this->_getLabel($value);

        return isset($label) ? $label : $value;
    }

    private function _getLabel($value) {
        return Mage::helper("troopid_connect")->getAffiliationByScope($value);
    }

}