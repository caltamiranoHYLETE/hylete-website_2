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
	 */
	protected function attemptAutomaticCouponApplication($observer)
	{
		// If session contains 'automatic_coupon_code'
			// Apply the code
			// Remove the code from the session
			// Notify the user with an addSuccess (?)

		// Else do nothing
	}
}
