<?php

/**
 * Main helper for the loyalty abstraction.
 *
 * @package Ebizmarts_BakerlooLoyalty
 */
class Ebizmarts_BakerlooLoyalty_Helper_Data extends Mage_Core_Helper_Abstract {

    const LOYALTY_INTEGRATIONS_CONFIG_PATH = 'default/bakerloo_loyalty/integrations';

    /**
     * Simple config check.
     */
    public function canUse() {
        return Mage::helper('bakerloo_restful/integrations')->canUse('loyalty');
    }

    /**
     * Returns selected integration from config.
     *
     * @return string
     */
    public function getIntegrationFromConfig() {
        return Mage::helper('bakerloo_restful/integrations')->getIntegrationFromConfig('loyalty');
    }


    public function isSweetTooth($loyalty = null) {
        if(is_null($loyalty))
            $loyalty = Mage::getModel('bakerloo_restful/integrationDispatcher', array('integration_type' => 'loyalty') );

        $configOk  = ($loyalty->getLoyaltyConfig() == 'TBT_Rewards');
        $isEnabled = $loyalty->isEnabled();

        return (bool)($configOk and $isEnabled);
    }
}