<?php

class Ebizmarts_BakerlooPayment_Block_Info_Savedcc extends Mage_Payment_Block_Info_Ccsave {


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


}