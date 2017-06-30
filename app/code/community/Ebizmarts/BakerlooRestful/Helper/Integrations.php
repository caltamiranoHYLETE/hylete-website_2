<?php

class Ebizmarts_BakerlooRestful_Helper_Integrations {

    /**
     * Simple config check.
     * @param string $integrationType
     * @return bool
     */
    public function canUse($integrationType) {

        $canUse = false;

        $integration = $this->getIntegrationFromConfig($integrationType);
        if($integration != '') {

            if($this->moduleInstalledAndEnabled($integration))
                $canUse = true;

        }

        return $canUse;
    }

    /**
     * Returns selected integration from config.
     * @param string $integrationType
     * @return string
     */
    public function getIntegrationFromConfig($integrationType) {
        return (string)Mage::helper('bakerloo_restful')->config('integrations/' . $integrationType);
    }

    public function moduleInstalledAndEnabled($moduleCodename) {
        $moduleInstalled = Mage::helper('bakerloo_restful')->isModuleInstalled($moduleCodename);
        $outputEnabled   = ((int)Mage::getStoreConfig("advanced/modules_disable_output/{$moduleCodename}") === 0);

        return ( is_object($moduleInstalled) and ((string)$moduleInstalled->active) == 'true' and $outputEnabled );
    }

}