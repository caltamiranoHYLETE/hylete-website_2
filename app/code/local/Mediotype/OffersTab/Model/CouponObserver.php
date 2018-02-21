<?php

/**
 * Class CouponObserver
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OfferStab_Model_CouponObserver
{
    /**
     * Delegates.
     *
     * @param $observer
     */
    public function cartProductAddAfter($observer)
    {
        $this->attemptAutomaticCouponApplication($observer);
    }

    /**
     * Delegates.
     *
     * @param $observer
     */
    public function cartProductUpdateAfter($observer)
    {
        $this->attemptAutomaticCouponApplication($observer);
    }

    /**
     * Responsible for applying automatically set coupons.
     *
     * @param $observer
     * @return $this
     */
    protected function attemptAutomaticCouponApplication($observer)
    {
        $checkoutSession = Mage::getSingleton("checkout/session");
        $couponCode = $checkoutSession->getData("automaticCouponCode");

        // If session contains 'automatic_coupon_code'
        if ($couponCode) {
            // Apply the code
            $checkoutSession->getQuote()->setCouponCode($couponCode)->collectTotals()->save();

            // Refresh the totals so that the appliedRuleId is available in the removal block below
//            $checkoutSession->getQuote()->collectTotals()->save();

            // See if the coupon actually applied to the quote
            $appliedRuleIds = $checkoutSession->getQuote()->getAppliedRuleIds();
            $appliedRuleIds = explode(',', $appliedRuleIds);
            $rules = Mage::getModel('salesrule/rule')->getCollection()->addFieldToFilter('rule_id', array('in' => $appliedRuleIds));

            foreach ($rules as $rule) {
                if ($rule->getCode() == $couponCode) {
                    // Remove the code from the session
                    $checkoutSession->unsetData("automaticCouponCode");
                }
            }
        }

        return $this;
    }

    /**
     * Responsible for reverting the customPrice for a quote item to the original price if a coupn that was
     * targeting the MSRP is removed from the cart.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function revertPricesIfNecessary(Varien_Event_Observer $observer)
    {
        /* @var Mage_Core_Controller_Front_Action $controller */
        $controller = $observer->getControllerAction();
        if ($controller->getRequest()->getParam('remove') == 1) {
            $couponCode = $controller->getRequest()->getParam('coupon_code');

            if ($couponCode) {
                // Lookup coupon by code
                $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
                $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

                // Check coupon's price selector
                $priceSelectorValue = $rule->getPriceSelector();

                // If MSRP is target
                if ($priceSelectorValue == "4") {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();

                    // For each quote item, see if it has applied rule ids
                    foreach ($quote->getAllItems() as $item) {
                        if ($item->getAppliedRuleIds() == '') {
                            continue;
                        }

                        // If one of the applied rule ids matches our current rule id, then then revert custom pricing
                        foreach (explode(",", $item->getAppliedRuleIds()) as $ruleId) {
                            if ($ruleId == $rule->getId()) {
                                $item->setCustomPrice(null);
                                $item->setPrice($item->getProduct()->getPrice());
                            }
                        }
                    }
                }
            }
        }

        // Myles: Don't need to do anything else; once this observer is done, there will be a collectTotals(…) call

        return $this;
    }

    /**
     * Responsible for catching a custom dispatched event in the middle of the Cmind Observer
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function cmindRevertPricesIfNecessary(Varien_Event_Observer $observer)
    {
        $quote = $observer->getQuote();
        $oldCouponCode = $observer['couponCodeRemoved'];

        if ($oldCouponCode) {
            // Lookup coupon by code
            $coupon = Mage::getModel('salesrule/coupon')->load($oldCouponCode, 'code');
            $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

            // Check coupon's price selector
            $priceSelectorValue = $rule->getPriceSelector();

            // If MSRP is target
            if ($priceSelectorValue == "4") {
                // For each quote item, see if it has applied rule ids
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getAppliedRuleIds() == '') {
                        continue;
                    }

                    // If one of the applied rule ids matches our current rule id, then then revert custom pricing
                    foreach (explode(",", $item->getAppliedRuleIds()) as $ruleId) {
                        if ($ruleId == $rule->getId()) {
                            $item->setCustomPrice(null);
                            $item->setPrice($item->getProduct()->getPrice());
                        }
                    }
                }
            }
        }

        // Myles: Don't need to do anything else; once this observer is done, there will be a collectTotals(…) call

        return $this;
    }
}
