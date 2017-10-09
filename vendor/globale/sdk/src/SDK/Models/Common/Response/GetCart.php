<?php

namespace GlobalE\SDK\Models\Common\Response;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\API;


/**
 * Class GetCart
 *
 * @method getIsFreeShipping()
 * @method getUserId()
 * @method getCartHash()
 * @method getFreeShippingCouponCode()
 * @method getLoyaltyPointsTotal()
 * @method getLoyaltyPointsEarned()
 * @method getLoyaltyPointsSpent()
 * @method getShippingOptionsList()
 *
 * @method $this setIsFreeShipping($IsFreeShipping)
 * @method $this setUserId($UserId)
 * @method $this setCartHash($CartHash)
 * @method $this setFreeShippingCouponCode($FreeShippingCouponCode)
 * @method $this setLoyaltyPointsTotal($LoyaltyPointsTotal)
 * @method $this setLoyaltyPointsEarned($LoyaltyPointsEarned)
 * @method $this setLoyaltyPointsSpent($LoyaltyPointsSpent)
 * @method $this setShippingOptionsList($ShippingOptionsList)
 *
 * @package GlobalE\SDK\Models\Common\Request
 */
class GetCart extends Common  {

	/**
	 * @var Request\Product[]
	 */
	public $productsList;

	/**
	 * @var API\Common\Address
	 */
	public $shippingDetails;

	/**
	 * @var API\Common\Address
	 */
	public $billingDetails;

	/**
	 * @var API\Common\Discount[]
	 */
	public $discountsList;

	/**
	 * @var boolean
	 */
	public $IsFreeShipping;

	/**
	 * @var int
	 */
	public $UserId;

	/**
	 * @var string
	 */
	public $CartHash;

	/**
	 * @var string
	 */
	public $FreeShippingCouponCode;

    /**
     * @var int
     */
    public $LoyaltyPointsTotal;

    /**
     * @var int
     */
    public $LoyaltyPointsEarned;

    /**
     * @var int
     */
    public $LoyaltyPointsSpent;

    /**
     * Shipping Option List
     * @var API\Common\ShippingOption[]
     * @access public
     */
    public $ShippingOptionsList;


	/**
	 * @return Request\Product[]
	 */
	public function getProductsList()
	{
		return $this->productsList;
	}

	/**
	 * @param Request\Product[] $productsList
	 * @return GetCart
	 */
	public function setProductsList($productsList)
	{
		$this->productsList = $productsList;
		return $this;
	}

	/**
	 * @return API\Common\Address
	 */
	public function getShippingDetails()
	{
		return $this->shippingDetails;
	}

	/**
	 * @param API\Common\Address $shippingDetails
	 * @return GetCart
	 */
	public function setShippingDetails($shippingDetails)
	{
		$this->shippingDetails = $shippingDetails;
		return $this;
	}

	/**
	 * @return API\Common\Address
	 */
	public function getBillingDetails()
	{
		return $this->billingDetails;
	}

	/**
	 * @param API\Common\Address $billingDetails
	 * @return GetCart
	 */
	public function setBillingDetails($billingDetails)
	{
		$this->billingDetails = $billingDetails;
		return $this;
	}

	/**
	 * @return API\Common\Discount[]
	 */
	public function getDiscountsList()
	{
		return $this->discountsList;
	}

	/**
	 * @param API\Common\Discount[] $discountsList
	 * @return GetCart
	 */
	public function setDiscountsList($discountsList)
	{
		$this->discountsList = $discountsList;
		return $this;
	}


}