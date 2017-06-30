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
 * Sales Invoice Discount Total 
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Sales_Order_Invoice_Total_Discount extends Mage_Sales_Model_Order_Invoice_Total_Discount
{
    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return \TBT_Rewrads_Model_Sales_Order_Invoice_Total_Discount
     * 
     * @see Mage_Sales_Model_Order_Invoice_Total_Discount::collect()
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $shouldApplyRulesToParent = Mage::getStoreConfig('rewards/general/apply_rules_to_parent');
        
        // Check settings whether to apply rules on parent items as well
        if (!$shouldApplyRulesToParent) {
            return parent::collect($invoice);
        } else {
            $invoice->setDiscountAmount(0);
            $invoice->setBaseDiscountAmount(0);

            $totalDiscountAmount     = 0;
            $baseTotalDiscountAmount = 0;

            /**
             * Checking if shipping discount was added in previous invoices.
             * So basically if we have invoice with positive discount and it
             * was not canceled we don't add shipping discount to this one.
             */
            $addShippingDicount = true;
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
                if ($previusInvoice->getDiscountAmount()) {
                    $addShippingDicount = false;
                }
            }

            if ($addShippingDicount) {
                $totalDiscountAmount     = $totalDiscountAmount + $invoice->getOrder()->getShippingDiscountAmount();
                $baseTotalDiscountAmount = $baseTotalDiscountAmount + $invoice->getOrder()->getBaseShippingDiscountAmount();
            }

            /** @var $item Mage_Sales_Model_Order_Invoice_Item */
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->getParentItem() && !$orderItem->isChildrenCalculated()) {
                     continue;
                }

                $orderItemDiscount      = (float) $orderItem->getDiscountAmount();
                $baseOrderItemDiscount  = (float) $orderItem->getBaseDiscountAmount();
                $orderItemQty       = $orderItem->getQtyOrdered();

                if ($orderItemDiscount && $orderItemQty) {

                    /**
                     * Resolve rounding problems
                     *
                     * We dont want to include the weee discount amount as the right amount
                     * is added when calculating the taxes.
                     *
                     * Also the subtotal is without weee
                     */

                    $discount = $orderItemDiscount - $orderItem->getDiscountInvoiced();
                    $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountInvoiced();

                    if (!$item->isLast()) {
                        $activeQty = $orderItemQty - $orderItem->getQtyInvoiced();
                        if (method_exists('roundPrice', $invoice)) {
                            $discount = $invoice->roundPrice($discount / $activeQty * $item->getQty(), 'regular', true);
                            $baseDiscount = $invoice->roundPrice($baseDiscount / $activeQty * $item->getQty(), 'base', true);
                        } else {
                            $discount = $invoice->getStore()->roundPrice($discount / $activeQty * $item->getQty(), 'regular', true);
                            $baseDiscount = $invoice->getStore()->roundPrice($baseDiscount / $activeQty * $item->getQty(), 'base', true);
                        }
                    }

                    $item->setDiscountAmount($discount);
                    $item->setBaseDiscountAmount($baseDiscount);

                    $totalDiscountAmount += $discount;
                    $baseTotalDiscountAmount += $baseDiscount;
                }
            }

            $invoice->setDiscountAmount(-$totalDiscountAmount);
            $invoice->setBaseDiscountAmount(-$baseTotalDiscountAmount);

            $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);
            return $this;
        }
    }
}
