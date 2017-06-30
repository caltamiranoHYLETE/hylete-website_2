<?php

class Ebizmarts_BakerlooPayment_Block_Info_Purchaseorder extends Ebizmarts_BakerlooPayment_Block_Info_Default {

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {
        return $this->getMethod()->getTitle() . "<br />" . $this->__("Number: %s", $this->getInfo()->getPoNumber())
                . parent::_toHtml();
    }

}