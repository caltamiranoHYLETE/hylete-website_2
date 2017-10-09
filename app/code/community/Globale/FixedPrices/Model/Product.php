<?php
use GlobalE\SDK;
use GlobalE\SDK\API\Common\Response\Country;
use GlobalE\SDK\Models;

class Globale_FixedPrices_Model_Product extends Mage_Core_Model_Abstract {

    const PRICE_TYPE_REGULAR = 'regular_price';
    const PRICE_TYPE_SPECIAL = 'special_price';

    const HTML_ATTRIBUTE_REGULAR = "data-fp='%s'";
    const HTML_ATTRIBUTE_SPECIAL = "data-fp='%s'";

    const DATE_FORMAT = 'Y-m-d';

    /**
     * Update Products collection with fixed prices
     * @param $Products
     */
    public function loadProductsFixedPrices($Products){
        // Check if country support fixed price
        if($this->isSupportFixedPrice()) {
            // Collect all products id's in order to filter by product id's in one query
            $ProductsList = array();
            foreach ($Products as $Product) {
                // prepere product collection filtered by id's
                $ProductsList[$Product->getSku()] = $Product;
            }

			$GlobaleSDK = Mage::registry('globale_sdk');
			$CustomerInfo = $GlobaleSDK->Browsing()->GetCustomerInformation();

			if($CustomerInfo->getSuccess()) {
				$CurrencyCode = $CustomerInfo->getData()->getcurrencyCode();
				$CountryCode = $CustomerInfo->getData()->getcountryISO();
				// Run query in order to filter products that has fixed prices and needs to change their prices
				$QueryFilteredProducts = $this->FilterProductFixedPrices(array_keys($ProductsList),$CurrencyCode,$CountryCode );
			}

			if (!empty($QueryFilteredProducts)) {

                $GlobaleFixedpriceProducts = Mage::registry('globale_fixedprice_products');
                if (!empty($GlobaleFixedpriceProducts)) {
                    Mage::unregister('globale_fixedprice_products');
                    $FullProducts = array_merge($GlobaleFixedpriceProducts, $QueryFilteredProducts);
                } else {
                    $FullProducts = $QueryFilteredProducts;
                }

                Mage::register('globale_fixedprice_products', $FullProducts);
            }
        }
    }

    /**
     * Check if Country supported Fixed Prices
     * @return boolean
     */
    protected function isSupportFixedPrice(){
        //@TODO: check also merchantAppSettings

        $CurrentCountry = Mage::registry('globale_current_country');
        if( empty($CurrentCountry) ){
            /**@var $GlobaleSDK SDK\SDK */
            $GlobaleSDK = Mage::registry('globale_sdk');
            /**@var $CountryObject Models\Country */
            $CountriesResponse = $GlobaleSDK->Browsing()->GetCountries();
            if($CountriesResponse->getSuccess()){
                $CurrentCountry = $CountriesResponse->getData()->getCountry();
            }
            Mage::register('globale_current_country', $CurrentCountry);
        }

        /**@var $CurrentCurrency Country */
        return $CurrentCountry->getSupportsFixedPrices();
    }

    /**
     * Filter products which are support and has fixed prices in database
     * @param array $ProductsCodes - filter by product SKU's
	 * @param string $CurrencyCode
	 * @param string $CountryCode
     * @return array $FilteredProducts - filtered products that support and has fixed prices in database
     */
    public function FilterProductFixedPrices(array $ProductsCodes,$CurrencyCode,$CountryCode ){

		$FilteredProducts = array();

		/**@var $FP_Collection Globale_FixedPrices_Model_Resource_Fixedprices_Collection */
		$FP_Collection = Mage::getModel('globale_fixedprices/fixedprices')->getCollection()
			->addFieldToFilter('product_code', array('in' => $ProductsCodes))
            ->addFieldToFilter('currency_code', $CurrencyCode)
            ->addFieldToFilter('country_code', array($CountryCode, array('null' => true)))
			->setOrder('country_code', 'DESC');

		$ResultsFixedPrices = $FP_Collection->getData();
		if (!empty($ResultsFixedPrices)) {
			// filter products that has more then 1 row in database
			foreach ($ResultsFixedPrices as $ResultItem) {
				// if were found more than one product in the products list, get only the first found product
				if (!isset($FilteredProducts[$ResultItem['product_code']])) {
					$FilteredProducts[$ResultItem['product_code']] = $ResultItem;
				}
			}
		}

		return $FilteredProducts;
    }


    /**
     * Apply fixed price to product
     * @param Mage_Catalog_Model_Product $Product
     * @param array $FixedPrice
     */
    public function insertFixedPriceToProduct(Mage_Catalog_Model_Product $Product, array $FixedPrice){

        if(!empty($FixedPrice) && ($FixedPrice['price'] != $Product->getPrice()) && ($FixedPrice['product_code'] == $Product->getSku())){
            // Change all prices for product
            $this->changeProductPrices($Product, $FixedPrice);
        }
    }

    /**
     * Change all prices for product
     * @param Mage_Catalog_Model_Product $Product
     * @param array $FixedPrice
     */
    protected function changeProductPrices(Mage_Catalog_Model_Product $Product, array $FixedPrice) {

        $Product->setPrice($FixedPrice['price']);
        $Product->setSpecialPrice($FixedPrice['special_price']);
        $Product->setSpecialFromDate($FixedPrice['date_from']);
        $Product->setSpecialToDate($FixedPrice['date_to']);
        $Product->setGlobaleFixedPrices(true);
    }

	/**
	 * Load array of price/special_price of fixed price of the product
	 * GEM usage
	 * @param $ProductCode
	 * @param $CurrencyCode
	 * @param $CountryCode
	 * @return array
	 */
    public function loadSingleProductFixedPrices($ProductCode,$CurrencyCode, $CountryCode){
		$FixedPriceRows = $this->FilterProductFixedPrices(array($ProductCode), $CurrencyCode, $CountryCode);

		if(empty($FixedPriceRows) || !isset($FixedPriceRows[$ProductCode])){
			return array();
		}

		$SpecialPrice = $this->calculateSpecialPrice($FixedPriceRows[$ProductCode]);

		$ProductFixedPrices = array(
			'price'          => $FixedPriceRows[$ProductCode]['price'],
			'special_price'  => $SpecialPrice
		);
		return $ProductFixedPrices;
	}

	/**
	 * Calculate Special Price for FixedPrice Row
	 * @param array $FixedPriceRow
	 * @return float|null
	 */
	protected function calculateSpecialPrice(array $FixedPriceRow){

		if(empty($FixedPriceRow['special_price'])){
			return null;
		}
		$ProductPriceModel = Mage::getModel('catalog/product_type_price');

		$SpecialFixedPriceAmount = $ProductPriceModel::calculateSpecialPrice(
			$FixedPriceRow['price'],
			$FixedPriceRow['special_price'],
			$FixedPriceRow['date_from'],
			$FixedPriceRow['date_to']
		);
		return $SpecialFixedPriceAmount;
	}

    /**
     * Collects products fixed prices data
     * @param array $ProductList
     * @return array
     * Reply data format :
     * [
     *    "productSku001" => [
     *        "country" => [
     *            "IS" => ["regular_price":"0.0000", "special_price":"1.0000"],
     *            "IT" => ["regular_price":"2.0000"],
     *        ],
     *        "currency" => [
     *            "USD" => ["regular_price":"3.0000", "special_price":"4.0000"],
     *            "EUR" => ["regular_price":"5.0000"],
     *        ]
     *    ],
     *    ...
     * ]
     */
	protected function collectProductsWithFixedPrice(array $ProductList)
    {
        /**@var $FP_Collection Globale_FixedPrices_Model_Resource_Fixedprices_Collection */
        $FP_Collection = Mage::getModel('globale_fixedprices/fixedprices')
            ->getCollection()
            ->addFieldToFilter('product_code', array('in' => $ProductList));

        $ResultsFixedPrices = $FP_Collection->getData();

        $FilteredProducts = array();

        if (!empty($ResultsFixedPrices)) {

            $Now = new \DateTime();
            foreach ($ResultsFixedPrices as $ResultItem) {

                if (!$ResultItem['country_code'] && $ResultItem['currency_code']) {

                    $FilteredProducts[$ResultItem['product_code']]['currency'][$ResultItem['currency_code']][self::PRICE_TYPE_REGULAR] = $ResultItem['price'];

                    if ( $ResultItem['date_from'] && $ResultItem['date_to']) {
                        $DateStart = \DateTime::createFromFormat(self::DATE_FORMAT, $ResultItem['date_from']);
                        $DateEnd = \DateTime::createFromFormat(self::DATE_FORMAT, $ResultItem['date_to']);
                        if($DateStart <= $Now && $Now < $DateEnd) {
                            $FilteredProducts[$ResultItem['product_code']]['currency'][$ResultItem['currency_code']][self::PRICE_TYPE_SPECIAL] = $ResultItem['special_price'];
                        }
                    }
                }

                if ($ResultItem['country_code']) {

                    $FilteredProducts[$ResultItem['product_code']]['country'][$ResultItem['country_code']][self::PRICE_TYPE_REGULAR] = $ResultItem['price'];

                    if ( $ResultItem['date_from'] && $ResultItem['date_to']) {
                        $DateStart = \DateTime::createFromFormat(self::DATE_FORMAT, $ResultItem['date_from']);
                        $DateEnd = \DateTime::createFromFormat(self::DATE_FORMAT, $ResultItem['date_to']);
                        if($DateStart <= $Now && $Now < $DateEnd) {
                            $FilteredProducts[$ResultItem['product_code']]['country'][$ResultItem['country_code']][self::PRICE_TYPE_SPECIAL] = $ResultItem['special_price'];
                        }
                    }


                }
            }
        }
        return $FilteredProducts;
    }

    /**
     * Updates products with properties, that contain HTML attributes for fixed prices
     * @param array|Iterator $Products
     */
    public function updateProductsWithFixedPricesAttributes($Products){

        $ProductsCodes = array();
        foreach ($Products as $Product) {
            $ProductsCodes[] = $Product->getSku();
        }

        $FilteredProducts = $this->collectProductsWithFixedPrice($ProductsCodes);

        foreach ($Products as $Product) {
            if (isset($FilteredProducts[$Product->getSku()])) {
                $Product->setGlobaleRegularPriceAttribute(
                    $this->generateHtmlAttribute(
                        self::PRICE_TYPE_REGULAR,
                        $FilteredProducts[$Product->getSku()]
                    )
                );
                $Product->setGlobaleSpecialPriceAttribute(
                    $this->generateHtmlAttribute(
                        self::PRICE_TYPE_SPECIAL,
                        $FilteredProducts[$Product->getSku()]
                    )
                );
            }
        }

    }


    /**
     * Build HTML attribute for product for specified price type
     * @param $Type
     * @param array $ProductData
     * @return string
     */
    protected function generateHtmlAttribute($Type, array $ProductData)
    {

        $Attribute = '';

        switch ($Type) {
            case \Globale_FixedPrices_Model_Product::PRICE_TYPE_REGULAR:
                $PriceData = $this->arrangeData(self::PRICE_TYPE_REGULAR, $ProductData);
                $Attribute = sprintf(self::HTML_ATTRIBUTE_REGULAR, Mage::helper('core')->jsonEncode($PriceData));
                break;
            case \Globale_FixedPrices_Model_Product::PRICE_TYPE_SPECIAL:
                $PriceData = $this->arrangeData(self::PRICE_TYPE_SPECIAL, $ProductData);
                $Attribute = sprintf(self::HTML_ATTRIBUTE_SPECIAL, Mage::helper('core')->jsonEncode($PriceData));
                break;
            default:
                throw new \RuntimeException("Price type='$Type' not supported");
                break;
        }

        return $Attribute;
    }

    /**
     * Prepare price data array to suit JSON format of fixed price attribute
     * @param $Type
     * @param $ProductData
     * @return array
     */
    protected function arrangeData($Type, $ProductData)
    {

        $ResultData = array();

        if (isset($ProductData['currency']) && !empty($ProductData['currency'])){
            foreach ($ProductData['currency'] as $currencyCode => $currency) {
                if(isset($currency[$Type])) {
                    $ResultData['defaults'][$currencyCode] = $currency[$Type];
                }
            }
        }

        if (isset($ProductData['country']) && !empty($ProductData['country'])){
            foreach ($ProductData['country'] as $countryCode => $country) {
                if(isset($country[$Type])) {
                    $ResultData[$countryCode] = $country[$Type];
                }
            }
        }

        return $ResultData;
    }

}