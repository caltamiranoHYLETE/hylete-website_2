<?php

class Cryozonic_Stripe_Model_Source_StripeJs
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('cryozonic_stripe')->__('None')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('cryozonic_stripe')->__('Stripe.js v2')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('cryozonic_stripe')->__('Stripe.js v3 + Stripe Elements (default)')
            ),
        );
    }
}
