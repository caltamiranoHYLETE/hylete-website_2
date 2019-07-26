<?php

class Cryozonic_Stripe_Model_Source_ExpiredAuthorizations
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('cryozonic_stripe')->__('Warn admin and don\'t capture')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('cryozonic_stripe')->__('Try to re-create the charge with a saved card')
            ),
        );
    }
}
