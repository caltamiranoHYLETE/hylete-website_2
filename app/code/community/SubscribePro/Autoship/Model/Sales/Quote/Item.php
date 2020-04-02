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

class SubscribePro_Autoship_Model_Sales_Quote_Item extends Mage_Sales_Model_Quote_Item
{
    /**
     * Checking if there children calculated or parent item
     * when we have parent quote item and its children
     *
     * @return bool
     */
    public function isChildrenCalculated()
    {
        //If this item has children, and has a custom price set, we are in a trial scenario
        //In that case, do not allow Magento to collect subtotal, tax, and discount based off the child items
        //Magento should work this way automatically, as it doesn't fully honor custom prices
        if ($this->getHasChildren() && !is_null($this->getCustomPrice())) {
            return false;
        }
        return parent::isChildrenCalculated();
    }
}