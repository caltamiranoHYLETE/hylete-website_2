<?php
/**
 * Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Sweet Tooth SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).

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
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 *
 * PayPal Standard Checkout Module
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TBT_Rewards_Model_Paypal_Standard extends Mage_Paypal_Model_Standard {

    //@nelkaake Sunday April 25, 2010 : Calculated in the base currency.
    public function getPaypalZeroCheckoutFee() {
        return Mage::helper('rewards/config_paypal')->getPaypalCheckoutFee();
    }

    /**
     * We're discounting the whole amount, so we need to add a premium in order for PayPal to see the output.
     * This is because there is a bug in Magento 1.4+ that does not allow you to checkout if the total
     * is $0 but the tax amount is more than $0.       
     * @param float $discountAmount
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model           
     * @return float negative number to be added to the paypal total checkout discount amount.
     */
    protected function _getPaypalCheckoutFee($discountAmount, $_quote) {
        $discountAmount = (float) $discountAmount;
        $ppFee = 0;
        
        if ( $this->_doAddPaypalCheckoutFee($discountAmount, $_quote) ) {
            $ppFee = - ($this->getPaypalZeroCheckoutFee());
        }
        
        return $ppFee;
    }

    /**    
     * Decides whethero rn ot to add a paypal checkout fee (usually 0.01).
     * @param float $discountAmount
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model
     * @return boolean true if you should add the paypal checkout fee to the discount amount.
     */
    protected function _doAddPaypalCheckoutFee($discountAmount, $_quote) {
        
        // if the discount amount is less than the subtotal, then never add the paypal fee
        if ( $discountAmount < $_quote->getSubtotal() ) {
            return false;
        }
        
        // Only add the fee if the tax amount is greater than zero for at least one item.
        foreach ($_quote->getAllItems() as $item) {
            if ( (float) $item->getTaxAmount() > 0 ) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Returns the total amount of discount displacement due to catalog redemption rules that needs to 
     * be subtracted from the grand total.
     * This is a positive amount          
     * 
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model
     * @return float
     */
    public function getDiscountDisplacement($_quote = null) {
        
        if ( ! ($_quote instanceof Mage_Sales_Model_Order || $_quote instanceof Mage_Sales_Model_Quote) ) {
            $_quote = $this->_getQuote();
        }
        $items = $_quote->getAllItems();
        
        Mage::getSingleton('rewards/redeem')->refactorRedemptions($items);
        
        //@nelkaake -a 16/11/10: Figure out the accumulated difference in price so we can add to the discount amount 
        $acc_diff = 0;
        
        $acc_diff = $this->_getTotalCatalogDiscount($_quote);
        
        $acc_diff = $_quote->getStore()->roundPrice($acc_diff);
        if ( $acc_diff == - 0 ) $acc_diff = 0;
        
        $acc_diff = (float) $acc_diff + $this->_getPaypalCheckoutFee($acc_diff, $_quote);
        
        return $acc_diff;
    }

    /**
     * Returns a quote model that is applicable to this checkout model
     *
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote() {
        
        if ( $this->getHasEnsuredQuote() ) return $this->getEnsuredQuote();
        
        $quote = $this->getQuote();
        $items = $quote->getAllItems();
        if ( empty($items) ) {
            $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            $quote_id = $order->getQuoteId();
            $quote = Mage::getModel('rewards/sales_quote')->load($order->getQuoteId());
        }
        $this->setHasEnsuredQuote(true);
        $this->setEnsuredQuote($quote);
        
        return $quote;
    }

    /**
     * Fetches the redemption calculator model
     *
     * @return TBT_Rewards_Model_Redeem
     */
    protected function _getRedeemer() {
        return Mage::getSingleton('rewards/redeem');
    }

    /**
     * Returns the total accumulated catalog discounts on the quote model that is in this class
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $_quote=null If null uses the current session quote or the quote stored in this model
     * @return int negative discount amount
     */
    protected function _getTotalCatalogDiscount($_quote = null) {
        
        if ( ! ($_quote instanceof Mage_Sales_Model_Order || $_quote instanceof Mage_Sales_Model_Quote) ) {
            $_quote = $this->_getQuote();
        }
        $items = $_quote->getAllItems();
        
        // If the rewards catalog discount is already stored in the quote, just use that.
        $quote_discount_amount = $_quote->getRewardsDiscountAmount();
        if ( $quote_discount_amount ) {
            return $quote_discount_amount;
        }
        
        if ( ! is_array($items) ) {
            $items = array(
                $items
            );
        }
        
        $acc_discount = 0;
        foreach ($items as $item) {
            $acc_discount += $this->_getTotalItemCatalogDiscount($item);
        }
        
        return $acc_discount;
    
    }

    /**
     * Returns the total accumulated catalog discounts on an item
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item
     * @return int negative discount amount
     */
    protected function _getTotalItemCatalogDiscount($item) {
        if ( ! $item->getQuoteId() || ! $item->getId() ) {
            return 0;
        }
        
        $row_total_before_disc = $item->getRowTotalBeforeRedemptions();
        $row_total = $item->getRowTotal();
        
        if ( $item->getRewardsCatalogDiscount() ) {
            $total_discount = $item->getRewardsCatalogDiscount();
        } else {
            if ( empty($row_total_before_disc) ) {
                $item->setRowTotal($item->getRowTotalBeforeRedemptions());
                $item->setRowTotalInclTax($item->getRowTotalBeforeRedemptionsInclTax());
                $total_discount = $this->_getRedeemer()->getTotalCatalogDiscount($item);
            } else {
                $total_discount = $item->getRowTotalBeforeRedemptions() - $item->getRowTotal();
            }
        }
        
        return $total_discount;
    
    }

}
 
