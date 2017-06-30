<?php

class Ebizmarts_BakerlooPayment_Model_Sagepay extends Ebizmarts_BakerlooPayment_Model_Method_Abstract {

    protected $_code          = "bakerloo_sagepay";
    protected $_infoBlockType = "bakerloo_payment/info_sagepay";

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Method_Purchaseorder
     */
    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $this->getInfoInstance()->setPosPaymentInfo($data->getPoNumber());

        return $this;
    }

}