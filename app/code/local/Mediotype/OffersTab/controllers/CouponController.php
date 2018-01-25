<?php

/**
 * Class CouponController
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class CouponController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Responsible for reading the parameter `coupon_code` and storing it in the session,
	 * and deciding where to redirect to afterwards.
	 */
	public function applyAction()
	{
		// Ensure that the request has a `coupon_code` parameter
		// If it does
			// Store it in the session
			// Notify the user with an addSuccess (?)
			// If `url` parameter
				// Redirect

			// Else
				// Do nothing (?)
		
		// Else
			// Quietly do nothing?
	}
}
