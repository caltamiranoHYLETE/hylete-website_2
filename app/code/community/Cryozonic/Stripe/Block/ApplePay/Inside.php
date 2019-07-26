<?php

class Cryozonic_Stripe_Block_ApplePay_Inside extends Cryozonic_Stripe_Block_ApplePay_Abstract
{
    protected $_template = 'cryozonic/stripe/form/applepay/inside.phtml';

    public function shouldDisplay()
    {
        return parent::shouldDisplay() && parent::location() == 2;
    }
}
