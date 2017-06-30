<?php

class Ebizmarts_BakerlooPayment_Block_Info_Sagepay extends Ebizmarts_BakerlooPayment_Block_Info_Default {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('bakerloo_restful/payment/info/sagepay.phtml');
    }

    /**
     * Retrieve payment xml data and show on info.
     *
     * @return null|SimpleXMLElement
     */
    public function getPayrouterData() {
    	$paymentResponse = $this->getInfo()->getPosPaymentInfo();
    	$xmlPayment      = simplexml_load_string($paymentResponse);

    	$data = null;

    	if($xmlPayment !== false) {
    		$data = $xmlPayment;
    	}

    	return $data;
    }

}