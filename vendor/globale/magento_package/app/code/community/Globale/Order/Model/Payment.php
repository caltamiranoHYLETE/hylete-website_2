<?php

/**
 * Class Globale_Order_Model_Payment
 */
class Globale_Order_Model_Payment extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('globale_order/payment'); // this is location of the resource file.
    }

    /**
     * Save customer payment for order in DB, in globale_order_payments
     * @param $Request
     * @param $IncrementId
     */
    public function saveCustomerPayment($Request, $IncrementId) {

        $this->setOrderId($IncrementId);
        $this->setCustomerPaymentMethodName($Request->InternationalDetails->PaymentMethodName);
        $this->setCustomerPaymentMethodCode($Request->InternationalDetails->PaymentMethodCode);
        $this->save();

    }

    /**
     * Save payment for order in DB, in globale_order_payments
     * added in 1.1.0
     * @param $Request
     */
    public function savePayment($Request) {

        $this->setCardNumber($Request->PaymentDetails->CardNumber);
        $this->setCvvNumber($Request->PaymentDetails->CVVNumber);
        $this->setExpirationDate($Request->PaymentDetails->ExpirationDate);
        $this->setOwnerFirstName($Request->PaymentDetails->OwnerFirstName);
        $this->setOwnerLastName($Request->PaymentDetails->OwnerLastName);
        $this->setOwnerName($Request->PaymentDetails->OwnerName);
        $this->setPaymentMethodTypeCode($Request->PaymentDetails->PaymentMethodTypeCode);
        $this->setPaymentMethodName($Request->PaymentDetails->PaymentMethodName);
        $this->setPaymentMethodCode($Request->PaymentDetails->PaymentMethodCode);
        $this->save();

    }

}