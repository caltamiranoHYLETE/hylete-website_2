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
			$checkoutSession->getQuote()->setCouponCode($couponCode)->save();

			// Refresh the totals so that the appliedRuleId is available in the removal block below
			$checkoutSession->getQuote()->collectTotals()->save();

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
}
