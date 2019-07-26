<?php

class Cryozonic_Stripe_Block_ApplePay_Abstract extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        $this->stripe = Mage::getModel('cryozonic_stripe/standard');
        $this->applePayEnabled = $this->stripe->isApplePayEnabled();
        $this->location = $this->stripe->store->getConfig('payment/cryozonic_stripe/apple_pay_location');
    }

    public function shouldDisplay()
    {
        return $this->applePayEnabled;
    }

    public function location()
    {
        return $this->location;
    }
}
