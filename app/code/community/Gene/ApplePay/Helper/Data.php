<?php

/**
 * Class Gene_ApplePay_Helper_Data
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */ 
class Gene_ApplePay_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Determine if the module is active based on the parent Gene_Braintree module
     *
     * @return bool
     */
    public function hasDependencies()
    {
        // Gene_ApplePay requires the core Gene_Braintree module
        if (!Mage::helper('core')->isModuleEnabled('Gene_Braintree')) {
            return false;
        }

        // Verify the Gene_Braintree module is at least v2.1.0
        if (version_compare(Mage::getConfig()->getModuleConfig('Gene_Braintree')->version, '2.2.0', '<')) {
            return false;
        }

        return true;
    }
}
