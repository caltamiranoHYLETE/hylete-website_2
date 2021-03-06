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
 * @category  Nosto
 * @package   Nosto_Tagging
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Current variation tagging block.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Variation extends Mage_Core_Block_Template
{
    /**
     * Render variation string as hidden meta data if the module is enabled for
     * the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        if (!Mage::helper('nosto_tagging/module')->isModuleEnabled()
            || !$helper->existsAndIsConnected()
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return the current variation id
     *
     * @return string|null
     */
    public function getVariationId()
    {
        $variationId = null;
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        if ($dataHelper->isMultiCurrencyMethodExchangeRate(Mage::app()->getStore())) {
            $variationId = Mage::app()->getStore()->getCurrentCurrencyCode();
        } else if ($dataHelper->isVariationEnabled(Mage::app()->getStore())){
            $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

            /** @var Mage_Customer_Model_Group $customerGroup */
            $customerGroup = Mage::getModel('customer/group')->load($groupId);
            if ($customerGroup instanceof Mage_Customer_Model_Group) {
                /* @var Nosto_Tagging_Helper_Variation $variationHelper  */
                $variationHelper = Mage::helper('nosto_tagging/variation');
                $variationId = $variationHelper->generateVariationId($customerGroup);
            }
        }

        return $variationId;
    }

    /**
     * Tells if store uses multiple currencies
     *
     * @return string
     */
    public function useMultiCurrency()
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');

        return $helper->isMultiCurrencyMethodExchangeRate(Mage::app()->getStore());
    }
}
