<?php

class Cryozonic_StripeExpress_Model_Source_ButtonTheme
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'dark',
                'label' => Mage::helper('cryozonic_stripeexpress')->__('Dark')
            ),
            array(
                'value' => 'light',
                'label' => Mage::helper('cryozonic_stripeexpress')->__('Light')
            ),
            array(
                'value' => 'light-outline',
                'label' => Mage::helper('cryozonic_stripeexpress')->__('Light-Outline')
            ),
        );
    }
}
