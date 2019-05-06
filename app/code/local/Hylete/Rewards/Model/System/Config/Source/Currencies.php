<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All Rights Reserved.
 */

class Hylete_Rewards_Model_System_Config_Source_Currencies
{
    /**
     * Returns a list of currency options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(array(
            'value' => 0,
            'label' => Mage::helper('hylete_rewards')->__('Default')
        ));

        $collection = Mage::getSingleton('rewards/currency')->getCollection();
        foreach ($collection as $currency) {
            $options[] = array(
                'value' => $currency->getId(),
                'label' => $currency->getCaption()
            );
        }
        return $options;
    }
}
