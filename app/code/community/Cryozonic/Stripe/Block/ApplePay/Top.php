<?php

class Cryozonic_Stripe_Block_ApplePay_Top extends Cryozonic_Stripe_Block_ApplePay_Abstract
{
    protected $_template = 'cryozonic/stripe/form/applepay/top.phtml';

    public function shouldDisplay()
    {
        return parent::shouldDisplay() && parent::location() == 1;
    }
}
