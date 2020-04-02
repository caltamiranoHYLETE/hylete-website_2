<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

class SubscribePro_Autoship_Model_System_Config_Source_Cctype extends Mage_Payment_Model_Source_Cctype
{
    public function getAllowedTypes()
    {
        // Lookup available types in payment method config
        $availableTypes = Mage::getStoreConfig('payment/' . SubscribePro_Autoship_Model_Payment_Method_Cc::METHOD_CODE . '/cctypes');
        // Explode available types
        $availableTypes = explode(',', $availableTypes);

        return $availableTypes;
    }

    public function getCcAvailableTypes()
    {
        // Use parent to get option array
        $optionArray = $this->toOptionArray();
        // Parse option array into simple hash
        $availableTypes = array();
        foreach ($optionArray as $option) {
            $availableTypes[$option['value']] = $option['label'];
        }

        return $availableTypes;
    }

    public function getCcAvailableTypesSubscribeProFormat()
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');

        // Use parent to get option array
        $optionArray = $this->toOptionArray();
        // Parse option array into simple hash
        $availableTypes = array();
        foreach ($optionArray as $option) {
            $ccType = $vaultHelper->mapMagentoCardTypeToSubscribePro($option['value'], false);
            if (strlen($ccType)) {
                $availableTypes[$ccType] = $option['label'];
            }
        }

        return $availableTypes;
    }

    public function getCcAllTypesSubscribeProFormat()
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');

        // Parse option array into simple hash
        $availableTypes = array();
        foreach (Mage::getSingleton('payment/config')->getCcTypes() as $code => $name) {
            try {
                $availableTypes[$vaultHelper->mapMagentoCardTypeToSubscribePro($code)] = $name;
            }
            catch(\Exception $e) {

            }
        }

        return $availableTypes;
    }

    public function toOptionArraySubscribeProFormat()
    {
        /** @var SubscribePro_Autoship_Helper_Platform_Vault $vaultHelper */
        $vaultHelper = Mage::helper('autoship/platform_vault');

        // Use parent to get option array
        $optionArray = $this->toOptionArray();
        foreach ($optionArray as $option) {
            $ccType = $vaultHelper->mapMagentoCardTypeToSubscribePro($option['value'], false);
            if (strlen($ccType)) {
                $option['value'] = $ccType;
            }
        }

        return $optionArray;
    }
}
