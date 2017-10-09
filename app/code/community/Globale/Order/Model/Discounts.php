<?php

/**
 * Class Globale_Order_Model_Discounts
 */
class Globale_Order_Model_Discounts extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('globale_order/discounts'); // this is location of the resource file.
    }

    /**
     * Save one discount for order in DB, in globale_order_discounts
     * @param $Discount
     * @param $IncrementId
     */
    public function saveDiscount($Discount, $IncrementId) {

            $this->setOrderId($IncrementId);
            $this->setCouponCode($Discount->CouponCode);
            $this->setDescription($Discount->Description);
            $this->setDiscountCode($Discount->DiscountCode);
            $this->setDiscountType($Discount->DiscountType);
            $this->setInternationalPrice($Discount->InternationalPrice);
            $this->setLocalVatRate($Discount->LocalVATRate);
            $this->setName($Discount->Name);
            $this->setPrice($Discount->Price);
            $this->setVatRate($Discount->VATRate);
            $this->setProductCartItemId($Discount->ProductCartItemId);
            $this->setLoyaltyVoucherCode($Discount->LoyaltyVoucherCode);

		$this->save();
    }


}