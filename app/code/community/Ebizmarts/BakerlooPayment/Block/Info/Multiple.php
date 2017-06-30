<?php

class Ebizmarts_BakerlooPayment_Block_Info_Multiple extends Ebizmarts_BakerlooPayment_Block_Info_Default {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('bakerloo_restful/payment/info/multiple.phtml');
    }

    public function getAllPayments() {
        $paymentData = unserialize($this->getInfo()->getPosPaymentInfo());

        $payments = array();

        if($paymentData !== false) {

            foreach($paymentData as $_payment) {
                if(!isset($_payment->payMethod) or empty($_payment->payMethod))
                    continue;

                $paymentInfo = array(
                    'code'     => $_payment->payMethod->code,
                    'label'    => $_payment->payMethod->label,
                    'amount'   => $_payment->amount,
                    'comments' => isset($_payment->comments) ? $_payment->comments : '',
                );

                array_push($payments, $paymentInfo);
            }
        }

        return $payments;
    }

}