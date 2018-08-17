<?php

class Potato_CANavManager_Block_System_Config_Type_Links
    extends Mage_Adminhtml_Block_Abstract
{
    protected $_designPackageFolder     = 'base';
    protected $_designThemeFolder       = 'default';

    public function __construct()
    {
        $this->setTemplate('po_canm/config/links.phtml');
    }

    public function getLinks()
    {
        $storeId = Mage::app()->getRequest()->getParam('store', '');
        $store = Mage::app()->getStore($storeId);
        $initialEnvironmentInfo = Mage::helper('po_canm')
            ->startEmulation(
                $store->getId(),
                Mage_Core_Model_App_Area::AREA_FRONTEND,
                Mage::getStoreConfig('design/package/name', $store),
                Mage::getStoreConfig('design/package/layout', $store)
            );

        $handles = $this->getLayout()->getUpdate()
            ->getFileLayoutUpdatesXml(
                Mage_Core_Model_Design_Package::DEFAULT_AREA,
                Mage::getStoreConfig('design/package/name', $store),
                Mage::getStoreConfig('design/package/layout', $store),
                $store->getId()
            );

        $layoutUpdateModel = Mage::getModel('core/layout_update');
        foreach ($handles as $handleName => $updateXml) {
            if ($handleName != 'customer_account'){
                continue;
            }
            $layoutUpdateModel
                ->fetchRecursiveUpdates($updateXml)
                ->addUpdate($updateXml->innerXml())
            ;
        }
        $xml = $layoutUpdateModel->asSimplexml();
        $customerBlockNodeActions = $xml->xpath("//block[@type='customer/account_navigation']//action[@method='addLink']");
        $referenceCustomerNodeActions = $xml->xpath("//reference[@name='customer_account_navigation']//action[@method='addLink']");

        $links = array();
        $sortOrder = 0;
        foreach ($customerBlockNodeActions as $action) {
            $action = $action->asArray();
            if (!isset($action['name']) || !isset($action['label']) || !isset($action['path'])) {
                continue;
            }
            if (!isset($action['urlParams'])) {
                $action['urlParams'] = array();
            }
            $link = new Varien_Object();
            $link->setName($action['name'])
                ->setDefaultLabel($action['label'])
                ->setNewLabel($action['label'])
                ->setVisible(1)
                ->setSortOrder($sortOrder)
                ->setPath($action['path'])
                ->setUrlParams(json_encode($action['urlParams']))
            ;
            $links[$sortOrder] = $link;
            $sortOrder++;
        }

        foreach ($referenceCustomerNodeActions as $action) {
            $action = $action->asArray();
            if (!isset($action['name']) || !isset($action['label']) || !isset($action['path'])) {
                continue;
            }
            if (!isset($action['urlParams'])) {
                $action['urlParams'] = array();
            }
            $link = new Varien_Object();
            $link->setName($action['name'])
                ->setDefaultLabel($action['label'])
                ->setNewLabel($action['label'])
                ->setVisible(1)
                ->setSortOrder($sortOrder)
                ->setPath($action['path'])
                ->setUrlParams(json_encode($action['urlParams']))
            ;
            $links[$sortOrder] = $link;
            $sortOrder++;
        }
        Mage::helper('po_canm')->stopEmulation($initialEnvironmentInfo);

        $value = $this->getLinksSetting();
        if (!$value) {
            $store = Mage::app()->getRequest()->getParam('store', null);
            $storeId = Mage::app()->getStore($store)->getId();
            $linksSetting = Mage::helper('po_canm')->getLinksSetting($storeId);
        } else {
            $linksSetting = unserialize($this->getLinksSetting());
        }

        $sortedLinks = array();
        foreach ($links as $link) {
            $name = $link->getName();
            if ($linksSetting && array_key_exists($name, $linksSetting)) {
                if (!isset($linksSetting[$name]['visible'])) {
                    $linksSetting[$name]['visible'] = 0;
                }
                $link->setNewLabel($linksSetting[$name]['label'])
                    ->setVisible($linksSetting[$name]['visible'])
                    ->setSortOrder($linksSetting[$name]['sort_order']);
            }
            $sortedLinks[$link->getSortOrder()] = $link;
        }
        ksort($sortedLinks);
        $links = $sortedLinks;

        return $links;
    }

    public function getYesNoOptions()
    {
        return Mage::getModel('adminhtml/system_config_source_yesno')->toArray();
    }
}