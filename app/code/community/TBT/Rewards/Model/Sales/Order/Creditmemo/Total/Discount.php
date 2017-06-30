<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).

 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
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
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Credit Memo Discount Total 
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Sales_Order_Creditmemo_Total_Discount extends Mage_Sales_Model_Order_Creditmemo_Total_Discount
{
    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return \TBT_Rewrads_Model_Sales_Order_Creditmemo_Total_Discount
     * 
     * @see Mage_Sales_Model_Order_Creditmemo_Total_Discount::collect()
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $shouldApplyRulesToParent = Mage::getStoreConfig('rewards/general/apply_rules_to_parent');
        
        // Check settings whether to apply rules on parent items as well
        if (!$shouldApplyRulesToParent) {
            return parent::collect($creditmemo);
        } else {
            $creditmemo->setDiscountAmount(0);
            $creditmemo->setBaseDiscountAmount(0);

            $order = $creditmemo->getOrder();

            $totalDiscountAmount = 0;
            $baseTotalDiscountAmount = 0;

            /**
             * Calculate how much shipping discount should be applied
             * basing on how much shipping should be refunded.
             */
            $baseShippingAmount = $creditmemo->getBaseShippingAmount();
            if ($baseShippingAmount) {
                $baseShippingDiscount = $baseShippingAmount * $order->getBaseShippingDiscountAmount() / $order->getBaseShippingAmount();
                $shippingDiscount = $order->getShippingAmount() * $baseShippingDiscount / $order->getBaseShippingAmount();
                $totalDiscountAmount = $totalDiscountAmount + $shippingDiscount;
                $baseTotalDiscountAmount = $baseTotalDiscountAmount + $baseShippingDiscount;
            }

            /** @var $item Mage_Sales_Model_Order_Invoice_Item */
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();

                if ($orderItem->getParentItem() && !$orderItem->isChildrenCalculated()) {
                    continue;
                }

                $orderItemDiscount      = (float) $orderItem->getDiscountInvoiced();
                $baseOrderItemDiscount  = (float) $orderItem->getBaseDiscountInvoiced();
                $orderItemQty           = $orderItem->getQtyInvoiced();

                if ($orderItemDiscount && $orderItemQty) {
                    $discount = $orderItemDiscount - $orderItem->getDiscountRefunded();
                    $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountRefunded();
                    if (!$item->isLast()) {
                        $availableQty = $orderItemQty - $orderItem->getQtyRefunded();
                        if (method_exists('roundPrice', $creditmemo)) {
                            $discount = $creditmemo->roundPrice(
                                $discount / $availableQty * $item->getQty(), 'regular', true
                            );
                            $baseDiscount = $creditmemo->roundPrice(
                                $baseDiscount / $availableQty * $item->getQty(), 'base', true
                            );
                        } else {
                            $discount = $creditmemo->getStore()->roundPrice(
                                $discount / $availableQty * $item->getQty(), 'regular', true
                            );
                            $baseDiscount = $creditmemo->getStore()->roundPrice(
                                $baseDiscount / $availableQty * $item->getQty(), 'base', true
                            );
                        }
                    }

                    $totalDiscountAmount += $discount;
                    $baseTotalDiscountAmount += $baseDiscount;

                    $item->setDiscountAmount($discount);
                    $item->setBaseDiscountAmount($baseDiscount);
                }
            }

            $creditmemo->setDiscountAmount(-$totalDiscountAmount);
            $creditmemo->setBaseDiscountAmount(-$baseTotalDiscountAmount);

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalDiscountAmount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalDiscountAmount);
            return $this;
        }
    }
}
