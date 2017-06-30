<?php
class Hylete_Coupons_Model_Create_Api extends Mage_Api_Model_Resource_Abstract
{
	public function clonecoupon($couponID, $couponCode)
	{
		$masterCoupon = Mage::getModel('salesrule/rule')->load($couponID);
		if (!$masterCoupon || ! $masterCoupon->getId())
        {
            throw new Mage_Api_Exception('master_coupon_error');
        }

		try
        {
            $rule = Mage::getModel('salesrule/rule');
			$coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
			if($coupon->getId()) {
				//the coupon exists so we delete it
				 $coupon->delete();
			}

			//Let's build our rule
			$rule = $masterCoupon;

			$rule->setId(null);
            $rule->setUsagePerCustomer(1);
            $rule->setCode($couponCode);

			$couponNameTemplate = $couponCode;

			if (empty($couponNameTemplate))
			{
				$rule->setName($coupon->getCode());
			}
			else
			{
				$rule->setName(sprintf($couponNameTemplate, $coupon->getCode()));
			}

			$date = new DateTime();
			$date->modify('+40 day');
			$rule->setToDate($date->format('Y-m-d'));

			//this creates the new code. Hide it when not in use.
			$rule->save();


			//now we have a rule let's create the coupon
			$coupon->setId(null);
			$coupon->setRuleId($rule->getRuleID());
			$coupon->setUsageLimit(1);
            $coupon->setUsagePerCustomer(1);
            $coupon->setCode($couponCode);

			$date = new DateTime();
			$date->modify('+65 day');
			$coupon->setToDate($date->format('Y-m-d'));

			$coupon->setIsPrimary(1);
			$coupon->save();

			return $couponCode;

        }
        catch (Exception $e)
        {
			throw new Mage_Api_Exception('coupon_exists');
		}
	}

	public function clonecouponwithname($couponID, $couponCode, $couponName)
	{
		$masterCoupon = Mage::getModel('salesrule/rule')->load($couponID);
		if (!$masterCoupon || ! $masterCoupon->getId())
		{
			throw new Mage_Api_Exception('master_coupon_error');
		}

		try
		{
			$rule = Mage::getModel('salesrule/rule');
			$coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
			if($coupon->getId()) {
				//the coupon exists so we delete it
				$coupon->delete();
			}

			//Let's build our rule
			$rule = $masterCoupon;

			$rule->setId(null);
			$rule->setUsagePerCustomer(1);
			$rule->setCode($couponCode);
			$rule->setName($couponName);

			$date = new DateTime();
			$date->modify('+90 day');
			$rule->setToDate($date->format('Y-m-d'));

			//this creates the new code. Hide it when not in use.
			$rule->save();


			//now we have a rule let's create the coupon
			$coupon->setId(null);
			$coupon->setRuleId($rule->getRuleID());
			$coupon->setUsageLimit(1);
			$coupon->setUsagePerCustomer(1);
			$coupon->setCode($couponCode);

			$date = new DateTime();
			$date->modify('+90 day');
			$coupon->setToDate($date->format('Y-m-d'));

			$coupon->setIsPrimary(1);
			$coupon->save();

			return $couponCode;

		}
		catch (Exception $e)
		{
			throw new Mage_Api_Exception('coupon_exists');
		}
	}

	public function getcoupon($couponID)
	{
		$strRet = "";
		$masterCoupon = Mage::getModel('salesrule/rule')->load($couponID);
		if (!$masterCoupon || ! $masterCoupon->getId())
        {
            throw new Mage_Api_Exception('master_coupon_error');
		} else {
			if($masterCoupon->getName() != "") {
				$strRet = $masterCoupon->getName();
			}	
		}
		
		return $strRet;
	}

	public function doescouponexist($couponCode)
	{
		$bReturn = false;
		$coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
		if($coupon->getId()) {
			$bReturn = true;
		}
		
		return $bReturn;
	}

	public function savecustomreferral($customerID, $referral) {

		$customer = Mage::getModel('customer/customer')->load($customerID);

		if (!$customer->getReferralName()) {
			$customer->setReferralName($referral);
			$customer->save();

			return $customerID;
		} else {
			return "No Update.";
		}
	}
	
	public function getallcustomerbalance()
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $query = 'SELECT * FROM ' . $resource->getTableName('enterprise_customerbalance/balance');
	    $results = $readConnection->fetchAll($query);
	
	    return($results);
	}

}