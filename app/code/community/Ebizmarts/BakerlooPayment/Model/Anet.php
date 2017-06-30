<?php

/**
 * Authorize.net SIM (Server iFrame integration)
 */
class Ebizmarts_BakerlooPayment_Model_Anet extends Ebizmarts_BakerlooPayment_Model_Method_Iframe {

    protected $_code          = "bakerloo_anet";
    const SIM_API_VERSION     = 3.1;

    //protected $_infoBlockType = "bakerloo_payment/info_sagepay";

    public function getSMIPostUrl() {
        $mode = $this->getConfigData('api_mode');

        if($mode == "test") {
            $url = "https://test.authorize.net/gateway/transact.dll";
        }
        else {
            $url = "https://secure.authorize.net/gateway/transact.dll";
        }

        return $url;
    }

    public function getReturnData($post = array()) {
        $returnData = array(
            'responseCode'   => $post['x_response_code'],
            'responseStatus' => Mage::helper("bakerloo_payment/anet")->responseStatusForCode($post['x_response_code']),
            'responseText'   => $post['x_response_reason_text'],
            'authCode'       => $post['x_auth_code'],
            'txId'           => $post['x_trans_id'],
            'txType'         => $post['x_type'],
            'cardType'       => $post['x_card_type'],
            'cardNumber'     => $post['x_account_number'],
        );
        return $returnData;
    }



}