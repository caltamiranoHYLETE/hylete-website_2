<?php

class Cryozonic_Stripe_Block_Form_StripeJs extends Mage_Core_Block_Template
{
    public $billingInfo;
    public $paymentIntent;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cryozonic/stripe/form/stripejs.phtml');
        $this->stripe = Mage::getModel('cryozonic_stripe/standard');
        $this->billingInfo = Mage::helper('cryozonic_stripe')->getSanitizedBillingInfo();
    }

    public function getPublishableKey()
    {
        $mode = $this->stripe->store->getConfig('payment/cryozonic_stripe/stripe_mode');
        $path = "payment/cryozonic_stripe/stripe_{$mode}_pk";
        return trim($this->stripe->store->getConfig($path));
    }

    public function hasBillingAddress()
    {
        return isset($this->billingInfo) && !empty($this->billingInfo);
    }

    public function getIsAdmin()
    {
        if (Mage::app()->getStore()->isAdmin())
            return 'true';

        return 'false';
    }
}
