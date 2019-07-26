<?php

class Cryozonic_Stripe_Model_Source_Mode
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Cryozonic_Stripe_Model_Standard::TEST,
                'label' => Mage::helper('cryozonic_stripe')->__('Test')
            ),
            array(
                'value' => Cryozonic_Stripe_Model_Standard::LIVE,
                'label' => Mage::helper('cryozonic_stripe')->__('Live')
            ),
        );
    }
}
