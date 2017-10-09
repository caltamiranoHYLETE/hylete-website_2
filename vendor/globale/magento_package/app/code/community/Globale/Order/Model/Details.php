<?php

/**
 * Class Globale_Order_Model_Details
 */
class Globale_Order_Model_Details extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('globale_order/details'); // this is location of the resource file.
    }

    /**
     * Save Order details in DB, in globale_order_details
     * @param Mage_Sales_Model_Order $Order
     * @param $Request
     * @param $IncrementId
     */
    public function saveOrderDetails(Mage_Sales_Model_Order $Order, $Request, $IncrementId) {

        $this->setOrderId($IncrementId);
        $this->setUserId($Request->UserId);
        $this->setQuoteId($Order->getQuoteId());
        $this->setAllowMailsFromMerchant($Request->AllowMailsFromMerchant);
        $this->setCartHash($Request->CartHash);
        $this->setClearCart($Request->ClearCart);
        $this->setBaseCurrencyCode($Request->CurrencyCode); // name changed in 1.1.0
        $this->setCustomerComments($Request->CustomerComments);
        $this->setDoNotChargeVat($Request->DoNotChargeVAT);
        $this->setEmailAddress($Request->Customer->EmailAddress);
        $this->setIsFreeShipping($Request->IsFreeShipping);
        $this->setFreeShippingCouponCode($Request->FreeShippingCouponCode);
        $this->setIsEndCustomerPrimary($Request->Customer->IsEndCustomerPrimary);
        $this->setIsSplitOrder($Request->IsSplitOrder);
        $this->setRoundingRate($Request->RoundingRate);
        $this->setSameDayDispatch($Request->SameDayDispatch);
        $this->setSameDayDispatchCost($Request->SameDayDispatchCost);
        $this->setSendConfirmation($Request->Customer->SendConfirmation);
        $this->setShipToStoreCode($Request->ShipToStoreCode);
        $this->setTotalPrice($Request->InternationalDetails->TotalPrice);
        $this->setTransactionTotalPrice($Request->InternationalDetails->TransactionTotalPrice);
        $this->setTransactionCurrencyCode($Request->InternationalDetails->TransactionCurrencyCode);
        $this->setUrlParameters($Request->UrlParameters);
        // added on 1.1.0
        $this->setLoyaltyPointsSpent($Request->LoyaltyPointsSpent);
        $this->setLoyaltyPointsEarned($Request->LoyaltyPointsEarned);
        $this->setLoyaltyCode($Request->LoyaltyCode);
        $this->setFreeShippingCouponCode($Request->FreeShippingCouponCode);
        $this->setOriginalMerchantTotalProductsDiscountedPrice($Request->OriginalMerchantTotalProductsDiscountedPrice);
        $this->setOtVoucherCode($Request->OTVoucherCode);
        $this->setOtVoucherAmount($Request->OTVoucherAmount);
        $this->setOtVoucherCurrencyCode($Request->OTVoucherCurrencyCode);
        $this->setStatusCode($Request->StatusCode);
        $this->setPriceCoefficientRate($Request->PriceCoefficientRate);
        $this->setWebStoreCode($Request->WebStoreCode);
        $this->setDiscountedShippingPrice($Request->DiscountedShippingPrice);
        $this->setCustomerCurrencyCode($Request->InternationalDetails->CurrencyCode);
        $this->setSameDayDispatchCost($Request->InternationalDetails->SameDayDispatchCost);
        $this->setTotalCcfPrice($Request->InternationalDetails->TotalCCFPrice);
        $this->setDutiesGuaranteed($Request->InternationalDetails->DutiesGuaranteed);
        $this->setDeliveryDaysFrom($Request->InternationalDetails->DeliveryDaysFrom);
        $this->setDeliveryDaysTo($Request->InternationalDetails->DeliveryDaysTo);
        $this->setConsignmentFee($Request->InternationalDetails->ConsignmentFee);
        $this->setSizeOverchargeValue($Request->InternationalDetails->SizeOverchargeValue);
        $this->setRemoteAreaSurcharge($Request->InternationalDetails->RemoteAreaSurcharge);
        $this->setTotalDutiesPrice($Request->InternationalDetails->TotalDutiesPrice);

        $this->save();
    }

}