<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Pos_Debug_View extends Mage_Adminhtml_Block_Template {


    public function logObject() {
        return Mage::registry('current_log');
    }

    public function getBackUrl() {
        return $this->getUrl('*/*/');
    }

}