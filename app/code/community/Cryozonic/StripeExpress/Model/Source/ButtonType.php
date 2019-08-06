<?php

class Cryozonic_StripeExpress_Model_Source_ButtonType
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'default',
                'label' => Mage::helper('cryozonic_stripeexpress')->__('Default')
            ),
            array(
                'value' => 'buy',
                'label' => Mage::helper('cryozonic_stripeexpress')->__('Buy')
            ),
            array(
                'value' => 'donate',
                'label' => Mage::helper('cryozonic_stripeexpress')->__('Donate')
            ),
        );
    }
}
