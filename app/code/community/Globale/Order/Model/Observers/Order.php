<?php

class Globale_Order_Model_Observers_Order {

    const CART_DISCOUNT     = 1;
    const SHIPPING_DISCOUNT = 2;
    const LOYALTY_DISCOUNT  = 3;
    const DUTIES_DISCOUNT   = 4;

    /**
     * Listens to event 'sales_order_save_after' and sends statusUpdate to core
     * @param Varien_Event_Observer $Observer
     */
    public function orderStatusUpdated(Varien_Event_Observer $Observer){

    	/**@var $Order Mage_Sales_Model_Order */
        $Order    = $Observer->getEvent()->getOrder();
        $NewState = $Order->getData();
        $OldState = $Order->getOrigData();

		//check if the call doesn't came from API
		$ApiMode = Mage::registry('globale_api');

        if(!$ApiMode && !empty($NewState['state']) && !empty($OldState['state']) && $NewState['state'] != $OldState['state']){

            /** @var Globale_Order_Model_Orders $OrderModel */
            $OrderModel = Mage::getModel('globale_order/orders');
            $OrderModel->sendUpdateStatusRequest($Order);
        }
    }

    /**
     * choose rather to create an order regularly or to use the non converted price logic
     * and remove all base prices
     * @param  Varien_Event_Observer $Observer sales_order_payment_place_end
     * @return $this
     */
    function orderTotalsUpdatedProcess(Varien_Event_Observer $Observer){

        $Request = Mage::registry('GlobaleOrderRequest');
        if(!$Request){
            return $this;
        }

        /**@var $Order Mage_Sales_Model_Order */
        $Order = $Observer->getEvent()->getPayment()->getOrder();
        $Store = Mage::getModel('core/store')->load($Order->getStoreId());

        $OrderBaseCurrency = $Request->CurrencyCode;
        $StoreCurrency = $Store->getBaseCurrencyCode();

        if($OrderBaseCurrency == $StoreCurrency){
            $this->orderTotalsUpdated($Observer, $Request);
        }
        else{
            $this->orderTotalsUpdatedNoBase($Observer, $Request);
        }

    }

    /**
     * @param Varien_Event_Observer $Observer
     * @return $this
     */
    public function orderTotalsUpdated(Varien_Event_Observer $Observer, $Request){

		/**@var $Order Mage_Sales_Model_Order */
		$Order = $Observer->getEvent()->getPayment()->getOrder();

		/**@var $OrdersModel Globale_Order_Model_Orders */
        $OrdersModel = Mage::getModel('globale_order/orders');
        $Request = $OrdersModel->reArrangeRequest($Request);

        $Order->setOrderCurrencyCode($Request->InternationalDetails->CurrencyCode);
        $Order->setBaseToOrderRate(0);

        $TotalDiscount = 0;
        $BaseTotalDiscount = 0;

        foreach ($Request->Discounts as $Discount) {
            if($Discount->DiscountType == self::CART_DISCOUNT){
                $TotalDiscount += $Discount->InternationalPrice;
                $BaseTotalDiscount += $Discount->Price;
            }
        }

        $SubTotal = 0;
        $BaseSubTotal = 0;
        $SubTotalIncl = 0;
        $BaseSubTotalIncl = 0;
        $TaxTotal = 0;
        $BaseTaxTotal = 0;

        foreach ($Order->getItemsCollection() as $OrderItem) {
			/**@var $OrderItem Mage_Sales_Model_Order_Item */
            $Id = $OrderItem->getQuoteItemId();

			// if it's child product that doesn't came from API
            if(!isset($Request->Products[$Id])){
                continue;
            }

			$Product = $Request->Products[$Id];

            $Tax = (1 + $Product->VATRate / 100);
            $OrderItem->setBasePrice($Product->Price / $Tax)
                ->setBaseTaxAmount(($Product->Price - $Product->Price / $Tax) * $Product->Quantity )
                ->setBaseOriginalPrice($OrderItem->getBaseOriginalPrice())
                ->setBaseRowTotal($Product->Price / $Tax * $Product->Quantity)
                ->setBasePriceInclTax($Product->Price)
                ->setBaseRowTotalInclTax($Product->Price * $Product->Quantity)
                ->setBaseDiscountAmount($OrdersModel->getProductDiscount($Request->BaseSubTotalIncl, $BaseTotalDiscount, $Product->Price, $Product->Quantity));

            $OrderItem->setPrice($Product->InternationalPrice / $Tax)
                ->setTaxAmount(($Product->InternationalPrice - $Product->InternationalPrice / $Tax) * $Product->Quantity)
                ->setOriginalPrice($Product->InternationalPrice)
                ->setRowTotal($Product->InternationalPrice / $Tax * $Product->Quantity)
                ->setPriceInclTax($Product->InternationalPrice)
                ->setRowTotalInclTax($Product->InternationalPrice * $Product->Quantity)
                ->setDiscountAmount($OrdersModel->getProductDiscount($Request->SubTotalIncl, $TotalDiscount, $Product->InternationalPrice, $Product->Quantity));

            //support TBT_Rewards
            $OrderItem->setRowTotalAfterRedemptionsInclTax($OrderItem->getBaseRowTotalInclTax());
            $OrderItem->setRowTotalAfterRedemptions($OrderItem->getBaseRowTotal());

            $OrderItem->setTaxPercent($Product->VATRate);

            $BaseSubTotal += $Product->Price * $Product->Quantity / $Tax;
            $SubTotal += $Product->InternationalPrice * $Product->Quantity / $Tax;
            $BaseSubTotalIncl += $Product->Price * $Product->Quantity;
            $SubTotalIncl += $Product->InternationalPrice * $Product->Quantity;
            $BaseTaxTotal += ($Product->Price - $Product->Price / $Tax) * $Product->Quantity ;
            $TaxTotal += ($Product->InternationalPrice - $Product->InternationalPrice / $Tax) * $Product->Quantity;
        }

        $Order->setBaseHiddenTaxAmount(0)
			->setBaseTaxAmount($BaseTaxTotal)
            ->setBaseSubtotal($BaseSubTotal)
            ->setBaseSubtotalInclTax($BaseSubTotalIncl)
            ->setBaseShippingAmount(0)
            ->setBaseShippingInclTax(0)
            ->setBaseGrandTotal($BaseSubTotalIncl - $BaseTotalDiscount)
            ->setBaseDiscountAmount($BaseTotalDiscount *(-1));

        $Order->setHiddenTaxAmount(0)
			->setTaxAmount($TaxTotal)
            ->setSubtotal($SubTotal)
            ->setSubtotalInclTax($SubTotalIncl)
            ->setShippingAmount(0)
            ->setShippingInclTax(0)
            ->setGrandTotal($SubTotalIncl - $TotalDiscount)
            ->setDiscountAmount($TotalDiscount *(-1));

        /**@var $BaseSetting Globale_Base_Model_Settings */
        $Setting = Mage::getModel('globale_base/settings');
        if($Setting->useExtOrderId()){
            $Order->setExtOrderId($Request->OrderId);
        }

        return $this;

    }


    /**
     * @param Varien_Event_Observer $Observer
     * @return $this
     */
    public function orderTotalsUpdatedNoBase(Varien_Event_Observer $Observer, $Request){

        /**@var $Order Mage_Sales_Model_Order */
        $Order = $Observer->getEvent()->getPayment()->getOrder();

        /**@var $OrdersModel Globale_Order_Model_Orders */
        $OrdersModel = Mage::getModel('globale_order/orders');
        $Request = $OrdersModel->reArrangeRequest($Request);

        $TotalDiscount = 0;
        $BaseTotalDiscount = 0;

        foreach ($Request->Discounts as $Discount) {
            if($Discount->DiscountType == self::CART_DISCOUNT){
                $TotalDiscount += $Discount->InternationalPrice;
                $BaseTotalDiscount += $Discount->Price;
            }
        }

        $SubTotal = 0;
        $BaseSubTotal = 0;
        $SubTotalIncl = 0;
        $BaseSubTotalIncl = 0;
        $TaxTotal = 0;
        $BaseTaxTotal = 0;

        foreach ($Order->getItemsCollection() as $OrderItem) {
            /**@var $OrderItem Mage_Sales_Model_Order_Item */
            $Id = $OrderItem->getQuoteItemId();

            // if it's child product that doesn't came from API
            if(!isset($Request->Products[$Id])){
                continue;
            }

            $Product = $Request->Products[$Id];

            $Tax = (1 + $Product->VATRate / 100);
            $OrderItem->setBasePrice($Product->InternationalPrice / $Tax)
                ->setBaseTaxAmount(($Product->InternationalPrice - $Product->InternationalPrice / $Tax) * $Product->Quantity)
                ->setBaseOriginalPrice($Product->InternationalPrice)
                ->setBaseRowTotal($Product->InternationalPrice / $Tax * $Product->Quantity)
                ->setBasePriceInclTax($Product->InternationalPrice)
                ->setBaseRowTotalInclTax($Product->InternationalPrice * $Product->Quantity)
                ->setBaseDiscountAmount($OrdersModel->getProductDiscount($Request->SubTotalIncl, $TotalDiscount, $Product->InternationalPrice, $Product->Quantity));

            $OrderItem->setPrice($Product->InternationalPrice / $Tax)
                ->setTaxAmount(($Product->InternationalPrice - $Product->InternationalPrice / $Tax) * $Product->Quantity)
                ->setOriginalPrice($Product->InternationalPrice)
                ->setRowTotal($Product->InternationalPrice / $Tax * $Product->Quantity)
                ->setPriceInclTax($Product->InternationalPrice)
                ->setRowTotalInclTax($Product->InternationalPrice * $Product->Quantity)
                ->setDiscountAmount($OrdersModel->getProductDiscount($Request->SubTotalIncl, $TotalDiscount, $Product->InternationalPrice, $Product->Quantity));

            //support TBT_Rewards
            $OrderItem->setRowTotalAfterRedemptionsInclTax($OrderItem->getRowTotalInclTax());
            $OrderItem->setRowTotalAfterRedemptions($OrderItem->getRowTotal());

            $OrderItem->setTaxPercent($Product->VATRate);

            $BaseSubTotal += $Product->Price * $Product->Quantity / $Tax;
            $SubTotal += $Product->InternationalPrice * $Product->Quantity / $Tax;
            $BaseSubTotalIncl += $Product->Price * $Product->Quantity;
            $SubTotalIncl += $Product->InternationalPrice * $Product->Quantity;
            $BaseTaxTotal += ($Product->Price - $Product->Price / $Tax) * $Product->Quantity ;
            $TaxTotal += ($Product->InternationalPrice - $Product->InternationalPrice / $Tax) * $Product->Quantity;
        }

        $Order->setBaseHiddenTaxAmount(0)
            ->setBaseTaxAmount($TaxTotal)
            ->setBaseSubtotal($SubTotal)
            ->setBaseSubtotalInclTax($SubTotalIncl)
            ->setBaseShippingAmount(0)
            ->setBaseShippingInclTax(0)
            ->setBaseGrandTotal($SubTotalIncl - $TotalDiscount)
            ->setBaseDiscountAmount($TotalDiscount *(-1));

        $Order->setHiddenTaxAmount(0)
            ->setTaxAmount($TaxTotal)
            ->setSubtotal($SubTotal)
            ->setSubtotalInclTax($SubTotalIncl)
            ->setShippingAmount(0)
            ->setShippingInclTax(0)
            ->setGrandTotal($SubTotalIncl - $TotalDiscount)
            ->setDiscountAmount($TotalDiscount *(-1));

        /**@var $BaseSetting Globale_Base_Model_Settings */
        $Setting = Mage::getModel('globale_base/settings');
        if($Setting->useExtOrderId()){
            $Order->setExtOrderId($Request->OrderId);
        }

        return $this;

    }


	/**
	 * Support Flint_Multistock 3-d part extension by adding current WebsiteId to helper list
	 * Event ==> adminhtml -> globale_order_create_quote_submit_before 
	 * @param Varien_Event_Observer $Observer
	 */
    public function pushScopeForFlintMultistockExtension(Varien_Event_Observer $Observer){

		if(Mage::helper('core')->isModuleEnabled('Flint_Multistock')){
			$Quote = $Observer->getEvent()->getData('quote');

			$WebsiteId = $Quote->getStore()->getWebsiteId();
			Mage::helper( 'flint_multistock' )->pushScope( array( 'website', $WebsiteId ) );
		}



	}

}