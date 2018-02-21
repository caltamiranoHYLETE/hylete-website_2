<?php

/**
 * Class Mediotype_HyletePrice_Model_Sales_Quote
 */

/*
 * Myles: This class is extending the TBT Quote, which in turn extends the Amasty Quote;
 */
class Mediotype_OffersTab_Model_Sales_Quote extends TBT_Rewards_Model_Sales_Quote
{
	/**
	 * When adding the code from the default form we always have the "remove" param
	 * when deleting the code we have no any params as we are using different from
	 * @return bool
	 */
	protected function _isAddNewCoupon()
	{
		$isRemove = null;
		if (Mage::app()->getStore()->isAdmin()) {
			$request = Mage::app()->getRequest()->getParam('order');
			$code = $request['coupon']['code'];
			if (!empty($code)) {
				$isRemove = 0;
			}
		}

		if (!isset($isRemove)) {
			$isRemove = Mage::app()->getRequest()->getParam('remove');
			if (!isset($isRemove) && $this->_isXcoupon()) {
				$isRemove = 0;
			}
		}

		$inAutomaticCouponObserver = false;
		// Are we in an automatic coupon observer?
		$backTrace = debug_backtrace();
		foreach ($backTrace as $step) {
			if (isset($step['object']) && ($step['object'] instanceof Mediotype_OfferStab_Model_CouponObserver)) {
				$inAutomaticCouponObserver = true;
				break;
			}
		}
		$backTrace = null;

		return ($inAutomaticCouponObserver || (isset($isRemove) && $isRemove == 0));
	}
}
