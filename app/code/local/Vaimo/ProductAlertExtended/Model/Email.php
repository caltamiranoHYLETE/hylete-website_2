<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_ProductAlert
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert Email processor
 *
 * @category   Mage
 * @package    Mage_ProductAlert
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Vaimo_ProductAlertExtended_Model_Email extends Mage_ProductAlert_Model_Email
{

    /**
     * Send customer email
     *
     * @return bool
     */
    public function send()
    {
        if (is_null($this->_website) || is_null($this->_customer)) {
            return false;
        }
        if (($this->_type == 'price' && count($this->_priceProducts) == 0) || ($this->_type == 'stock' && count($this->_stockProducts) == 0)) {
            return false;
        }
        if (!$this->_website->getDefaultGroup() || !$this->_website->getDefaultGroup()->getDefaultStore()) {
            return false;
        }

        $store      = $this->_website->getDefaultStore();
        $storeId    = $store->getId();

        if ($this->_type == 'price' && !Mage::getStoreConfig(self::XML_PATH_EMAIL_PRICE_TEMPLATE, $storeId)) {
            return false;
        } elseif ($this->_type == 'stock' && !Mage::getStoreConfig(self::XML_PATH_EMAIL_STOCK_TEMPLATE, $storeId)) {
            return false;
        }

        if ($this->_type != 'price' && $this->_type != 'stock') {
            return false;
        }

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        if ($this->_type == 'price') {
            $this->_getPriceBlock()
                ->setStore($store)
                ->reset();
            foreach ($this->_priceProducts as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getPriceBlock()->addProduct($product);
            }
            $block = $this->_getPriceBlock()->toHtml();
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_PRICE_TEMPLATE, $storeId);
        } else {
            $this->_getStockBlock()
                ->setStore($store)
                ->reset();
            foreach ($this->_stockProducts as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getStockBlock()->addProduct($product);
            }
            $this->_getStockBlock()->setCustomerEmail($this->_customer->getEmail());
            $this->_getStockBlock()->setCustomerId($this->_customer->getEntityId());
            $this->_getStockBlock()->setCustomerName($this->_customer->getName());
            $block = $this->_getStockBlock()->toHtml();
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_STOCK_TEMPLATE, $storeId);
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        Mage::getModel('core/email_template')
            ->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => $storeId
            ))->sendTransactional(
                $templateId,
                Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId),
                $this->_customer->getEmail(),
                $this->_customer->getName(),
                array(
                    'customerName'  => $this->_customer->getName(),
                    'alertGrid'     => $block
                )
            );

        return true;
    }
}
