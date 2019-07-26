<?php

class Cryozonic_Stripe_Model_Source_CcType
{
    public function toOptionArray()
    {
        $options =  array();

        $_types = Mage::getConfig()->getNode('global/payment/cryozonic_stripe/cc_types')->asArray();

        uasort($_types, array('Mage_Payment_Model_Config', 'compareCcTypes'));

        foreach ($_types as $data)
        {
            if (isset($data['code']) && isset($data['name']))
            {
                $options[] = array(
                   'value' => $data['code'],
                   'label' => $data['name']
                );
            }
        }

        return $options;
    }
}
