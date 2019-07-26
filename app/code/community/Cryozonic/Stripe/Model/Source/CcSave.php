<?php

class Cryozonic_Stripe_Model_Source_CcSave
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('cryozonic_stripe')->__('Disabled')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('cryozonic_stripe')->__('Ask the customer')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('cryozonic_stripe')->__('Save without asking')
            ),
        );
    }
}
