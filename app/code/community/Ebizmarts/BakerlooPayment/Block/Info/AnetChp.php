<?php

class Ebizmarts_BakerlooPayment_Block_Info_AnetChp extends Ebizmarts_BakerlooPayment_Block_Info_Default {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('bakerloo_restful/payment/info/anet_chp.phtml');
    }

    /**
     * Retrieve payment xml data and show on info.
     *
     * @return null|SimpleXMLElement
     */
    public function getPayrouterData() {
    	$paymentResponse = $this->getInfo()->getPosPaymentInfo();
    	$jsonPayment     = json_decode($paymentResponse);

    	$data = null;

    	if($jsonPayment !== false) {
    		$data = $jsonPayment;
    	}

    	return $data;
    }

}