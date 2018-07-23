<?php

use GlobalE\SDK\API\Common;

class Globale_Base_Model_Cart extends Mage_Core_Model_Abstract {


	const DISCOUNT_TYPE_CART            = 1;

	/**
	 * Build SDK Address object and fill it with magento Address data
	 * @param Mage_Customer_Model_Customer $Customer
	 * @param Mage_Customer_Model_Address $AddressDetails
	 * @param boolean - true if shipping address
	 * @return Common\Address
	 */
	public function getAddress( Mage_Customer_Model_Customer $Customer, Mage_Customer_Model_Address $AddressDetails) {

		// Global-e SDK Request object
		$Address = new Common\Address();

		if($Customer->getId()){

			// fill up address information
			$Address->setUserId($Customer->getId());
			$Address->setFirstName($AddressDetails->getData('firstname'));
			$Address->setMiddleName($AddressDetails->getData('middlename'));
			$Address->setLastName($AddressDetails->getData('lastname'));
			$Address->setCompany($AddressDetails->getData('company'));
			$Address->setEmail($Customer->getData('email'));
			$Address->setMiddleName($AddressDetails->getData('middlename'));
			$Address->setPhone1($AddressDetails->getData('telephone'));
			$Address->setPhone2($AddressDetails->getPhone2());
			$Address->setFax($AddressDetails->getData('fax'));
			// get street from address
			$Street = $AddressDetails->getStreet();
			if(!empty($Street)){
				if(!empty($Street[0])) {
					$Address->setAddress1($Street[0]);
				}
				if(!empty($Street[1])) {
					$Address->setAddress2($Street[1]);
				}
			}
			$Address->setCity($AddressDetails->getData('city'));
			$Address->setStateOrProvince(null);
			$Address->setZip($AddressDetails->getData('postcode'));
			$Address->setCountryCode($AddressDetails->getCountryId());
			if($AddressDetails->getRegionId()) {
				/** @var Mage_Directory_Model_Region $Region */
				$Region = Mage::getModel('directory/region')->load($AddressDetails->getRegionId());
				$Address->setStateOrProvince($Region->getName());
				$Address->setStateCode($Region->getCode());
			}
		}
		return $Address;
	}



	/**
	 * Get All cart discounts - ( Exclude VATRate calculation ! )
	 * @param Mage_Sales_Model_Quote $Quote
	 * @return Common\Discount | null
	 */
	public function getDiscount(Mage_Sales_Model_Quote $Quote) {

		$Discount = null;
		// Subtotal
		$Subtotal = $Quote->getSubtotal();
		$SubtotalWithDiscount = $Quote->getSubtotalWithDiscount();

		// Base Subtotal
		$BaseSubtotal = $Quote->getBaseSubtotal();
		$BaseSubtotalWithDiscount = $Quote->getBaseSubtotalWithDiscount();

		if($Subtotal > $SubtotalWithDiscount){

			// calculate discount amount
			$DiscountAmount = ($Subtotal - $SubtotalWithDiscount);
			$BaseDiscountAmount = ($BaseSubtotal - $BaseSubtotalWithDiscount);

			// Build discount information
			$Discount = new Common\Discount();
			$Discount->setDiscountValue($DiscountAmount);
			$Discount->setOriginalDiscountValue($BaseDiscountAmount);
			$Discount->setDiscountType(self::DISCOUNT_TYPE_CART);

			// Get coupon by coupon code
			$CouponCode = $Quote->getCouponCode();

			if($CouponCode){
				/** @var Mage_SalesRule_Model_Coupon $Rule */
				$Rule = Mage::getModel('salesrule/coupon')->load($CouponCode, 'code');

				if($Rule->getRuleId()){
					$Discount->setDiscountCode($Rule->getRuleId());
				}
				/** @var Mage_SalesRule_Model_Rule $Coupon */
				$Coupon = Mage::getModel('salesrule/rule')->load($Rule->getRuleId());

				$Discount->setName($Coupon->getName());
				$Discount->setCouponCode($Coupon->getCouponCode());
				$Discount->setDescription($Coupon->getDescription());

			}else{
				$Discount->setName('Cart Discount');
			}

		}

		return $Discount;
	}


	/**
	 * Check for free shipping in coupon, or from promotions rules.
	 * In the case of free shipping return discount code or the promotion rule name with the rule id, otherwise null
	 * @param Mage_Sales_Model_Quote $Quote
	 * @return null|string
	 */
	public function getFreeShippingDiscountRuleCode(Mage_Sales_Model_Quote $Quote) {

		$FreeShippingDiscountRuleCode = null;

		//TODO This is an Override to stop free hsipping on international orders
		return $FreeShippingDiscountRuleCode;


		/** @var Mage_SalesRule_Model_Coupon $Rule */
		$Rule = Mage::getModel('salesrule/coupon')->load($Quote->getCouponCode(), 'code');
		/** @var Mage_SalesRule_Model_Rule $Coupon */
		$Coupon = Mage::getModel('salesrule/rule')->load($Rule->getRuleId());

		// Check free shipping on the cart coupon
		if($Quote->getCouponCode() && $Coupon->getSimpleFreeShipping() != 0) {
			$FreeShippingDiscountRuleCode = sprintf("%s", $Quote->getCouponCode());
		}else{
			// check if any applied promotions of free shipping exists on cart
			$CartAppliedRuleIds = $Quote->getAppliedRuleIds();
			foreach (explode(',', $CartAppliedRuleIds) as $RuleId){

				// Check Free shipping on any rule on the cart
				/** @var Mage_SalesRule_Model_Rule $RuleDetails */
				$RuleDetails = Mage::getModel('salesrule/rule')->load($RuleId, 'rule_id');
				if($RuleDetails->getSimpleFreeShipping() != 0){
					$FreeShippingDiscountRuleCode = sprintf("%s (%d)", $RuleDetails->getName(), $RuleDetails->getId());
				}
			}
		}
		return $FreeShippingDiscountRuleCode;
	}

    /**
     * Get Shipping Option details
     * @return Common\ShippingOption
     */
    public function getShippingOption() {

        $ShippingOption = new Common\ShippingOption();
        // Get all active carriers in magento
        $Methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        // Set detfault carrier code for search
        $CarrierName = $CarrierCode = 'globale';

        // Set shipping option
        $ShippingOption->setCarrier($CarrierCode);
        $ShippingOption->setCarrierTitle($CarrierName);
        $ShippingOption->setCarrierName($CarrierName);
        $ShippingOption->setCode($CarrierCode . '_standard');
        $ShippingOption->setMethod('standard');
        $ShippingOption->setMethodTitle('Standard');
        $ShippingOption->setMethodDescription('');
        $ShippingOption->setPrice(0);

        // Find carrier code in all methods
        if(array_key_exists($CarrierCode,$Methods)){
            $ConfigCarrierName = Mage::getStoreConfig('carriers/'.$CarrierCode.'/title');
            if(!empty($ConfigCarrierName)) {
                $CarrierName = $ConfigCarrierName;
            }
            $ShippingOption->setCarrierTitle($CarrierName);
            $ShippingOption->setCarrierName($CarrierName);
        }
        return $ShippingOption;
    }


}