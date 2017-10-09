<?php

/**
 * Class Globale_Order_Model_Products
 */
class Globale_Order_Model_Products extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('globale_order/products'); // this is location of the resource file.
    }

    /**
     * Save one product of an Order  in DB, in globale_order_products
     * @param stdClass $Product
     * @param $IncrementId
     */
    public function saveProduct($Product, $IncrementId) {

            $this->setOrderId($IncrementId);
            $this->setBackOrderDate($Product->BackOrderDate);
            $this->setCartItemId($Product->CartItemId);
            $this->setParentCartItemId($Product->ParentCartItemId);
            $this->setCartItemOptionId($Product->CartItemOptionId);
            $this->setGiftMessage($Product->GiftMessage);
            $this->setHandlingCode($Product->HandlingCode);
            $this->setIsBackOrdered($Product->IsBackOrdered);
            $this->setInternationalPrice($Product->InternationalPrice);
            $this->setPrice($Product->Price);
            $this->setPriceBeforeRoundingRate($Product->PriceBeforeRoundingRate);
            $this->setPriceBeforeGlobaleDiscount($Product->PriceBeforeGlobalEDiscount);
            $this->setQuantity($Product->Quantity);
            $this->setRoundingRate($Product->RoundingRate);
            $this->setSku($Product->Sku);
            $this->setVatRate($Product->VATRate);
            $this->save();
    }
}