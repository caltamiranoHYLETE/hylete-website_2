<?php

class Ebizmarts_BakerlooPayment_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getBakerlooPaymentMethods($store = null) {
        $methods = array();

        $_magentoMethods = Mage::helper('payment')->getPaymentMethodList(false, false, false, $store);

        //Bakerloo PayPal cctypes
        $paypalCcs = Mage::getModel('bakerloo_payment/source_payPalCcTypes')->toOption();

        //Usual cctypes
        $otherCcs = $this->_getDefaultCctypes();

        //All available cards
        $creditCards = array_merge($paypalCcs, $otherCcs);

        foreach ($_magentoMethods as $code => $label) {
            if( 1 === preg_match('/^bakerloo_/i', $code) ) {

                $method = Mage::helper('payment')->getMethodInstance($code);
                if(!$method) {
                    continue;
                }

                $active = (int)$method->getConfigData('active', $store);

                if($active === 1) {

                    $paymentMethodData = array(
                                                'code'               => $code,
                                                'label'              => $label,
                                                'open_cash_drawer'   => (int)$method->getConfigData('open_cash_drawer', $store),
                                                'requires_signature' => (int)$method->getConfigData('requires_signature', $store),
                                                'comments_step'      => (int)$method->getConfigData('show_payment_pre', $store),
                                                'sort_order'         => (int)$method->getConfigData('sort_order', $store),
                                                'cc_types'           => array()
                                        );

                    //Credit Card Types.
                    $ccTypes = $method->getConfigData('cctypes', $store);
                    if($ccTypes) {
                        $ccs = explode(',', $ccTypes);

                        foreach($ccs as $ccCode) {
                            if(!isset($creditCards[$ccCode])) {
                                continue;
                            }
                            $paymentMethodData['cc_types'] []= array('code' => $ccCode, 'label' => $creditCards[$ccCode]);
                        }

                    }

                    //Merchant Email for PayPal.
                    if($code == 'bakerloo_paypalhere') {
                        $paymentMethodData['merchant_email'] = $method->getConfigData('merchant_email', $store);
                        $paymentMethodData['check_in_enabled'] = (int)$method->getConfigData('enable_check_in', $store);
                        $paymentMethodData['use_sideloading']  = (int)$method->getConfigData('use_sideloading', $store);
                    }

                    if($code == 'bakerloo_manualcreditcard') {
                        $paymentMethodData['require_auth_code'] = (int)$method->getConfigData('require_auth_code', $store);
                    }

                    $mode = $method->getConfigData('mode', $store);
                    if(!empty($mode)) {
                        $paymentMethodData['mode'] = $mode;
                    }

                    $methods []= $paymentMethodData;
                }
            }
        }

        usort($methods, array($this, '_sortPaymentMethods'));

        return $methods;
    }

    /**
     * Sort payment methods by sort_order.
     *
     * @param array $a
     * @param array $b
     * @return int
     */
     protected function _sortPaymentMethods($a, $b) {
        if (is_array($a)) {
            return (int)$a['sort_order'] < (int)$b['sort_order'] ? -1 : ((int)$a['sort_order'] > (int)$b['sort_order'] ? 1 : 0);
        }
        return 0;
    }

    protected function _getDefaultCctypes() {
        $cards = Mage::getModel('adminhtml/system_config_source_payment_cctype')->toOptionArray();

        $ccs = array();

        for($i = 0; $i < count($cards); $i++) {
            $ccs[$cards[$i]['value']] = $cards[$i]['label'];
        }

        return $ccs;
    }

    public function processNvpData($stringData) {

        $data = array();

        $_data = urldecode($stringData);

        if(!empty($_data)) {
            // @codingStandardsIgnoreLine
            parse_str($_data, $data);
        }

        return $data;
    }

    public function getPayPalTxId($data) {
        $txid  = $data;
        $_data = explode("&", $data);

        if(is_array($_data) && !empty($_data)) {
            foreach($_data as $_d) {
                if(substr($_d, 0, 4) == 'TxId') {
                    $txid = substr($_d, (strpos($_d, '=')+1), strlen($_d));
                    break;
                }
            }
        }

        return $txid;
    }

    public function jsSetLocation($url) {
        return "<html><meta http-equiv=\"refresh\" content=\"2; url={$url}\"><body>Completing...</body></html>";
    }

    public function returnUrl($paymentMethodCode, $data = array()) {
        $protocol = "bakerloo://";

        return $protocol . $paymentMethodCode . '?' . http_build_query($data);
    }

    public function customerSignatureInfo() {
        return Mage::getModel('core/layout')
                    ->createBlock('bakerloo_payment/customersignature', 'pos_customer_signature')
                    ->setTemplate('bakerloo_restful/payment/info/customersignature.phtml');
    }

    public function getPayPalHereToken() {

    }


}