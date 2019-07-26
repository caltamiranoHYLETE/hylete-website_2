<?php

class Cryozonic_Stripe_Block_ApplePay_Bottom extends Cryozonic_Stripe_Block_ApplePay_Abstract
{
    protected $_template = 'cryozonic/stripe/form/applepay/bottom.phtml';

    public function shouldDisplay()
    {
        return parent::shouldDisplay() && parent::location() == 3;
    }
}
