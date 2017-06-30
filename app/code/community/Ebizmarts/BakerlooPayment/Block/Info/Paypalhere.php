<?php

class Ebizmarts_BakerlooPayment_Block_Info_Paypalhere extends Ebizmarts_BakerlooPayment_Block_Info_Default {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('bakerloo_restful/payment/info/paypalhere.phtml');
    }

    public function getTransactionDetails() {
    	$data = Mage::registry('bakerloo_paypalhere_transaction_details');

    	return $data;
    }

    /**
     * Return PayPal transaction id from data string, raw is for example:
     * Type=CreditCart&InvoiceId=XXXX-XXXX-XXXXX&Tip=0&Email=&TxId=XXXXXXXXX
     * 
     * @return string PayPal transaction ID.
     */
    public function getTxId() {
    	$transactionId = $this->helper('bakerloo_payment')->getPayPalTxId($this->getInfo()->getPoNumber());

    	return $transactionId;
    }
}