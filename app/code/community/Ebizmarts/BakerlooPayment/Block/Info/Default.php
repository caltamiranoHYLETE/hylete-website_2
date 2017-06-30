<?php

class Ebizmarts_BakerlooPayment_Block_Info_Default extends Mage_Payment_Block_Info {

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {

        if(false === $this->getIsSecureMode()) {
            $this->setChild('pos_customer_signature', $this->helper('bakerloo_payment')->customerSignatureInfo());
        }

        return parent::_toHtml();
    }

    public function loadPosOrder($parentId) {
        return Mage::getModel('bakerloo_restful/order')->load($parentId, 'order_id');
    }

}