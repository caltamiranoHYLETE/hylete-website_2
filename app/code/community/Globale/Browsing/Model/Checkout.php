<?php
use GlobalE\SDK;
use GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\Models;

/**
 * Class Globale_Browsing_Model_Checkout
 */
class Globale_Browsing_Model_Checkout extends Mage_Core_Model_Abstract {

    const DISCOUNT_TYPE_CART            = 1;

    /**
     * If Order has Free Shipping Discount
     * @var bool
     */
    protected $FreeShippingDiscount = false;

    /**
     * holds the coupon discount code or the promotion rule name with the rule id
     * @var string
     */
    protected $FreeShippingDiscountRuleCode = null;

    /**
     * holds the user Quote singletone
     * @var  Mage_Sales_Model_Quote $Quote
     */
    protected $Quote;

    /**
     * Allow /Not Allow customer to login before redirect to Global-e checkout page
     * @return bool
     */
    public function isAllowLoginBeforeCheckout() {

        $Settings = Mage::getModel('globale_base/settings');
        return ($Settings->getIsAllowLoginBeforeCheckout() == 1);
    }

    /**
     * Redirect the customer in case of Login/Register before proceed to checkout
     * @param $Session
     * @return bool
     */
    public function redirectCustomerToLoginPage()
    {
        /** @var Mage_Customer_Model_Session $Session */
        $CustomerSession = Mage::getSingleton('customer/session');
        $IsProceedCheckout = Mage::app()->getRequest()->getParam('ptc'); // proceed to checkout page
        if ($this->isAllowLoginBeforeCheckout() && !$CustomerSession->isLoggedIn() && empty($IsProceedCheckout)) {
            /** @var Globale_Browsing_Helper_Checkout $Checkout */
            $Checkout = Mage::helper('globale_browsing/checkout');
            $Url = $Checkout->getGlobaleCheckoutPageURL();
            $CustomerSession->setBeforeAuthUrl($Url);
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Send Cart Api request
     * @param string $CartToken
     * @return SDK\Models\Common\Response
     */
    public function SendCart($CartToken) {

        /** @var  GlobalE\SDK\SDK $GlobaleSDK */
        $GlobaleSDK = Mage::registry('globale_sdk');

        // Get Customer from session
        $CustomerSession = Mage::getSingleton('customer/session');

		/** @var Mage_Customer_Model_Customer $Customer */
		$Customer = $CustomerSession->getCustomer();

        // Get cart quote
        $Quote = $this->getQuote();

        // Get Billing address
        $BillingAddressId = $Customer->getDefaultBilling();
        /** @var Mage_Customer_Model_Address $BillingAddress */
        $BillingAddress = Mage::getModel('customer/address')->load($BillingAddressId);

        // Get Customer billing address
        $CustomerBillingAddress = $this->getAddress($GlobaleSDK, $Customer, $BillingAddress);

        // Get Shipping address
        $ShippingAddressId = $Customer->getDefaultShipping();
        // If customer has only billing address, do not create shipping address
        if(!empty($ShippingAddressId)) {
            /** @var Mage_Customer_Model_Address $ShippingAddress */
            $ShippingAddress = Mage::getModel('customer/address')->load($ShippingAddressId);
            // Get Customer shipping address
            /** @var Common\Address $CustomerShippingAddress */
            $CustomerShippingAddress = $this->getAddress($GlobaleSDK, $Customer, $ShippingAddress, true);
        }

        // Get Shipping Option
        $ShippingOption = Mage::getModel('globale_base/cart')->getShippingOption();

        // Get all items
        $Items = $this->getItems($Quote);
        // Get Discount Information
        $Discount = $this->getDiscounts($Quote);
        // Build Send Cart Request
        $SendCartRequest = new Request\SendCart();
        $SendCartRequest->setCartId($CartToken);
        // If customer has only billing details, do not send shipping details
        if(!empty($ShippingAddressId)) {
            /** @var Common\Address $CustomerShippingAddress */
            $SendCartRequest->setShippingDetails($CustomerShippingAddress);
        }
        $SendCartRequest->setBillingDetails($CustomerBillingAddress);
        $SendCartRequest->setShippingOptionsList($ShippingOption);
        $SendCartRequest->setProductsList($Items);

        // OriginalCurrencyCode => Catalog Currency code
		$CurrentStoreBaseCurrency = Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $SendCartRequest->setOriginalCurrencyCode($CurrentStoreBaseCurrency);
        $SendCartRequest->setMerchantCartToken($Quote->getId());
        $SendCartRequest->setMerchantCartHash(spl_object_hash($Quote));

        //@TODO implement VAT exempt
        $SendCartRequest->setDoNotChargeVAT(null);
        $SendCartRequest->setVatRegistrationNumber(null);

		$FreeShippingDiscountRuleCode = Mage::getModel('globale_base/cart')->getFreeShippingDiscountRuleCode($Quote);

        // Set Is Free Shipping for coupon, if true set coupon code
        if($FreeShippingDiscountRuleCode !== null){
            $SendCartRequest->setIsFreeShipping(true);
            $SendCartRequest->setFreeShippingCouponCode($FreeShippingDiscountRuleCode);
        }else{
            $SendCartRequest->setIsFreeShipping(false);
        }

        $Locale = array(
        	'Key'		=> 'locale',
        	'Value' 	=> Mage::app()->getLocale()->getLocaleCode()
		);

		$UrlParameters = array();

		$UrlParameters[] = $Locale;
        $SendCartRequest->setUrlParameters($UrlParameters);

        // Get discounts list
        $SendCartRequest->setDiscountsList($Discount);

        // Send API "SendCart" request throughout the SDK
        $SendCart = $GlobaleSDK->Checkout()->SendCart($SendCartRequest);

        return $SendCart;
    }


    /**
     * @param $GlobaleSDK
     * @param Mage_Customer_Model_Customer $Customer
     * @param Mage_Customer_Model_Address $AddressDetails
     * @param boolean - true if shipping address
     * @return Common\Address
     */
    private function getAddress(SDK\SDK $GlobaleSDK, Mage_Customer_Model_Customer $Customer, Mage_Customer_Model_Address $AddressDetails, $IsShippingAddress = false) {

		$Address = Mage::getModel('globale_base/cart')->getAddress($Customer,$AddressDetails);

        //if exist user has empty countryCode info or it's shipping address -> load countryCode from SDK
        $CountryCode = $Address->getCountryCode();
        if((empty($CountryCode) || $IsShippingAddress)){
            /** @var GlobalE\SDK\Models\Common\Response\Data $GlobaleCustomerInfo */
            $GlobaleCustomerInfo = $GlobaleSDK->Browsing()->GetCustomerInformation();
            if ($GlobaleCustomerInfo->getSuccess()) {
                $Address->setCountryCode($GlobaleCustomerInfo->getData()->getcountryISO());
            }
        }

        return $Address;
    }

    /**
     * Get All Items from cart Quote
     * @param Mage_Sales_Model_Quote $Quote
     * @return array
     */
    public function getItems(Mage_Sales_Model_Quote $Quote) {

        $Items = array();
        foreach($Quote->getAllVisibleItems() as $Item) {

            // Get All Item information
			/**@var $ProductsRequest Request\Product[] */
            $ProductsRequest = $this->getItemDetails($Item);
			if(!empty($ProductsRequest)){
				$Items = array_merge($Items, $ProductsRequest);
			}
        }
        return $Items;
    }

    /**
     * Get All shopping cart discounts
     * @param Mage_Sales_Model_Quote $Quote
     * @return Common\Discount | null
     */
    public function getDiscounts(Mage_Sales_Model_Quote $Quote) {

		$Discount = Mage::getModel('globale_base/cart')->getDiscount($Quote);

		if(!empty($Discount)){
			//Add VatRate according to Cart Average Tax Rate
			$VatRate = $this->calculateAverageTaxRate($Quote);
			$Discount->setVATRate($VatRate);
		}

        return $Discount;
    }


    /**
     * Check if cart is empty
     * @return bool
     */
    public function isCartEmpty() {

        // Get cart quote
        /** @var  Mage_Sales_Model_Quote $Quote */
        $Quote = $this->getQuote();
        return !($Quote->hasItems());
    }


    /**
     * Redirect the customer to Global-e checkout page after login/register
     * @access public
     */
    public function redirectCustomerToCheckoutPage() {

        $IsOperatedByGlobale = Mage::registry('globale_user_supported');
        $IsProceedCheckout = Mage::app()->getRequest()->getParam('proceed_to_checkout');
        /** @var Globale_Browsing_Model_Checkout $Checkout */
        $Checkout = Mage::getModel('globale_browsing/checkout');
        if($IsOperatedByGlobale && $Checkout->isAllowLoginBeforeCheckout() && $IsProceedCheckout){
            // redirect the customer to Global-e checkout page
            /** @var Globale_Browsing_Helper_Checkout $CheckoutHelper */
            $CheckoutHelper = Mage::helper('globale_browsing/checkout');
            $GlobaleCheckoutURL = $CheckoutHelper->getGlobaleCheckoutPageURL();
            // set Customer redirect to Global-e checkout page
            /** @var Mage_Customer_Model_Session $Customer */
            $Customer = Mage::getSingleton('customer/session');
            $Customer->setBeforeAuthUrl($GlobaleCheckoutURL);
            $Customer->setAfterAuthUrl($GlobaleCheckoutURL);
        }
    }

    /**
     * Get All Item information
     * @param Mage_Sales_Model_Quote_Item $Item
     * @return Request\Product[] |array() $ProductsRequest
     */
    protected function getItemDetails( Mage_Sales_Model_Quote_Item $Item) {

        $Product = $Item->getProduct();

		if( !$Product->hasGlobaleProductInfo() ) {
			return array();
		}

		$ProductRequest = Mage::getModel('globale_base/product')->updateProductCommonAdditionalData($Product);

        # Prices
        /**@var $Info Models\Common\ProductResponseData **/
        $Info = $Product->getGlobaleProductInfo();

        /**@var $ItemModel Globale_Browsing_Model_Item */
        $ItemModel = Mage::getModel('globale_browsing/item');

        /**@var $Item Mage_Sales_Model_Quote_Item */
        $BaseAdditionalListItemAmount = $ItemModel->calculateAdditionalItemAmount($Item,true, false);
        $AdditionalListItemAmount = $ItemModel->calculateAdditionalItemAmount($Item, false, false);

        $AdditionalSaleBeforeRoundingItemAmount = $ItemModel->calculateAdditionalItemAmount($Item, false, true, true);

        $ProductRequest->setListPrice($Info->getListPrice() + $AdditionalListItemAmount);

        $ProductRequest->setOriginalListPrice($Info->getOriginalListPrice() + $BaseAdditionalListItemAmount);

		$AdditionalSaleItemAmount = $ItemModel->calculateAdditionalItemAmount($Item, false, true);
		$ProductSalePrice = $Info->getSalePrice() + $AdditionalSaleItemAmount;


        $ProductRequest->setVATRateType($Info->getVATRateType());
        $ProductRequest->setLocalVATRateType($Info->getLocalVATRateType());

        $ProductRequest->setIsFixedPrice($Info->getMarkedAsFixedPrice());


        //data that came from Item
        $ProductRequest->setOrderedQuantity($Item->getQty());
        $ProductRequest->setCartItemId($Item->getId());

		$CalculationIncludeTax =  Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX);

		//get SalePrices values from Item
		// usage in RowTotal and not in Price - Custom Price are changing row_total and not price properties
		if($CalculationIncludeTax){
			$ItemSalePrice = $Item->getRowTotalInclTax()/$Item->getQty();
			$ItemOriginalSalePrice = $Item->getBaseRowTotalInclTax()/$Item->getQty();

		}else{
			$ItemSalePrice = $Item->getRowTotal()/$Item->getQty();
			$ItemOriginalSalePrice = $Item->getBaseRowTotal()/$Item->getQty();
		}

		$ProductRequest->setSalePrice($ItemSalePrice);
		$ProductRequest->setOriginalSalePrice($ItemOriginalSalePrice);

		//Calculating SalePriceBeforeRounding
		//could be different from $ProductSalePriceBeforeRounding in the case Item Price != Product Price
		$ProductSalePriceBeforeRounding = $Info->getSalePriceBeforeRounding() + $AdditionalSaleBeforeRoundingItemAmount;
		$ItemSalePriceBeforeRounding = $ProductSalePriceBeforeRounding * $ItemSalePrice / $ProductSalePrice;

		//if SalePriceBeforeRounding = "0" not to set value - prevent dividing in "0" in core
		if($ItemSalePriceBeforeRounding){
			$ProductRequest->setSalePriceBeforeRounding($ItemSalePriceBeforeRounding);
		}


		$ProductsRequest = array($ProductRequest);

		$ItemCustomOptions = $this->buildItemCustomOptions($Item);
		if(!empty($ItemCustomOptions)){
			$ProductsRequest = array_merge($ProductsRequest,$ItemCustomOptions);
		}

        return $ProductsRequest;
    }


    /**
     * calculate Average Tax Rate
     * @param Mage_Sales_Model_Quote $Quote
     * @return float
     */
    protected function calculateAverageTaxRate(Mage_Sales_Model_Quote $Quote) {

        $AverageTaxRate = 0;
        $Total = 0;
        $ItemTaxValueArray = array();


        foreach ($Quote->getAllItems() as $Item) {
            /**@var $Item Mage_Sales_Model_Quote_Item */

            if ($Item->isDeleted() || !$Item->hasRowTotalInclTax()) {
                continue;
            }
            $Product = $Item->getProduct();

            if($Product->hasGlobaleProductInfo()){

				/**@var $Info Common\ProductResponseData **/
				$Info = $Product->getGlobaleProductInfo();

                $ItemTaxPercent = $Info->getVATRateType()->getRate();
                $ItemPrice = $Item->getRowTotalInclTax();

                $Total += $ItemPrice;
                $ItemTaxValueArray[] = $ItemPrice * $ItemTaxPercent;
            }
        }
        if (!$Total) {
            return 0;
        }

        foreach ($ItemTaxValueArray AS $ItemTaxValue) {
            $AverageTaxRate += $ItemTaxValue / $Total;
        }

        return $AverageTaxRate;
    }


	/**
	 * Build array of Item Custom Options
	 * @param Mage_Sales_Model_Quote_Item $Item
	 * @return Request\Product[] |array() $ItemOptions
	 */
    protected function buildItemCustomOptions(Mage_Sales_Model_Quote_Item $Item){
		$ItemOptions = array();

		$OptionsArray = $Item->getProduct()->getTypeInstance(true)->getOrderOptions($Item->getProduct());

        // added to support gifrtwrap for EE version.
		if(Mage::helper('core')->isModuleEnabled('Enterprise_GiftWrap')){
		    $OptionsArray[] = $Item->getOptionByCode('GIFTWRAP');
        }

		if(empty($OptionsArray['options'])){
			return array();
		}

        $SelectedOptions = $OptionsArray['options'];

		foreach ($SelectedOptions AS $SelectedOption){
			$ItemCustomOptionProduct = $this->buildItemCustomOptionProduct($SelectedOption,$Item);

			if(!empty($ItemCustomOptionProduct)){
				$ItemOptions[] = $ItemCustomOptionProduct;
			}
		}
		return $ItemOptions;
	}

	/**
	 * @param array $SelectedOption
	 * @param Mage_Sales_Model_Quote_Item $Item
	 * @return Request\Product
	 */
	protected function buildItemCustomOptionProduct(array $SelectedOption,Mage_Sales_Model_Quote_Item $Item ){

		$ItemCustomOptionProduct = new Request\Product();

		$OptionProductCode = $Item->getSku().'|'.$SelectedOption['option_id'].'|'.crc32($SelectedOption['value']);

		/**@var $Info Common\ProductResponseData **/
		$Info = $Item->getProduct()->getGlobaleProductInfo();

		$ItemCustomOptionProduct
			->setProductCode($OptionProductCode)
			->setName($SelectedOption['label'])
			->setDescription($SelectedOption['value'])
			->setWeight(0.001)
			->setListPrice(0)
			->setOriginalListPrice(0)
			->setSalePrice(0)
			->setSalePriceBeforeRounding(0)
			->setOriginalSalePrice(0)
			->setOrderedQuantity($Item->getQty())
			->setVATRateType($Info->getVATRateType())
			->setLocalVATRateType($Info->getLocalVATRateType())
			->setCartItemId($Item->getId().$SelectedOption['option_id'])
			->setParentCartItemId($Item->getId())
			->setCartItemOptionId($SelectedOption['option_id']);

		return $ItemCustomOptionProduct;

	}

    /**
     * Singletone get user Quote
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote(){

        if($this->Quote == null){
            $this->setQuote(Mage::getModel('checkout/cart')->getQuote());
        }
        return $this->Quote;
    }

    /**
     * Set user Quote
     * @param $Quote
     */
    protected function setQuote($Quote){

        $this->Quote = $Quote;
    }

}