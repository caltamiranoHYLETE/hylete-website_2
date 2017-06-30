<?php

/**
 * Authorize.net Card Holder Present (Swipe) integration
 */
class Ebizmarts_BakerlooPayment_Model_AnetChp extends Ebizmarts_BakerlooPayment_Model_Method_Abstract {

    protected $_code                    = "bakerloo_anet_chp";
    protected $_infoBlockType           = "bakerloo_payment/info_anetChp";
    protected $_canCapture              = true;

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

    /**
     * Capture payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount) {

        $info = $this->getInfoInstance()->getPosPaymentInfo();
        $jsonInfo = json_decode($info);

        if($jsonInfo !== false) {
            if( property_exists($jsonInfo, 'TransactionID') ) {
                $payment->setTransactionId($jsonInfo->TransactionID)->setIsTransactionClosed(0);
            }
        }

        return $this;
    }

}