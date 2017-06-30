<?php

/**
 * WDCA - Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 *      https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL:
 *      http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * By adding to, editing, or in any way modifying this code, WDCA is
 * not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code.
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the
 * provided Sweet Tooth License.
 * Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time WDCA spent
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension.
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy
 * immediately.
 *
 * @category   [TBT]
 * @package    [TBT_RewardsSocial2]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com
 */

/**
 * Purchase Share Block
 *
 * @category   TBT
 * @package    TBT_RewardsSocial2
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_RewardsSocial2_Block_Purchase extends Mage_Checkout_Block_Onepage_Success
{
    /**
     * Is purchase sharing enabled?
     * @return bool
     */
    public function isPurchaseSharingEnabled()
    {
        $purchaseButtons = Mage::getStoreConfig('rewards/rewardssocial2/purchase_buttons');
        return !empty($purchaseButtons);
    }

    /**
     * Fetch column count
     * @return int
     */
    public function getColumnCount() 
    {
        return 3;
    }

    /**
     * Create the sharing buttons for a product
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return string (HTML)
     */
    public function getSocialButtonsHtml($product)
    {
        return $this->getLayout()->createBlock('rewardssocial2/social')
            ->setTemplate('rewardssocial2/sharing.phtml')
            ->setActionType('purchase')
            ->setData('product', $product)
            ->setData('order_id', $this->getOrderId())
            ->toHtml();
    }

    /**
     * Fetch the order that was just made
     * @return Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        return Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string $allowableTags
     * @param bool $escape
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $escape = false)
    {
        $result = strip_tags($data, $allowableTags);
        return $escape ? $this->escapeHtml($result, $allowableTags) : $result;
    }
}
