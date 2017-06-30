<?php

class Ebizmarts_BakerlooPayment_Model_Multiple extends Ebizmarts_BakerlooPayment_Model_Method_Abstract {

    protected $_code  = "bakerloo_multiple";

    protected $_infoBlockType = 'bakerloo_payment/info_multiple';

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Method_Purchaseorder
     */
    public function assignData($data) {

        $allPayments = $data->getData('addedPayments');

        $this->getInfoInstance()->setPosPaymentInfo( serialize($allPayments) );
        return $this;
    }

}