<?php

class Potato_CANavManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ACCOUNT_LINKS_SETTING = 'po_canm/account/links';
    const GENERAL_ENABLED_EXTENSION = 'po_canm/general/enabled';

    public function startEmulation(
        $storeId,
        $area = Mage_Core_Model_App_Area::AREA_FRONTEND,
        $package = Mage_Core_Model_Design_Package::BASE_PACKAGE,
        $theme = Mage_Core_Model_Design_Package::DEFAULT_THEME
    ) {
        if (class_exists('Mage_Core_Model_App_Emulation')) {
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $emulateInfo = $appEmulation->startEnvironmentEmulation($storeId);
            Mage::getDesign()->setTheme($theme);
            Mage::getDesign()->setPackageName($package);
        } else {
            $emulateInfo = new Varien_Object;
            $emulateInfo->setStoreId(Mage::app()->getStore()->getId());
            Mage::app()->setCurrentStore($storeId);
            $initialDesign = Mage::getDesign()->setAllGetOld(array(
                    'package' => $package,
                    'store'   => $storeId,
                    'area'    => $area
                ));
            $emulateInfo->setDesign($initialDesign);
            Mage::getDesign()->setTheme($theme);
            Mage::getDesign()->setPackageName($package);
        }

        return $emulateInfo;
    }

    public function stopEmulation(Varien_Object $emulateInfo)
    {
        if (class_exists('Mage_Core_Model_App_Emulation')) {
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $appEmulation->stopEnvironmentEmulation($emulateInfo);
        } else {
            Mage::app()->setCurrentStore($emulateInfo->getStoreId());
            Mage::getDesign()->setAllGetOld($emulateInfo->getDesign());
            Mage::getDesign()->setTheme('');
            Mage::getDesign()->setPackageName('');
        }
        return $this;
    }

    public function getLinksSetting($store = null)
    {
        return unserialize(Mage::getStoreConfig(self::ACCOUNT_LINKS_SETTING, $store));
    }

    public function isEnabled($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_ENABLED_EXTENSION, $store);
    }

}