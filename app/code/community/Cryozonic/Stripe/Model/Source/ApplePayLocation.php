<?php

class Cryozonic_Stripe_Model_Source_ApplePayLocation
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 1,
                'label' => Mage::helper('cryozonic_stripe')->__('Above all payment methods')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('cryozonic_stripe')->__('Inside the Stripe payment form (default)')
            ),
            array(
                'value' => 3,
                'label' => Mage::helper('cryozonic_stripe')->__('Below all payment methods')
            ),
        );
    }
}
