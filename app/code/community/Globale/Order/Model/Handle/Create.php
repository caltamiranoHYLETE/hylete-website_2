<?php

use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\Models\Common\iHandleAction;


/**
 * Class Globale_Browsing_Model_Conversion
 */
class Globale_Order_Model_Handle_Create extends Mage_Core_Model_Abstract implements iHandleAction
{

    /**
     * @param $Request
     * @return Response|Response\Order
     */
    public function handleAction($Request){


        //validate Request call before starting the order creation process
        $ISValid = $this->validateRequest($Request);
        if(!$ISValid->getSuccess()){
            return $ISValid;
        }

        $StoreCode = $Request->WebStoreCode;

        /** @var $store Mage_Core_Model_Store */
        $Store = Mage::getSingleton('core/store')->load($StoreCode);

        /** @var $Quote Mage_Sales_Model_Quote */
        $Quote = Mage::getModel('sales/quote')->setStore($Store)->load($Request->CartId);

        Mage::register('GlobaleOrderRequest', $Request);

        //update data of Quote from Request data
        $this->updateQuoteData($Request, $Quote);

        try {
            // Create Order From Quote
            /** @var $Service Mage_Sales_Model_Service_Quote */
            $Service = Mage::getModel('sales/service_quote', $Quote);
            $Service->submitAll();
            $IncrementId = $Service->getOrder()->getRealOrderId();
        }
        catch (Exception $ex) {
            return new Response(false, $ex->getMessage());
        }

        if(!empty($IncrementId)){

            // Subscribe to newsletter
            $this->setNewsletterSubscribe($Request);

            // save the order into database
            $this->saveOrderDetailsIntoDB($IncrementId, $Request, $Service->getOrder());

            //manually deactivating the Quote
            $Quote->setIsActive(0)->save();

            /** @var Globale_Order_Model_Orders $GlobaleOrder */
            $GlobaleOrder = Mage::getModel('globale_order/orders')->load($Request->OrderId,'globale_order_id');
            $GlobaleOrder->saveSuccessOrder();

            $Response = new Response\Order(true, null, $IncrementId, $IncrementId);
        }
        else{
            $Response = new Response(false, "Error as occurred when trying to create an order!");
        }

        return $Response;

    }

    /**
     * @param stdClass $Request
     * @param Mage_Sales_Model_Quote $Quote
     */
    private function updateQuoteData($Request, Mage_Sales_Model_Quote $Quote) {

		//Allow quote to use MultiCurrency
		$Quote->setIsSuperMode(true);

        if(!$Request->Customer->SendConfirmation){
            $Quote->setSendConfirmation(false);
        }

		$Quote->setCustomerEmail(urldecode($Request->PrimaryBilling->Email));

		$this->setAllowedCurrency($Request->InternationalDetails->CurrencyCode);
        $Quote->setQuoteCurrencyCode($Request->InternationalDetails->CurrencyCode);

        //Set Customer First/LastName from GE Request
		$Quote->setCustomerFirstname(urldecode($Request->PrimaryBilling->FirstName));
		$Quote->setCustomerLastname(urldecode($Request->PrimaryBilling->LastName));

        $this->setQuoteAddress($Quote, $Request);

        $ShippingAddress = $Quote->getShippingAddress();
        $ShippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($Request->ShippingMethodCode)
            ->setPaymentMethod('globale')
			->setShouldIgnoreValidation(true);

		$Quote->getBillingAddress()->setShouldIgnoreValidation(true);

        $Quote->getPayment()->importData(array('method' => 'globale'));

    }

	/**
	 * Set Globale currency as CurrentCurrencyCode
	 * @param $CurrencyCode
	 */
	public function setAllowedCurrency($CurrencyCode){

		$Store = Mage::app()->getStore();
		$AllowedCurrency = $Store->getAvailableCurrencyCodes(true);
		if(!in_array($CurrencyCode, $AllowedCurrency)){
			$AllowedCurrency[] = $CurrencyCode;
			$Store->setData('available_currency_codes',$AllowedCurrency);
		}

		$Store->setCurrentCurrencyCode($CurrencyCode);

	}


    /**
     * @param $Quote
     * @param $Request
     */
    private function setQuoteAddress($Quote, $Request){

        if($Request->Customer->IsEndCustomerPrimary){
            $Shipping = 'PrimaryShipping';
            $Billing = 'PrimaryBilling';
        }
        else{
            $Shipping = 'SecondaryShipping';
            $Billing = 'SecondaryBilling';
        }

        $ShippingAddress = $Request->{$Shipping};
        $BillingAddress = $Request->{$Billing};

        $LowerShippingAddress = $this->mergeAddresses($ShippingAddress);
        $LowerBillingAddress = $this->mergeAddresses($BillingAddress);

        $Quote->getShippingAddress()->addData($LowerShippingAddress);
        $Quote->getBillingAddress()->addData($LowerBillingAddress);

    }

    /**
     * @param $Request
     * @return Response
     */
    private function validateRequest($Request){

        $Valid = new Response(true);

        $GlobaleOrder = Mage::getModel('globale_order/orders')->load($Request->OrderId,'globale_order_id');
        $OrderId = $GlobaleOrder->getId();
        if(!empty($OrderId) && $GlobaleOrder->getStatus() == 1){
            $Valid = new Response(false, "Order {$GlobaleOrder->getGlobaleOrderId()} - {$GlobaleOrder->getOrderId()} Already Exists.\r\n");
        }
        elseif(!empty($OrderId) && $GlobaleOrder->getStatus() == 0){
            $Valid = $this->removeFailedOrder($GlobaleOrder->getOrderId());
            $Valid->setMessage("Order {$GlobaleOrder->getGlobaleOrderId()} - {$GlobaleOrder->getOrderId()} didn't create properly so it was removed to try create it again.\r\n");
        }

        //@TODO: validate Quote vs Request Items

        return $Valid;

    }

	/**
	 * Remove Global-e order data from DB (all tables using FK)
	 * and also cancel the Magento order.
	 * @param $OrderId
	 * @return Response
	 */
    private function removeFailedOrder($OrderId){

        try{
            // will remove al constrained rows to the order_id
            Mage::getModel('globale_order/orders')->load($OrderId, 'order_id')->delete();

            /** @var Mage_Sales_Model_Order $Order */
            $Order = Mage::getModel('sales/order')->load($OrderId, 'increment_id');
            $Response = $this->cancelOrder($Order);
        }
        catch (Exception $e){
            $Response = new Response(false, $e->getMessage());
        }

        return $Response;

    }

	/**
	 * @param Mage_Sales_Model_Order $Order
	 * @return Response
	 */
    private function cancelOrder(Mage_Sales_Model_Order $Order){

        $Response = new Response(true);

        if ($Order->canCancel()) {
            try {
                $Order->cancel();
                // remove status history set in _setState
                $Order->getStatusHistoryCollection(true);
                $History = $Order->addStatusHistoryComment('Order marked as cancelled.', false);
                $History->setIsCustomerNotified(false);
                $Order->save();
                $Response->setSuccess(true);
            } catch (Exception $e) {
                $Response->setSuccess(false)->setMessage($e->getMessage());
            }
        }

        return $Response;
    }

    /**
     * @param $Address
     * @return mixed
     */
    private function mergeAddresses($Address){

        foreach($Address as $k => $v){

            switch ($k){
                case 'FirstName':
                    $MergedAddress['firstname'] = urldecode($v);
                    break;
                case 'MiddleName':
                    $MergedAddress['middlename'] = urldecode($v);
                    break;
                case 'LastName':
                    $MergedAddress['lastname'] = urldecode($v);
                    break;
                case 'Salutation':
                    $MergedAddress['prefix'] = $v;
                    break;
                case 'Address1':
                    $MergedAddress['street'] = urldecode($v);
                    break;
                case 'Address2':
                    if(!empty($v)){
                        $MergedAddress['street'] .= ', ' . urldecode($v);
                    }
                    break;
                case 'City':
                    $MergedAddress['city'] = urldecode($v);
                    break;
                case 'StateCode':
                    $MergedAddress['region_id'] = ($v)? $v : "";
                    break;
                case 'StateOrProvince':
                    $MergedAddress['region'] = ($v)? urldecode($v) : "";
                    break;
                case 'Zip':
                    $MergedAddress['postcode'] = $v;
                    break;
                case 'Email':
                    $MergedAddress['email'] = urldecode($v);
                    break;
                case 'Phone1':
                    $MergedAddress['telephone'] = urldecode($v);
                    if(empty($MergedAddress['telephone'])){
                        $MergedAddress['telephone'] = '0000000000';
                    }
                    break;
                case 'Phone2':
                    if(!empty($v)){
                        $MergedAddress['telephone'] .= ', ' . urldecode($v);
                    }
                    break;
                case 'CountryCode':
                    $MergedAddress['country_id'] = $v;
                    break;
                case 'CountryCode3':
                case 'CountryName':
                    break;
                default:
                    $MergedAddress[strtolower($k)] = $v;
                    break;
            }
        }

        return $MergedAddress;
    }

    /**
     * Save all request information of the order to the database
     * @param string $IncrementId                       Order incremental id
     * @param object $Request                           API request
     * @param Mage_Sales_Model_Service_Quote $Service   Service quote of the order
     * @access private
     */
    private function saveOrderDetailsIntoDB($IncrementId, $Request, Mage_Sales_Model_Order $Order) {

        // Save order general information
        /** @var Globale_Order_Model_Orders $Orders */
        $Orders = Mage::getModel('globale_order/orders');
        $Orders->saveOrder($IncrementId, $Request);

        // Save order details
        /** @var Globale_Order_Model_Details $Details */
        $Details = Mage::getModel('globale_order/details');
        $Details->saveOrderDetails($Order, $Request, $IncrementId);

        // Save order address

        // Primary Billing
        /** @var Globale_Order_Model_Addresses $Addresses */
        $Addresses = Mage::getModel('globale_order/addresses');
        $Addresses->saveAddresses($Request->PrimaryBilling, $IncrementId, Globale_Order_Model_Addresses::BILLING, true);

        // Secondart Billing
        /** @var Globale_Order_Model_Addresses $Addresses */
        $Addresses = Mage::getModel('globale_order/addresses');
        $Addresses->saveAddresses($Request->SecondaryBilling, $IncrementId, Globale_Order_Model_Addresses::BILLING, false);

        // Secondart Billing
        /** @var Globale_Order_Model_Addresses $Addresses */
        $Addresses = Mage::getModel('globale_order/addresses');
        $Addresses->saveAddresses($Request->PrimaryShipping, $IncrementId, Globale_Order_Model_Addresses::SHIPPING, true);

        // Secondart Billing
        /** @var Globale_Order_Model_Addresses $Addresses */
        $Addresses = Mage::getModel('globale_order/addresses');
        $Addresses->saveAddresses($Request->SecondaryShipping, $IncrementId, Globale_Order_Model_Addresses::SHIPPING, false);

        // Save order discounts


		if(count($Request->Discounts)) {
			foreach ($Request->Discounts as $Discount) {
				/** @var $DiscountModel Globale_Order_Model_Discounts  */
				$DiscountModel = Mage::getModel('globale_order/discounts');
				$DiscountModel->saveDiscount($Discount, $IncrementId);
			}
		}

        // Save order shipping

        /** @var Globale_Order_Model_Shipping $Shipping */
        $Shipping = Mage::getModel('globale_order/shipping');
        $Shipping->saveShipping($Request, $IncrementId);

        // Save order payment

        /** @var Globale_Order_Model_Payment $Payment */
        $Payment = Mage::getModel('globale_order/payment');
        $Payment->saveCustomerPayment($Request, $IncrementId);

        // Save order products
        if(count($Request->Products)) {
            foreach ($Request->Products as $Product) {
                /** @var Globale_Order_Model_Products $Products */
                $Products = Mage::getModel('globale_order/products');
                $Products->saveProduct($Product, $IncrementId);
            }
        }
    }

    /**
     * Subscribe to newsletter
     * @param object $Request
     */
    private function setNewsletterSubscribe($Request) {

        // Subscribe to newsletter by configuration attribute from request
        if($Request->AllowMailsFromMerchant) {
            $SubscribeEmail = urldecode($Request->PrimaryBilling->Email);
            $Subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($SubscribeEmail);
            if ($Subscriber->getId()) {
                // For registered users
                // Newsletter/Subscriber change status to "subscribed"
                $Subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                $Subscriber->save();
            } else {
                // For guest users
                // Create new subscriber by email
                Mage::getModel('newsletter/subscriber')->subscribe($SubscribeEmail);
            }
        }
    }

}

