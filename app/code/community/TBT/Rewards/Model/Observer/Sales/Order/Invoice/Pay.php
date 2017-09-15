<?php

/**
 * Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 * https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * By adding to, editing, or in any way modifying this code, Sweet Tooth is
 * not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code.
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by Sweet Tooth, outlined in the
 * provided Sweet Tooth License.
 * Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time Sweet Tooth spent
 * during the support process.
 * Sweet Tooth does not guarantee compatibility with any other framework extension.
 * Sweet Tooth is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy
 * immediately.
 *
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer sales Order Invoice Pay
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Observer_Sales_Order_Invoice_Pay
    extends TBT_Rewards_Model_Observer_Sales_Order_Abstract
{
    /**
     * Approve points after invoice is created, if this option is enabled in admin.
     *
     * @param  Varien_Event_Observer $observer
     * @see Event 'sales_order_invoice_save_commit_after'
     * @see Admin Config 'rewards/orders/shouldApprovePointsOn'
     * @return $this
     */
    public function approveAssociatedPendingTransfersOnInvoice($observer)
    {
        $event = $observer->getEvent();

        if (!$event) {
            return $this;
        }

        $order = $event->hasInvoice() ? $event->getInvoice()->getOrder() : $event->getOrder();

        if (!$order) {
            return $this;
        }

        if (Mage::helper('rewards/config')->shouldApprovePointsOnInvoice()) {
            $this->approveOrderPointTransfers($order);
        }

        return $this;
    }
}
