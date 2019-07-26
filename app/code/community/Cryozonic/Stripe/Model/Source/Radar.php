<?php

class Cryozonic_Stripe_Model_Source_Radar
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('cryozonic_stripe')->__('Disabled')
            ),
            array(
                'value' => 10,
                'label' => Mage::helper('cryozonic_stripe')->__('Enabled')
            ),
        );
    }
}
