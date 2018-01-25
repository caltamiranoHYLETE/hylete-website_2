<?php

/**
 * Class Mediotype_OffersTab_CouponController
 *
 * @author Myles Forrest <myles@mediotype.com>
 */
class Mediotype_OffersTab_CouponController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Responsible for reading the parameter `coupon_code` and storing it in the session,
	 * and deciding where to redirect to afterwards.
	 */
	public function applyAction()
	{
		// Ensure that the request has a `couponCode` parameter
		$couponCode = $this->getRequest()->getParam('couponCode'); // MYLES: Validate the coupon code?
		$redirectUrl = $this->getRequest()->getParam('redirectUrl');

		// If it does
		if ($couponCode) {
			// Store it in the session
			$checkoutSession = Mage::getSingleton("checkout/session");
			$checkoutSession->setData("automaticCouponCode", $couponCode);

			// Notify the user with an addSuccess
			$coreSession = Mage::getSingleton('core/session');
			$coreSession->addSuccess("Coupon `" . $couponCode ."` was added to cart and will automatically apply to your cart during checkout.");

			// If `url` parameter, redirect
			if ($redirectUrl) {
				$this->_redirect($redirectUrl);
			}

		} else {
			// Do nothing (?)
		}

		// …
	}
}
