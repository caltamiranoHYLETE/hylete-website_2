<?php

class Ebizmarts_BakerlooPayment_Block_Form_Anet extends Ebizmarts_BakerlooPayment_Block_Form_Iframe {

    protected function _toHtml() {

    	$data = $this->getFormPostData();

        $method = Mage::getModel('bakerloo_payment/anet');

		$apiLoginId     = $method->getConfigData('api_login');
		$transactionKey = $method->getConfigData('api_transaction_key');
		$amount         = $data->getAmount();
		$sequence       = Mage::app()->getStore()->getId() . Mage::getModel('core/date')->gmtTimestamp();
		$timestamp      = Mage::getModel('core/date')->gmtTimestamp();
		$invoiceNumber  = uniqid(); //@TODO: Use POS number.

		$fpHash = $this->helper('bakerloo_payment/anet')->getFingerprint($apiLoginId, $transactionKey, $amount, $sequence, $timestamp, $data->getCurrency());

		$cancelUrl = Mage::getUrl('pos_payment/index/hostedCancel', array('_secure' => true, 'method' => $data->getMethod(), 'txid' => $invoiceNumber));
		$returnUrl = Mage::getUrl('pos_payment/index/hostedSuccess', array('_secure' => true, 'method' => $data->getMethod()));
		/*
		Notes on ReturnURL:
		If this field is submitted, the payment gateway will validate the URL value against
		the Relay Response URL configured in the Merchant Interface. If the URL submitted does
		not match the URL configured in the Merchant Interface, the transaction will be rejected. If
		no value is submitted in the HTML Form POST, the payment gateway will post the
		transaction results to the URL configured in the Merchant Interface.*/

		$fields = array (
			"x_login"           => $apiLoginId,
			"x_fp_hash"         => $fpHash,
			"x_type"            => $method->getConfigData('payment_mode'),
			"x_amount"          => $amount,
			"x_fp_timestamp"    => $timestamp,
			"x_fp_sequence"     => $sequence,
			"x_version"         => Ebizmarts_BakerlooPayment_Model_Anet::SIM_API_VERSION,
			"x_show_form"       => "PAYMENT_FORM",
			"x_currency_code"   => $data->getCurrency(),
			"x_invoice_num"     => substr($invoiceNumber, 0, 20),
			"x_cancel_url"      => $cancelUrl,
			"x_relay_response"  => "TRUE",
			"x_relay_url"       => $returnUrl,
			"x_relay_always"    => "TRUE",
			"x_description"     => "POS order.",
			//"x_test_request"  => ($method->getConfigData('api_mode') == "test" ? "TRUE" : "FALSE"),
			"x_method"          => "CC",
			"x_cancel_url_text" => $this->__('Cancel'),
		);

        $this->setPostFields($fields);

       $this->setPostUrl($method->getSMIPostUrl());
       $this->setCode($data->getMethod());

       return parent::_toHtml();
    }

}