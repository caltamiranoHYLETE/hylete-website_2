<?php

class Ebizmarts_BakerlooLoyalty_Model_SweetTooth extends Ebizmarts_BakerlooLoyalty_Model_Abstract {

    public function init() {
        $reward = Mage::getModel('rewards/customer')->getRewardsCustomer($this->getCustomer());

        $this->_reward = $reward;
    }

    public function isEnabled() {
        $posConfig = ($this->getLoyaltyConfig() == 'TBT_Rewards');
        $active    = Mage::getStoreConfig('rewards/platform/is_connected');

        return $posConfig && $active;

    }

    public function rewardCustomer($customer, $points) {
        try {

            //Code taken from http://help.sweettoothrewards.com/article/259-create-a-points-transfer-from-code
            //and slightly modified.

            $customerId = $customer->getId();

            //load in transfer model
            $transfer = Mage::getModel('rewards/transfer');
            //Load it up with information
            $transfer->setId(null)
                ->setCurrencyId("1") // in versions of sweet tooth 1.0-1.2 this should be set to "1"
                ->setQuantity($points) // number of points to transfer. This number can be negative or positive, but not zero
                ->setCustomerId($customerId) // the id of the customer that these points will be going out to
                ->setComments("POS points transfer."); //This is optional
            //Checks to make sure you can actually move the transfer into the new status
            if($transfer->setStatus(null, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED)) { // STATUS_APPROVED would transfer the points in the approved status to the customer
                $transfer->save(); //Save everything and execute the transfer
            }

        }catch(Exception $e){
            Mage::logException($e);

            return false;
        }

        return true;
    }

    public function getPointsBalance() {
        /*
        TODO:
        the only caution I would give you is accounting for anyone who may have added extra functionality to Sweet Tooth by extending the support for multiple currencies.
        We've seen a couple of clients who have implemented multi currency support in Sweet Tooth. We do have a currency model (TBT_Rewards_Model_Currency) which gives you
        a list of available currencies, but in most cases that will only return "1". hope this helps Pablo! */

        $usable  = $this->_reward->getUsablePoints();
        $balance = 0;

        if(is_array($usable))
            $balance = current($usable);

        return $balance;
    }

    public function getMinumumToRedeem() {
        return 0;
    }

    public function getCurrencyAmount() {
        return "";
    }

    public function getYouWillEarnPoints(Mage_Sales_Model_Quote $cart) {
        $rewardsSession = Mage::getSingleton('rewards/session');

        $points_earning = $rewardsSession->getTotalPointsEarnedOnCart($cart);
        $points_string  = Mage::getModel ( 'rewards/points' )->set ( $points_earning )->getRendering ()->setDisplayAsList ( true )->toHtml();


        $cartPoints = array();

        $cartPoints['items']                      = array();
        $cartPoints['total_points_earned']        = (int)current($points_earning);
        $cartPoints['total_points_earned_string'] = strip_tags($points_string);

        foreach ( $cart->getAllItems () as $item ) {
            if ($item->getParentItem())
                continue;

            $points_to_earn = Mage::helper ( 'rewards' )->unhashIt ( $item->getEarnedPointsHash () );
            //Mage::helper ( 'rewards/transfer' )->getEarnedPointsOnItem ( $item )

            if(empty($points_to_earn))
                continue;

            $pointsToEarn = (int)$points_to_earn[0]->points_amt;
            $asString     = strip_tags((string)Mage::getModel ( 'rewards/points' )->set ( array (1 => $pointsToEarn) ));

            $cartPoints ['items'][] = array (
                'sku'                        => $item->getSku(),
                'total_points_earned'        => $pointsToEarn,
                'total_points_earned_string' => $asString,
            );
        }

        return $cartPoints;
    }

    public function productRedeemOptions($customer, $product) {
        Mage::getSingleton('rewards/session')->setCustomer($customer);

        $_product    = TBT_Rewards_Model_Catalog_Product::wrap($product);
        $ruleOptions = $_product->getCatalogRedemptionRules($customer);

        if(is_array($ruleOptions) and !empty($ruleOptions)) {
            $rulesCount = count($ruleOptions);

            $rule = Mage::getModel ('rewards/catalogrule_rule');

            for($i=0; $i < $rulesCount; $i++) {
                $rule->load($ruleOptions[$i]->rule_id);
                $ruleData = $rule->getData();

                if(isset($ruleData['conditions_serialized'])) unset($ruleData['conditions_serialized']);
                if(isset($ruleData['actions_serialized'])) unset($ruleData['actions_serialized']);
                if(isset($ruleData['customer_group_ids'])) unset($ruleData['customer_group_ids']);
                if(isset($ruleData['website_ids'])) unset($ruleData['website_ids']);
                if(isset($ruleOptions[$i]->applicable_qty)) unset($ruleOptions[$i]->applicable_qty);


                $ruleOptions[$i]->name = $rule->getName(); //$ruleData;
                $ruleOptions[$i]->points_max_uses = (int)$rule->getPointsUsesPerProduct();
                $ruleOptions[$i]->points_max_qty = (int)$rule->getPointsMaxQty();
                $ruleOptions[$i]->points_max_percentage = (int)$rule->getPointsMaxRedeemPercentagePrice();
//                $ruleOptions[$i]->data = $ruleData;

                $rule->unsetData();
                $rule->unsetOldData();
                $ruleData = null;

            }
        }

        return $ruleOptions;

    }

    public function cartRedeemOptions($quote) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $options = array();

        $cartRules = Mage::getSingleton('rewards/session')->collectShoppingCartRedemptions($quote);
//        Mage::log($cartRules, null, 'cartrules.log', true);

        $model = Mage::getModel('rewards/salesrule_rule');

        foreach($cartRules['applicable'] as $id => $ruleData) {
            $model->load($id);

            $data = array(
                "points_amt" => abs($ruleData['amount']),
                "points_currency_id" => (int)$ruleData['currency'],
                "rule_id" => (int)$ruleData['rule_id'],
                "rule_name" => $ruleData['rule_name'],
                "points_max_uses" => 0,
                "points_max_qty" => (int)$model->getPointsMaxQty(),
                "points_max_percentage" => 0
            );

            if(isset($ruleData['caption']) and !empty($ruleData['caption']))
                $data['legend'] = $ruleData['caption'];
            else
                $data['legend'] = strip_tags(Mage::helper('rewards')->__('Spend <b>%s</b>, Get <b>%s</b>', $ruleData['points_cost'], $ruleData['action_str']));

            $options[] = $data;
        }

        /* dbps rules are included in the applied array with amount 0 if they haven't been applied yet */
        foreach($cartRules['applied'] as $id => $ruleData) {
            if(!$ruleData['is_dbps'])
                continue;

            $model->load($id);

            $data = array(
                "points_amt" =>  (int)$model->getPointsAmount(), //abs($ruleData['amount']),
                "points_currency_id" => (int)$ruleData['currency'],
                "rule_id" => (int)$ruleData['rule_id'],
                "rule_name" => $ruleData['rule_name'],
                "points_max_uses" => 0,
                "points_max_qty" => (int)$model->getPointsMaxQty(),
                "points_max_percentage" => 0
            );

            if(isset($ruleData['caption']) and !empty($ruleData['caption']))
                $data['legend'] = $ruleData['caption'];
            else {
                $actionStr = (int)$model->getPointsDiscountAmount() . "% off";
                $data['legend'] = strip_tags(Mage::helper('rewards')->__('Spend <b>%s</b>, Get <b>%s</b>', $data['points_amt'], $actionStr));
            }

            $options[] = $data;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $options;

    }

}