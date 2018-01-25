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
			$result = Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($couponCode)->save();

			// Remove the code from the session
			$checkoutSession->unsetData("automaticCouponCode");

			// Notify the user with an addSuccess
			//$coreSession = Mage::getSingleton('core/session');
			//$coreSession->addSuccess("Coupon `" . $couponCode . "` was successfully applied!");
		}

		return $this;
	}
}
