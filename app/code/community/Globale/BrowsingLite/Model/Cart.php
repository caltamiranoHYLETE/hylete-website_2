<?php

use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\Models\Common\Response;

class Globale_BrowsingLite_Model_Cart extends Mage_Core_Model_Abstract {

    /**
     * @param $CartId
     * @param string $FixedPriceCountry if fixed price supported - country ISO
     * @param string $FixedPriceCurrency if fixed price supported - currency ISO
     * @param string $MerchantGUID - exist if server call to Magento
     * @return Response\GetCart | Response\GetCartError
     */
    public function getInfo($CartId,$FixedPriceCountry,$FixedPriceCurrency,$MerchantGUID){

        if(!empty($MerchantGUID) && $MerchantGUID == Mage::getStoreConfig(Globale_Base_Model_Settings::MERCHANT_GUID)){
            $MagentoCartId = Mage::helper('globale_base/cartHashing')->fetchCartId($CartId,$MerchantGUID);
            $Quote = Mage::getModel('sales/quote')->load($MagentoCartId);
        }else{
            $Quote = Mage::getModel('checkout/cart')->getQuote();
        }

        $CartInfo = new Response\GetCart();

        if($Quote->getIsActive() == 0 ){

            $CartInfo = new Response\GetCartError();
            $CartInfo->setErrorMessage('No active quote found') ;
            return $CartInfo;
        }


        $ProductList = $this->buildProductList($Quote,$FixedPriceCountry,$FixedPriceCurrency);
        $DiscountsList = $this->getDiscountsList($Quote);

        /** @var Mage_Customer_Model_Customer $Customer */
        $Customer = $Quote->getCustomer();

        $CartInfo->setUserId($Customer->getId());

        //Billing Address
        $BillingAddressId = $Customer->getDefaultBilling();
        if(!empty($BillingAddressId)){
            /** @var Mage_Customer_Model_Address $BillingAddressDetails */
            $BillingAddressDetails = Mage::getModel('customer/address')->load($BillingAddressId);
            $CartInfo->setBillingDetails(Mage::getModel('globale_base/cart')->getAddress($Customer,$BillingAddressDetails));

        }

        //Shipping Address
        $ShippingAddressId = $Customer->getDefaultShipping();
        if(!empty($ShippingAddressId)){
            /** @var Mage_Customer_Model_Address $ShippingAddressDetails */
            $ShippingAddressDetails = Mage::getModel('customer/address')->load($ShippingAddressId);
            $CartInfo->setShippingDetails(Mage::getModel('globale_base/cart')->getAddress($Customer,$ShippingAddressDetails));
        }

        $CartInfo->setProductsList($ProductList);
        $CartInfo->setDiscountsList($DiscountsList);

        //Free Shipping
        $FreeShippingDiscountRuleCode = Mage::getModel('globale_base/cart')->getFreeShippingDiscountRuleCode($Quote);
        if($FreeShippingDiscountRuleCode !== null){
            $CartInfo->setIsFreeShipping(true);
            $CartInfo->setFreeShippingCouponCode($FreeShippingDiscountRuleCode);
        }else{
            $CartInfo->setIsFreeShipping(false);
        }

        $LoyaltyPointsArray = $this->getLoyaltyPoints($Quote);

        if(!empty($LoyaltyPointsArray['LoyaltyPointsTotal'])) {
            $CartInfo->setLoyaltyPointsTotal($LoyaltyPointsArray['LoyaltyPointsTotal']);
        }

        if(!empty($LoyaltyPointsArray['LoyaltyPointsEarned'])) {
            $CartInfo->setLoyaltyPointsEarned($LoyaltyPointsArray['LoyaltyPointsEarned']);
        }

        if(!empty($LoyaltyPointsArray['LoyaltyPointsSpent'])) {
            $CartInfo->setLoyaltyPointsSpent($LoyaltyPointsArray['LoyaltyPointsSpent']);
        }

        $ShippingOption = Mage::getModel('globale_base/cart')->getShippingOption();
        $CartInfo->setShippingOptionsList(array($ShippingOption));


        $CartInfo->setCartHash(md5(print_r($CartInfo,true)));
        return $CartInfo;
    }

    /**
     * Get Loyalty Points from TBT_Rewards Session
     * @param Mage_Sales_Model_Quote $Quote
     * @return array
     */
    protected function getLoyaltyPoints(Mage_Sales_Model_Quote $Quote){

        $Arr = array();
        if(Mage::helper('core')->isModuleEnabled('TBT_Rewards')){
            $RewardSession = Mage::getSingleton('rewards/session');
            $LoyaltyPointsTotal = $RewardSession->getSessionCustomer()->getPoints();
            $LoyaltyPointsEarned = $RewardSession->getTotalPointsEarning();

            if(isset($LoyaltyPointsTotal[1])){
                $Arr['LoyaltyPointsTotal'] = $LoyaltyPointsTotal[1];
            }
            if(isset($LoyaltyPointsEarned[1])){
                $Arr['LoyaltyPointsEarned'] = $LoyaltyPointsEarned[1];
            }
            $Arr['LoyaltyPointsSpent'] = $Quote->getPointsSpending();
        }

        return $Arr;

    }

    /**
     * Build array of Cart Products
     * @param Mage_Sales_Model_Quote $Quote
     * @param $FixedPriceCountry
     * @param $FixedPriceCurrency
     * @return Request\Product[] | array()
     */
    protected function buildProductList(Mage_Sales_Model_Quote $Quote,$FixedPriceCountry,$FixedPriceCurrency){

        $Items = array();
        foreach($Quote->getAllVisibleItems() as $Item) {

            // Get All Item information
            /**@var $ProductsRequest Request\Product[] */
            $ProductsRequest = $this->getItemDetails($Item,$FixedPriceCountry,$FixedPriceCurrency);
            if(!empty($ProductsRequest)){
                $Items = array_merge($Items, $ProductsRequest);
            }
        }
        return $Items;
    }


    /**
     * @param Mage_Sales_Model_Quote_Item $Item
     * @param $FixedPriceCountry
     * @param $FixedPriceCurrency
     * @return Request\Product[]
     */
    protected function getItemDetails(Mage_Sales_Model_Quote_Item $Item,$FixedPriceCountry,$FixedPriceCurrency){

        $Product = $Item->getProduct();
        $ProductRequest = Mage::getModel('globale_base/product')->updateProductCommonAdditionalData($Product);

        //Prices
        $ProductRequest->setOriginalSalePrice($this->getOriginalSalePrice($Item));

        //fixed price
        $FixedPrices = $this->getProductFixedPrices($Item,$FixedPriceCountry,$FixedPriceCurrency);

        if(!empty($FixedPrices)){
            $ProductRequest->setIsFixedPrice(true);

            if(!empty($FixedPrices['special_price'])){
                $ProductRequest->setListPrice($FixedPrices['price']);
                $ProductRequest->setSalePrice($FixedPrices['special_price']);
            }else{
                $ProductRequest->setSalePrice($FixedPrices['price']);
            }

        }else{
            $ProductRequest->setIsFixedPrice(false);
        }

        //data that came from Item
        $ProductRequest->setOrderedQuantity( $this->getOrderQuantity($Item));
        $ProductRequest->setCartItemId($Item->getId());

        $ProductsRequest = array($ProductRequest);
        return $ProductsRequest;
    }


    /**
     * @param Mage_Sales_Model_Quote_Item $Item
     * @return float
     */
    protected function getOriginalSalePrice(Mage_Sales_Model_Quote_Item $Item){
        //@TODO check what happen when catalog price exclude tax
        $ItemTotal = (float)$Item->getRowTotalInclTax();
        $OriginalSalePrice = $ItemTotal / $Item->getQty();
        return $OriginalSalePrice;
    }

    /**
     * create Discount List according to $Quote discounts
     * @param $Quote
     * @return array
     */
    protected function getDiscountsList(Mage_Sales_Model_Quote $Quote){
        $DiscountsList = array();
        $Discount = Mage::getModel('globale_base/cart')->getDiscount($Quote);

        if(!empty($Discount)){
            $Discount->setDiscountValue(null);
            $DiscountsList[] = $Discount;
        }
        return $DiscountsList;

    }

    /**
     * @param Mage_Sales_Model_Quote_Item $Item
     * @param $FixedPriceCountry
     * @param $FixedPriceCurrency
     * @return array
     */
    protected function getProductFixedPrices(Mage_Sales_Model_Quote_Item $Item, $FixedPriceCountry, $FixedPriceCurrency){
        $FixedPrices = array();
        if(empty($FixedPriceCountry) || empty($FixedPriceCurrency)){
            return array();
        }

		//  use Catalog Price As FixedPrice
		if(empty($FixedPrices) && Mage::helper('globale_base')->useCatalogPriceAsFixedPrice()){

			if($Item->getProduct()->getFinalPrice() != $Item->getProduct()->getSpecialPrice()){
				$FixedPrices = array(
					'price'         => $Item->getProduct()->getPrice(),
					'special_price' => $Item->getProduct()->getFinalPrice()
				);
			}else{
				$FixedPrices = array(
					'price'         => $Item->getProduct()->getFinalPrice()
				);
			}
		}elseif (Mage::helper('core')->isModuleEnabled('Globale_FixedPrices')){
			// Get Fixed Prices from Fixed price module
			//@TODO - Add to GE DB override parameter for using both catalog and GE fixed prices

			$FixedPrices = Mage::getModel('globale_fixedprices/product')
				->loadSingleProductFixedPrices($Item->getSku(),$FixedPriceCurrency,$FixedPriceCountry);

			if(empty($FixedPrices) && $Item->getProduct()->getData('sku') != $Item->getSku()){
				//Take Fixed Price from parent
				$FixedPrices = Mage::getModel('globale_fixedprices/product')
					->loadSingleProductFixedPrices($Item->getProduct()->getData('sku'),$FixedPriceCurrency,$FixedPriceCountry);
			}
		}

		//if Item has a Custom Price we will change Sale price to data from item
		$ItemCustomPrice = $Item->getCustomPrice();
		if(!empty($FixedPrices) && $ItemCustomPrice ){

			if(isset($FixedPrices['special_price'])){
				$FixedPrices['special_price'] = $ItemCustomPrice;
			}else{
				$FixedPrices['price'] = $ItemCustomPrice;
			}}

        return $FixedPrices;
    }


    /**
     * If Request GET parameter 'CheckStock' in the url return only the quantity that is currently available in stock
     * @param Mage_Sales_Model_Quote_Item $Item
     * @return int $OrderedQuantity
     */
    protected function getOrderQuantity(Mage_Sales_Model_Quote_Item $Item){

        // Get the quote ordered item quantity
        $OrderedQuantity = $Item->getQty();
        // On Gem GET parameter CheckStock = 'true'.
        // If item product quantity stock is less then Item Quantity return the product stock
        $CheckStock = Mage::App()->getRequest()->getParam('CheckStock');
        if(!empty($CheckStock) && $CheckStock == 'true'){

            // Get product by sku
            $Product = Mage::getModel('catalog/product')->loadByAttribute('sku', $Item->getSku());
            // Get stock details for this product
            $StockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($Product);
            // Stock Quantity
            $ProductStockQuantity = (int)$StockItem->getQty();
            // Minimum Stock Quantity Allowed
            $MinimumStockQuantityAllowd = (int)$StockItem->getMinQty();
            // Stock Availability
            $IsProductInStock = (bool)$StockItem->getIsInStock();

            // Check if the product is in stock
            if($IsProductInStock && ($ProductStockQuantity > 0) && ($ProductStockQuantity > $MinimumStockQuantityAllowd)){
                // product quantity is in stock
                // If product stock quantity less then the items ordered quantity get the product stock quantity
                $OrderedQuantity = ($ProductStockQuantity < $OrderedQuantity) ? $ProductStockQuantity : $OrderedQuantity;
            }else{
                // product quantity is out of stock
                $OrderedQuantity = 0;
            }
        }
        return $OrderedQuantity;
    }

    /**
     * Create API call to GEPI API based on Params
     * @param array $Params Query string given by GEM
     * @return mixed
     */
    public function getCartToken($Params){

        $Quote = Mage::getModel('checkout/cart')->getQuote();
        $LoyaltyPointsArray = $this->getLoyaltyPoints($Quote);
        $Params = array_merge($Params, $LoyaltyPointsArray);
        $BaseUrl = 'https:' . Mage::getStoreConfig(Globale_Base_Model_Settings::GEM_BASE_URL)  . 'checkout/GetCartToken?';
        $Url = $BaseUrl . http_build_query($Params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $Response = curl_exec($ch);
        curl_close($ch);


        return $Response;

    }

}