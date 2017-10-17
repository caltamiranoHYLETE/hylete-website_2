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
 * Helper Transfer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Helper_Transfer extends Mage_Core_Helper_Abstract
{
    /**
     * Creates a customer point-transfer of any amount or currency.
     *
     * @param  int $numPoints                : Quantity of points to transfer: positive=>distribution, negative=>redemption
     * @param  Mage_Sales_Model_Order $order  :  The order
     * @param  int $ruleId                   : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
     * @return boolean                        : whether or not the point-transfer succeeded
     */

    public function transferOrderPoints($numPoints, $order, $ruleId)
    {
        if (is_numeric($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        $orderId = $order->getId();
        $customerId = $order->getCustomerId();

        if (!$orderId || !$customerId) {
            return false;
        }

        $transfer = $this->initTransfer($numPoints, $ruleId, $customerId, (bool) $order->getCustomerId());
        if (!$transfer) {
            return false;
        }

        $transfer->setReasonId(Mage::helper('rewards/transfer_reason')->getReasonId('order'));
        if (!$transfer->setStatusId(null, Mage::helper('rewards/config')->getInitialTransferStatusAfterOrder())) {
            return false;
        }

        if ($numPoints > 0) {
            $transfer->setComments(Mage::getStoreConfig('rewards/transferComments/orderEarned'));
        } else if ($numPoints < 0) {
            $transfer->setComments(Mage::getStoreConfig('rewards/transferComments/orderSpent'));
        }

        $transfer->setOrderId($orderId)->setCustomerId($customerId)->save();
        return true;
    }

    /**
     * Creates a customer point-transfer of any amount or currency.
     *
     * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
     * @param  int $rule_id       : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
     * @return boolean            : whether or not the point-transfer succeeded
     */
    public function transferSendfriendPoints($num_points, $rule_id, $productId) {
        $transfer = $this->initTransfer ( $num_points, $rule_id );
        if (! $transfer) {
            return false;
        }

        $transfer->setReasonId(Mage::helper('rewards/transfer_reason')->getReasonId('send_friend'))
            ->setReferenceId($productId);

        // get the default starting status - usually Pending
        if (! $transfer->setStatusId ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterSendfriend () )) {
            // we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
            return false;
        }
        $transfer->setComments ( Mage::getStoreConfig ( 'rewards/transferComments/tellAFriendEarned' ) )->setCustomerId ( Mage::getSingleton ( 'customer/session' )->getCustomerId () )->save ();

        return true;
    }

    /**
     * Creates a customer point-transfer of any amount or currency.
     *
     * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
     * @param  int $rule_id       : The ID of the rule that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
     * @return boolean            : whether or not the point-transfer succeeded
     * @deprecated from version 1.7.6.3+
     */
    public function transferPollPoints($num_points, $poll_id, $rule_id) {
        $transfer = $this->initTransfer ( $num_points, $rule_id );
        if (! $transfer) {
            return false;
        }

        $transfer->setReasonId(Mage::helper('rewards/transfer_reason')->getReasonId('poll'));

        // get the default starting status - usually Pending
        if (! $transfer->setStatusId ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterPoll () )) {
            // we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
            return false;
        }
        $transfer->setPollId ( $poll_id )->setComments ( Mage::getStoreConfig ( 'rewards/transferComments/pollEarned' ) )->setCustomerId ( Mage::getSingleton ( 'customer/session' )->getCustomerId () )->save ();

        return true;
    }

    /**
     * Creates a customer point-transfer of any amount or currency.
     *
     * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
     * @param  int $customer_id
     * @param  int $rule          : The rule model that allowed this transfer to be created... RULE MAY HAVE BEEN DISCONTINUED
     * @return boolean            : whether or not the point-transfer succeeded
     */
    public function transferSignupPoints($num_points, $customer_id, $rule) {
        // ALWAYS ensure that we only give an integral amount of points
        $num_points = floor ( $num_points );

        if ($num_points == 0) {
            return false;
        }

        $transfer = Mage::getModel ( 'rewards/transfer' );
        $currency_id = Mage::helper('rewards/currency')->getDefaultCurrencyId();
        if ((Mage::getModel('rewards/customer')->loadPointsBalance()->getUsablePointsBalance($currency_id) + $num_points) < 0) {
            throw Exception ('Your points balance cannot be negative.');
        }

        $reasonId = Mage::helper('rewards/transfer_reason')->getReasonId('signup');
        $transfer->setReasonId($reasonId);

        //get the default starting status - usually Pending
        if (! $transfer->setStatusId ( null, Mage::helper ( 'rewards/config' )->getInitialTransferStatusAfterSignup () )) {
            return false;
        }

        $transfer->setId(null)
            ->setQuantity($num_points)
            ->setComments(Mage::getStoreConfig('rewards/transferComments/signupEarned'))
            ->setRuleId($rule->getId())
            ->setCustomerId($customer_id)
            ->setReferenceId($customer_id)
            ->setAsSignup()
            ->save();

        return true;
    }
    /**
     * Creates a customer point-transfer of any amount or currency.
     * TODO Move this into a separate model that extends the Transfer model and instantiate it.
     *
     * @param  int $num_points    : Quantity of points to transfer: positive=>distribution, negative=>redemption
     * @param  int $friend_id
     * @param  string $personal_comment
     * @return boolean            : whether or not the point-transfer succeeded
     */
    public function transferPointsToFriend($num_points, $friend_id, $personal_comment) {
        if (! Mage::getSingleton ( 'customer/session' )->getCustomerId ()) {
            return false;
        }

        // ALWAYS ensure that we only give an integral amount of points
        $num_points = floor ( $num_points );

        if ($num_points == 0) {
            return false;
        }

        $recipient_transfer = Mage::getModel ( 'rewards/transfer' );
        $sender_transfer = Mage::getModel ( 'rewards/transfer' );

        // get the default starting status - usually Pending
        if (! $recipient_transfer->setStatusId ( null, Mage::helper ( 'rewards/config' )->getInitialTransferToFriendStatus () )) {
            // we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
            return false;
        }
        // get the default starting status - usually Pending
        if (! $sender_transfer->setStatusId ( null, Mage::helper ( 'rewards/config' )->getInitialTransferToFriendStatus () )) {
            // we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
            return false;
        }

        $reasonHelper = Mage::helper('rewards/transfer_reason');
        $recipient_transfer->setReasonId($reasonHelper->getReasonId('assign_from'));
        $sender_transfer->setReasonId($reasonHelper->getReasonId('assign_to'));

        $to_customer = Mage::getModel ( 'customer/customer' )->load ( $friend_id );
        $from_customer = Mage::getModel ( 'customer/customer' )->load ( Mage::getModel ( 'customer/session' )->getCustomerId () );

        $customer = Mage::getModel ( 'rewards/customer' )->load ( Mage::getModel ( 'customer/session' )->getCustomerId () );
        $currency_id = Mage::helper('rewards/currency')->getDefaultCurrencyId();
        if (($customer->getUsablePointsBalance ( $currency_id ) + $num_points) < 0) {
            $error = $this->__ ( 'Not enough points for transaction. You have %s, but you need %s', Mage::getModel ( 'rewards/points' )->set ( $currency_id, $customer->getUsablePointsBalance ( $currency_id ) ), Mage::getModel ( 'rewards/points' )->set ( $currency_id, $num_points * - 1 ) );
            throw new Exception ( $error );
        }

        $default_send_comment = Mage::getStoreConfig ( 'rewards/transferComments/sendToFriend' );
        $default_send_comment = str_replace('\n', "\n", $default_send_comment);
        $sender_comments = $this->__ ($default_send_comment , $to_customer->getName (), $personal_comment );

        $sender_transfer->setId ( null )->setQuantity ( $num_points * - 1 )
                ->setCustomerId ( $from_customer->getId () )->setToFriendId ( $to_customer->getId () )
                ->setComments ( $sender_comments )->save ();

        $default_receive_comment = Mage::getStoreConfig ( 'rewards/transferComments/receiveFromFriend' );
        $default_receive_comment = str_replace('\n', "\n", $default_receive_comment);
        $receiver_comment = $this->__ ( $default_receive_comment, $from_customer->getName (), $personal_comment );

        $recipient_transfer->setId ( null )
                ->setQuantity ( $num_points )->setCustomerId ( $to_customer->getId () )
                ->setFromFriendId ( $from_customer->getId () )->setComments ( $receiver_comment )->save ();

        return true;
    }

    /**
     * Validate that the customer has enough points for this order
     *
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    public function validateCustomerBalance($order)
    {
        $customerId = $order->getCustomerId();

        if ($customerId) {
            $customer = Mage::getModel('rewards/customer')->load($customerId);
            $totalPointsSpent = array();

            // Collect spent catalog points
            $catalogTransfers = Mage::getSingleton('rewards/observer_sales_catalogtransfers');
            foreach ($catalogTransfers->getAllRedeemedPoints () as $redeemedPointTotals) {
                if (!$redeemedPointTotals) {
                    continue;
                }

                foreach ($redeemedPointTotals as $transferPoints) {
                    $transferPoints = (array) $transferPoints;

                    $currencyId = $transferPoints[TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID];
                    if (!array_key_exists($currencyId, $totalPointsSpent)) {
                        $totalPointsSpent[$currencyId] = 0;
                    }

                    $totalPointsSpent[$currencyId] +=
                        $transferPoints[TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT]
                        * $transferPoints[TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY]
                        * -1;
                }
            }

            // Collect spent cart points
            $cartTransfers = Mage::getSingleton('rewards/observer_sales_carttransfers');
            foreach ($cartTransfers->getRedemptionRuleIds() as $ruleId) {
                $cartPoints = Mage::getSingleton('rewards/session')->calculateCartPoints($ruleId, $order->getAllItems(), true);

                if (!is_array($cartPoints)) {
                    continue;
                }

                if (!array_key_exists($cartPoints['currency'], $totalPointsSpent)) {
                    $totalPointsSpent[$cartPoints['currency']] = 0;
                }

                $totalPointsSpent[$cartPoints['currency']] += $cartPoints['amount'];
            }

            foreach ($totalPointsSpent as $currency => $amount) {
                if (($customer->getUsablePointsBalance($currency) + $amount) < 0) {
                    $error = $this->__( 'Not enough points for transaction. You have %s, but you need %s.',
                        Mage::getModel('rewards/points')->set($currency, $customer->getUsablePointsBalance($currency)),
                        Mage::getModel('rewards/points')->set($currency, $amount * -1)
                    );
                    throw new Exception($error);
                }
            }
        }
    }

    /**
     * Initiates a transfer model based on given criteria and verifies usage.
     *
     * @deprecated As of Sweet Tooth 1.5 and up functions should call their own
     * derivation of the TBT_Rewards_Model_Transfer model which contains this method.
     *
     * @param integer $num_points
     * @param integer $rule_id
     * @return TBT_Rewards_Model_Transfer
     */
    public function initTransfer($num_points, $rule_id, $customerId = null, $skipChecks = false) {
        if (
            !$skipChecks
            && !Mage::getSingleton('rewards/session')->isCustomerLoggedIn()
            && !Mage::getSingleton('rewards/session')->isAdminMode()
            && !Mage::getSingleton('rewards/session')->isRecurringOrderBeingPlaced()
        ) {
            return null;
        }
        // ALWAYS ensure that we only give an integral amount of points
        $num_points = floor ( $num_points );

        if ($num_points == 0) {
            return null;
        }

        $transfer = Mage::getModel ( 'rewards/transfer' );
        if ($num_points <= 0) {
            $customerId = $customerId ? $customerId : Mage::getSingleton('customer/session')->getCustomerId();
            $customer = Mage::getModel('rewards/customer')->load($customerId);

            $currency_id = Mage::helper('rewards/currency')->getDefaultCurrencyId();
            if (($customer->getUsablePointsBalance ( $currency_id ) + $num_points) < 0) {
                $error = $this->__ ( 'Not enough points for transaction. You have %s, but you need %s.', Mage::getModel ( 'rewards/points' )->set ( $currency_id, $customer->getUsablePointsBalance ( $currency_id ) ), Mage::getModel ( 'rewards/points' )->set ( $currency_id, $num_points * - 1 ) );
                throw new Exception ( $error );
            }
        }

        $now = Mage::getModel('core/date')->gmtDate();
        $transfer->setId(null)
            ->setCreatedAt($now)
            ->setUpdatedAt($now)
            ->setQuantity($num_points)
            ->setCustomerId($customerId)
            ->setRuleId($rule_id);

        return $transfer;
    }

    /**
     * Gets a list of all rule ID's that are associated with the given order/shoppingcart/quote.
     * @deprecated.  Use order->getAPpliedDistriCartRuleIds() instead.
     *
     * @param   Mage_Sales_Model_Order  $order  : The order object with which the returned rules are associated
     * @return  array(int)                      : An array of rule ID's that are associated with the order
     */
    public function getCartRewardsRuleIds($order) {
        /* TODO: make this method return REWARDS-SYSTEM rule id's ONLY */
        /* TODO - from JAY: You can do this by using the rewards/catalog_rule or rewards_salesrule_rule models. */
        // look up all rule ID's associated with this order, or shopping cart
        $rule_ids_string = $order->getAppliedRuleIds ();
        if (empty ( $rule_ids_string )) {
            $rule_ids = array ();
        } else {
            $rule_ids = explode ( ',', $rule_ids_string );
            $rule_ids = array_unique ( $rule_ids );
        }
        return $rule_ids;
    }

    /**
     * Gets a list of all rule ID's that are associated with the given item.
     *
     * @param   Mage_Sales_Model_Quote_Item $item   : The item object with which the returned rules are associated
     * @return  array(int)                          : An array of rule ID's that are associated with the item
     */
    public function getCatalogRewardsRuleIds($item, $wId = null) {
        return $this->getCatalogRewardsRuleIdsForProduct ( $item->getProductId (), $wId );
    }

    /**
     * Gets a list of all rule ID's that are associated with the given product id.
     * @see THIS GETS ALL RULES!!!!!!
     *
     * @param   int $productId                      : The item id for with which the returned rules are associated
     * @return  array(int)                          : An array of rule ID's that are associated with the item
     */
    public function getCatalogRewardsRuleIdsForProduct($productId, $wId = null, $gId = null) {
        $p = Mage::getModel ( 'rewards/catalog_product' )->load ( $productId );
        $rules = $p->getCatalogRewardsRuleIdsForProduct ( $wId, $gId );
        return $rules;
    }

    /**
     * @nelkaake Wednesday May 5, 2010: move to another helper or model class
     * @param unknown_type $item
     * @return unknown
     */
    public function getEarnedPointsOnItem($item) {
        $points_to_earn = ( array ) Mage::helper ( 'rewards' )->unhashIt ( $item->getEarnedPointsHash () );

        $currency_points = array ();

        $item_has_points = false;
        if ($points_to_earn) {
            foreach ( $points_to_earn as $points ) {
                if ($points) {
                    $item_has_points = true;
                    $points = ( array ) $points;
                    if (isset ( $currency_points [$points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID]] )) {
                        $currency_points [$points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID]] += ($points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY]);
                    } else {
                        $currency_points [$points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID]] = ($points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT] * $points [TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY]);
                    }
                }
            }
        }

        return $currency_points;
    }

    /**
     * Returns the rewards catalogrule points action singleton
     *
     * @return TBT_Rewards_Model_Catalogrule_Actions
     */
    private function getActionsSingleton() {
        return Mage::getSingleton ( 'rewards/catalogrule_actions' );
    }

    /**
     * returns an empty product if the product model could not be found
     *
     * @param   Mage_Sales_Model_Quote_Item||TBT_Rewards_Model_Catalog_Product $item                : the catalog item associated
     * @return TBT_Rewards_Model_Catalog_Product
     */
    private function assureProduct($item) {
        $requestParams = Mage::app()->getRequest()->getParams();
        $buyRequest = $this->_getProductRequest($requestParams);

        if ($item instanceof TBT_Rewards_Model_Catalog_Product) {
            $product = &$item;
        } else if ($item instanceof Mage_Catalog_Model_Product) {
            $item->getTypeInstance(false)->prepareForCartAdvanced($buyRequest, $item);
            $product = $this->assureProduct ( TBT_Rewards_Model_Catalog_Product::wrap ( $item ) );
        } else if ($this->hasGetProductFunc ( $item )) {
            $product = $item->getProduct ();
            if (empty ( $product )) {
                $product = Mage::getModel ( 'rewards/catalog_product' );
            }
        } else {
            $product = Mage::getModel ( 'rewards/catalog_product' );
        }

        return $product;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   mixed $requestInfo
     * @return  Varien_Object
     */
    protected function _getProductRequest($requestInfo) {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric ( $requestInfo )) {
            $request = new Varien_Object ();
            $request->setQty ( $requestInfo );
        } else {
            $request = new Varien_Object ( $requestInfo );
        }

        if (! $request->hasQty ()) {
            $request->setQty ( 1 );
        }
        return $request;
    }

    private function hasGetProductFunc($obj) {
        $ret = false;
        if ($this->isItem ( $obj ) || $obj instanceof Varien_Object) { // params are function($rule)
            $ret = true;
        }
        return $ret;
    }

    private function isItem($obj) {
        $ret = false;
        if ($obj instanceof Mage_Sales_Model_Quote_Item || $obj instanceof Mage_Sales_Model_Quote_Item_Abstract || $obj instanceof Mage_Sales_Model_Quote_Address_Item || $obj instanceof Mage_Sales_Model_Order_Item || $obj instanceof Mage_Sales_Model_Order_Invoice_Item || $obj instanceof Mage_Sales_Model_Order_Creditmemo_Item || $obj instanceof Mage_Sales_Model_Order_Shipment_Item) { // params are function($rule)
            $ret = true;
        }
        return $ret;
    }

    /**
     * Calculates the amount of points to be given or deducted from a customer based on catalog item,
     * given the rule that is being executed and the item that caused the rule to run.
     * @nelkaake Wednesday May 5, 2010: TODO move this to something other than the transfer helper      *
     *
     * @param   int                         $rule_id            : the ID of the rule to execute
     * @param   Mage_Sales_Model_Quote_Item||TBT_Rewards_Model_Catalog_Product $item                : the catalog item associated
     * @param   bool                        $allow_redemptions  : whether or not to calculate redemptions, if given
     * @param   bool                        $isGrouped          : If the product is a grouped product
     * @return  array                                           : 'amount' & 'currency' as keys
     */
    public function calculateCatalogPoints($rule_id, $item, $allow_redemptions,$isGrouped = false) {
        Varien_Profiler::start("TBT_Rewards:: Catalog points calculator");

        Varien_Profiler::start("TBT_Rewards:: Catalog points calculator (init)");

        // Load the rule and product model.
        $rule = $this->getCatalogRule($rule_id);
        $product = $this->assureProduct($item);


        // Get the store configuration
        $prices_include_tax = Mage::helper('tax')->priceIncludesTax();


        // Instantiate what the product cost will be evaluated to
        // If no rule needs it, then just skip this step.
        if($rule->getPointsAction() == 'give_by_profit') {
            if ( !$product->getCost() ) {
                $product = $product->load($product->getId());
            }
            $product_cost = (int) $product->getCost();
        } else {
            $product_cost = 0;
        }

        if ( $this->isItem($item) ) {
            $qty = ($item->getQty() > 0) ? $item->getQty() : 1; //@nelkaake 04/03/2010 2:05:12 PM (terniary check jsut in case)
            if ( $prices_include_tax ) {
                //@nelkaake Changed on Wednesday May 5, 2010:
                $price = Mage::helper('rewards/price')->getReversedCurrencyPrice($item->getRowTotalAfterRedemptions());
                if ( Mage::helper('rewards/config')->earnCatalogPointsForTax() ) {
                    $price = Mage::helper('rewards/price')->getReversedCurrencyPrice($item->getRowTotalAfterRedemptionsInclTax());
                }
            } else {
                $price = Mage::helper('rewards/price')->getReversedCurrencyPrice($item->getRowTotalAfterRedemptions());
            }
            $profit = $item->getBaseRowTotal() - ($product_cost * $qty); //@nelkaake 04/03/2010 2:05:12 PM
        } else {
            //@nelkaake Changed on Wednesday May 5, 2010:
            $qty = 1; //@nelkaake 04/03/2010 2:05:12 PM
            $price = $product->getFinalPrice();

            $bundlePriceModel = Mage::getModel('bundle/product_price');
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
                    && method_exists($bundlePriceModel, 'getTotalPrices')) {
                $price = $bundlePriceModel->getTotalPrices($product, 'max', 1, null);
            }
            $profit = $price - $product_cost;

            //@nelkaake Added on Wednesday May 5, 2010:
            if ( ! Mage::helper('rewards/config')->earnCatalogPointsForTax() && $prices_include_tax ) {
                $price = $price / (1 + ((float) $product->getTaxPercent() / 100));
                $profit = $price - $product_cost;
            }
        }

        // Set default price and profit values
        if ( $profit < 0 ) {
            $profit = 0;
        }
        if ( $price < 0 ) {
            $price = 0;
        }

        Varien_Profiler::stop("TBT_Rewards:: Catalog points calculator (init)");

        if ( $rule->getId() ) {
            if ( $rule->getPointsAction() == 'give_points' ) {
                // give a flat number of points if this rule's conditions are met
                // since this is a catalog rule, the points are relative to the quantity
                // if this is agroup product , points get updated when the qty is changes in
                // the product view page. So get the changes qty.
                if ($isGrouped) {
                    $qty = ($item->getQty() > -1) ? $item->getQty() : 1;
                }
                $points_to_transfer = $rule->getPointsAmount() * $qty; //@nelkaake 04/03/2010 2:05:12 PM
            } elseif ( ($rule->getPointsAction() == 'deduct_points') && $allow_redemptions ) {
                // deduct a flat number of points if this rule's conditions are met
                // since this is a catalog rule, the points are relative to the quantity
                $points_to_transfer = $rule->getPointsAmount() * - 1;
            } elseif ( $rule->getPointsAction() == 'give_by_amount_spent' || $rule->getPointsAction() == 'give_by_profit' ) {
                if ( $rule->getPointsAction() == 'give_by_amount_spent' ) {
                    $value = $price;
                } elseif ( $rule->getPointsAction() == 'give_by_profit' ) {
                    $value = $profit;
                } else {
                    $value = 0;
                }

                // give a set qty of points per every given amount spent if this rule's conditions are met
                $points_to_transfer = $rule->getPointsAmount() * floor(round($value / $rule->getPointsAmountStep(), 5));
                // group product , points get updated when the qty is changes.
                if ($isGrouped) {
                    $qty = ($item->getQty() > -1) ? $item->getQty() : 1;
                    $points_to_transfer = $rule->getPointsAmount() * floor(round($value * $qty / $rule->getPointsAmountStep(), 5));
                }
                if ( $rule->getPointsMaxQty() > 0 ) {
                    if ( $points_to_transfer > $rule->getPointsMaxQty() ) {
                        $points_to_transfer = $rule->getPointsMaxQty();
                    }
                }
                if ( $points_to_transfer < 0 ) {
                    $points_to_transfer = 0;
                }
            } elseif ( ($rule->getPointsAction() == 'deduct_by_amount_spent') && $allow_redemptions ) {
                // deduct a set qty of points per every given amount spent if this rule's conditions are met
                $price = $product->getFinalPrice();
                $points_to_transfer = $rule->getPointsAmount() * ceil(round($price / $rule->getPointsAmountStep(), 5)) * - 1;

                if ( $rule->getPointsMaxQty() > 0 ) {
                    if ( $points_to_transfer < ($rule->getPointsMaxQty() * - 1) ) {
                        $points_to_transfer = $rule->getPointsMaxQty() * - 1;
                    }
                }
            } else {
                // whatever the Points Action is set to is invalid
                // - this means no transfer of points
                Varien_Profiler::stop("TBT_Rewards:: Catalog points calculator");
                return null;
            }

            //@nelkaake Added on Sunday May 30, 2010:
            if ( $max_points_spent = $rule->getPointsMaxQty() * $qty ) {
                if ( $points_to_transfer < 0 ) {
                    if ( - $points_to_transfer > $max_points_spent ) $points_to_transfer = - $max_points_spent;
                } else {
                    if ( $points_to_transfer > $max_points_spent ) $points_to_transfer = $max_points_spent;
                }
            }

            Varien_Profiler::stop("TBT_Rewards:: Catalog points calculator");
            return array(
                'amount' => $points_to_transfer,
                'currency' => $rule->getPointsCurrencyId()
            );
        }

        Varien_Profiler::stop("TBT_Rewards:: Catalog points calculator");
        return null;
    }

    /**
     * Calculates the amount of points to be given or deducted from a customer's cart, given the
     * rule that is being executed and possibly a list of items to act upon, if applicable.
     *
     * @deprecated ??? not sure if this is deprecated... see calculateCartPoints in Rewards/session singleton
     *
     * @param   int                                 $rule_id            : the ID of the rule to execute
     * @param   array(Mage_Sales_Model_Quote_Item)  $order_items        : the list of items to act upon
     * @param   boolean                             $allow_redemptions  : whether or not to calculate redemption rules
     * @return  array                                                   : 'amount' & 'currency' as keys
     */
    public function calculateCartDiscounts($rule_id, $order_items) {
        $rule = $this->getSalesRule ( $rule_id );
        $crActions = $this->getActionsSingleton ();

        if ($rule->getId ()) {
            if ($crActions->isDeductPointsAction ( $rule->getPointsAction () )) {
                // give a flat number of points if this rule's conditions are met
                $discount = $rule->getPointsDiscountAmount ();
            } else if ($crActions->isDeductByAmountSpentAction ( $rule->getPointsAction () )) {
                // deduct a set qty of points per every given amount spent if this rule's conditions are met
                // - this is a total price amongst ALL associated items, so add it up
                $price = $this->getTotalAssociatedItemPrice ( $order_items, $rule->getId () );
                $points_to_transfer = $rule->getPointsAmount () * floor ( round($price / $rule->getPointsAmountStep (), 5) );

                if ($rule->getPointsMaxQty () > 0) {
                    if ($points_to_transfer > $rule->getPointsMaxQty ()) {
                        $points_to_transfer = $rule->getPointsMaxQty ();
                    }
                }

                $discount = $rule->getPointsDiscountAmount () * ($points_to_transfer / $rule->getPointsAmount ());
            } else if ($rule->getPointsAction () == 'deduct_by_qty') {
                // deduct a set qty of points per every given qty of items if this rule's conditions are met
                // - this is a total quantity amongst ALL associated items, so add it up
                $qty = $this->getTotalAssociatedItemQty ( $order_items, $rule->getId () );
                $points_to_transfer = $rule->getPointsAmount () * ($qty / $rule->getPointsQtyStep ());

                if ($rule->getPointsMaxQty () > 0) {
                    if ($points_to_transfer > $rule->getPointsMaxQty ()) {
                        $points_to_transfer = $rule->getPointsMaxQty ();
                    }
                }

                $discount = $rule->getPointsDiscountAmount () * ($points_to_transfer / $rule->getPointsAmount ());
            } else {
                // whatever the Points Action is set to is invalid
                // - this means no transfer of points
                $discount = 0;
            }

            return $discount;
        }

        return 0;
    }

    /**
     * Accumulates the quantity of all items out of a list that are associated with a given rule.
     *
     * @param   array(Mage_Sales_Model_Quote_Item)  $order_items    : list of items to look in
     * @param   int                                 $required_id    : ID of the rule with which to filter
     * @return  int                                                 : the total quantity of all associated items
     */
    public function getTotalAssociatedItemQty($order_items, $required_id) {
        $qty = 0;

        foreach ( $order_items as $item ) {
            // look up item rule ids
            $item_rule_ids = explode ( ',', $item->getAppliedRuleIds () );
            $item_rule_ids = array_unique ( $item_rule_ids );

            // TODO Sweet Tooth - change this inner loop into an array_search
            foreach ( $item_rule_ids as $item_rule_id ) {
                // instantiate an item rule and dump its data
                $item_rule = $this->getSalesRule ( $item_rule_id );

                if ($item_rule->getId () == $required_id) {
                    // add this associated item's quantity to the running total
                    if ($item->getOrderId ()) {
                        $qty += $item->getQtyOrdered ();
                    } else if ($item->getQuoteId ()) {
                        $qty += $item->getQty ();
                    }
                    break;
                }
            }
        }

        return $qty;
    }

    /**
     * Accumulates the price of all items out of a list that are associated with a given rule.
     *
     * @nelkaake Wednesday May 5, 2010: This should be moved to some other helper, not Transfer helper.
     * @nelkaake Added on Friday June 11, 2010: Added use_salesrule parameter
     * @param   array(Mage_Sales_Model_Quote_Item)  $order_items    : list of items to look in. Could be array or an object that implements an itteratable interface
     * @param   int                                 $required_id    : ID of the rule with which to filter
     * @param   TBT_Rewards_Model_Salesrule_Rule    [$use_salesrule=null]   : salesrule if this is a salesrule check
     * @param  $prediction_mode                                 : if enabled will add prices even though they may not be applied to the items
     * @return  float                                               : the total price of all associated items
     */
    public function getTotalAssociatedItemPrice($order_items, $required_id, $use_salesrule = null, $prediction_mode = false) {
        $price = 0;

        // Get the store configuration
        $prices_include_tax = Mage::helper ( 'tax' )->priceIncludesTax ();

        foreach ( $order_items as $item ) {
            if ($this->_skipItemSumCalc($item)) {
                continue;
            }

            //@nelkaake Added on Friday June 11, 2010:
            if ($use_salesrule != null) {
                if (! Mage::getSingleton ( 'rewards/salesrule_validator' )->itemHasAppliedRid ( $item->getId (), $required_id )) {
                    continue;
                }
            }
            // look up item rule ids
            $item_rule_ids = explode ( ',', $item->getAppliedRuleIds () );
            $item_rule_ids = $prediction_mode ? array ($required_id ) : $item_rule_ids;
            $item_rule_ids = array_unique ( $item_rule_ids );

            foreach ( $item_rule_ids as $item_rule_id ) {
                // instantiate an item rule and dump its data
                $item_rule = $this->getSalesRule ( $item_rule_id );

                if ($item_rule->getId () == $required_id) {
                    if (Mage::helper ( 'rewards/config' )->calcCartPointsAfterDiscount ()) {
                        if (Mage::helper('rewards')->getIsAdmin() && Mage::app()->getRequest()->isAjax()) {
                            // we don't use row_total_after_redemptions on the item because, for ajax calls in admin
                            // this might not be fresh at this point (ST-2761)
                            $row_total = $prices_include_tax
                                ? $this->_getRedeemer()->getRowTotalAfterRedemptionsInclTax($item)
                                : $this->_getRedeemer()->getRowTotalAfterRedemptions($item);
                        } else {
                            $row_total = $prices_include_tax
                                ? $item->getRowTotalAfterRedemptionsInclTax()
                                : $item->getRowTotalAfterRedemptions();
                        }

                        // add this associated item's quantity-price to the running total
                        $price += Mage::helper('rewards/price')->getReversedCurrencyPrice($row_total);
                    } else {
                        // add this associated item's quantity-price to the running total
                        if ($prices_include_tax) {
                            $rowTotal = $item->getRowTotalBeforeRedemptionsInclTax();
                            $price += Mage::helper('rewards/price')->getReversedCurrencyPrice($rowTotal ? $rowTotal : $item->getBaseRowTotalInclTax());
                        } else {
                            $rowTotal = $item->getRowTotalBeforeRedemptions();
                            $price += Mage::helper('rewards/price')->getReversedCurrencyPrice($rowTotal ? $rowTotal : $item->getBaseRowTotal());
                        }
                    }
                    break;
                }
            }
        }

        if ($price < 0.00001 && $price > - 0.00001) {
            $price = 0;
        }
        return $price;
    }

    /**
     * Accumulates the discount of all items out of a list that are associated with a given rule.
     *
     * @param   array(Mage_Sales_Model_Quote_Item)  $order_items    : list of items to look in. Could be array or an object that implements an itteratable interface
     * @param   int                                 $required_id    : ID of the rule with which to filter
     * @return  float                                               : the total discount of all associated items
     */
    public function getTotalAssociatedItemDiscount($order_items, $required_id) {
        $discount = 0;

        foreach ( $order_items as $item ) {
            if ($this->_skipItemSumCalc($item)) {
                continue;
            }

            // look up item rule ids
            $item_rule_ids = explode ( ',', $item->getAppliedRuleIds () );
            $item_rule_ids = array_unique ( $item_rule_ids );

            foreach ( $item_rule_ids as $item_rule_id ) {
                if ($item_rule_id == $required_id) {
                    $discountTaxCompensation = $this->_getDiscountTaxCompensation($item);

                    //add support for Global-e order creation
                    if($item->getDiscountAmount() != $item->getBaseDiscountAmount()){
                        $DiscountAmount = $item->getBaseDiscountAmount();
                    }
                    else{
                        $DiscountAmount = $item->getDiscountAmount();
                    }
                    // add this associated item's discount to the total discount amount
                    $discount += Mage::helper('rewards/price')->getReversedCurrencyPrice($DiscountAmount + $discountTaxCompensation);

                    break;
                }
            }
        }

        return $discount;
    }

    /**
     * Get Discount Tax Compensation based on admin tax configs
     * @param Mage_Sales_Model_Quote_Address_Item $item
     * @return float
     */
    protected function _getDiscountTaxCompensation($item)
    {
        $pricesIncludeTax = Mage::helper ( 'tax' )->priceIncludesTax ();
        $discountIncludeTax = Mage::helper ( 'tax' )->discountTax();

        $value = 0;

        if (!$pricesIncludeTax && $discountIncludeTax) {
            $discountExclTax = $item->getDiscountAmount() / (1 + ($item->getTaxPercent() / 100));
            $discountTaxCompensation = round(abs($item->getDiscountAmount()) - abs($discountExclTax),4);
            $value = (-1) * $discountTaxCompensation;
        }

        if ($pricesIncludeTax && !$discountIncludeTax) {
            $discountTaxCompensation = round(abs($item->getDiscountAmount()) * $item->getTaxPercent() / 100,4);
            $value = $discountTaxCompensation;
        }

        return $value;
    }

    /**
     *
     * @param Mage_Sales_Model_Quote_Address_Item $item
     */
    protected function _skipItemSumCalc($item) {
        if($item->getParentItem () ) {
            if(($item->getParentItem()->getProductType() != 'bundle')) {
                return true;
            } elseif (Mage::getStoreConfig('rewards/general/apply_rules_to_parent')) {
                return true;
            }
        }
        return false;
    }



    /**
     * Accumulates the profit of all items out of a list that are associated with a given rule.
     *
     * @param   array(Mage_Sales_Model_Quote_Item)  $order_items    : list of items to look in
     * @param   int                                 $required_id    : ID of the rule with which to filter
     * @return  float                                               : the total profit of all associated items
     */
    public function getTotalAssociatedItemProfit($order_items, $required_id) {
        $profit = 0;

        foreach ( $order_items as $item ) {
            // look up item rule ids
            $item_rule_ids = explode ( ',', $item->getAppliedRuleIds () );
            $item_rule_ids = array_unique ( $item_rule_ids );

            foreach ( $item_rule_ids as $item_rule_id ) {
                // instantiate an item rule and dump its data
                $item_rule = $this->getSalesRule ( $item_rule_id );

                if ($item_rule->getId () == $required_id) {
                    // add this associated item's quantity-price to the running total
                    $profit += $item->getPrice () - $item->getCost ();
                    break;
                }
            }
        }

        return $profit;
    }

    /**
     * Fetches a cached shopping cart rule model
     *
     * @param integer $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    public function &getSalesRule($rule_id) {
        return Mage::helper ( 'rewards/rule' )->getSalesRule ( $rule_id );
    }

    /**
     * Fetches a cached catalog rule model
     *
     * @param integer $rule_id
     * @return TBT_Rewards_Model_Catalogrule_Rule
     */
    public function &getCatalogRule($rule_id) {
        return Mage::helper ( 'rewards/rule' )->getCatalogRule ( $rule_id );
    }

    protected function _getRedeemer()
    {
        return Mage::getSingleton('rewards/redeem');
    }

}
