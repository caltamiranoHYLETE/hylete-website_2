<?php

class HyleteShipping_Shipping_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'hyleteshipping_shipping';
	protected $_request = null;

    /**
     * Returns available shipping rates for Inchoo Shipping carrier
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		$this->setRequest($request);

		$this->_result = $this->_getQuotes();

		return $this->getResult();
	}

	public function getResult()
	{
		return $this->_result;
	}

	public function setRequest(Mage_Shipping_Model_Rate_Request $request)
	{
		$this->_request = $request;

		//This is a generic object we can use to send to our service
		$r = new stdClass();

		$quote = null;
		//We need to get the quote to get some data about the order
		foreach ($request->getAllItems() as $item){
			$quote = $item->getQuote();
			break;
		}

		if ($quote) {

			$r->CustomerGroup = $quote->getCustomerGroupId();
			$r->AppliedRules = $quote->getAppliedRuleIds();
			$r->CouponCode = $quote->getCouponCode();

			$r->FreeShipping = false;
			if($request->getFreeShipping()) {
				$r->FreeShipping = true;
			}

			$items = array();
			foreach ($quote->getAllItems() as $prod) {

				$i = new stdClass();

				//The quote items don't have the categories so we need to load those up
				$model = Mage::getModel('catalog/product');
				$_product = $model->load($prod->getProductId());
				$categoryIds = $_product->getCategoryIds();
				$cats = implode(",", $categoryIds);

				$i->Sku = $prod->getSku();
				$i->Categories = $cats;
				$i->ProductId = $prod->getProductId();
				$i->Price = $prod->getBasePrice();
				$i->Discount = $prod->getBaseDiscountAmount();
				$i->Quantity = $prod->getQty();
				$i->Weight = $prod->getWeight();
				$i->FreeShipping = $prod->getFreeShipping();

				//Mage::log(print_r($prod), null, 'hylete-shipping.log', true);

				$items[] = $i;
			}

			$r->Items = $items;
		}

		if ($request->getDestCountryId()) {
			$destCountry = $request->getDestCountryId();
		} else {
			$destCountry = self::USA_COUNTRY_ID;
		}

		$r->Country = $destCountry;
		$r->CountryName = $this->_getCountryName($destCountry);

		if ($request->getDestRegionCode()) {
			$r->State = $request->getDestRegionCode();
		} else {
			$r->State = "";
		}

		if ($request->getDestPostcode()) {
			$r->ZipCode = $request->getDestPostcode();
		} else{
			$r->ZipCode = "";
		}

		$weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
		$r->WeightPounds = floor($weight);
		$r->WeightOunces = round(($weight-floor($weight)) * 16, 1);

		$r->Total = $request->getPackageValue();
		$r->TotalWithDiscount = $request->getPackageValueWithDiscount();

		$r->Subtotal = $request->getBaseSubtotalInclTax();

		$this->_rawRequest = $r;

		return $this;
	}

	//This parse the reponse from the webservice
	protected function _parseJson($response)
	{
		$result = Mage::getModel('shipping/rate_result');

		if (trim($response)!=='') {
			try{
				$array = Mage::helper('core')->jsonDecode($response);
				foreach ($array as $returnRate) {

					$rate = Mage::getModel('shipping/rate_result_method');
					$rate->setCarrier($returnRate["Code"]);
					$rate->setCarrierTitle($returnRate["Provider"]);
					$rate->setMethod($returnRate["Method"]);
					$rate->setMethodTitle($returnRate["MethodTitle"]);
					$rate->setCost($returnRate["Cost"]);
					$rate->setPrice($returnRate["Price"]);

					$result->append($rate);
				}
			} catch(Exception $e) {
				//There was an error getting a rate so we use a backup rate
				$rate = Mage::getModel('shipping/rate_result_method');
				$rate->setCarrier("hyleteshipping_shipping");
				$rate->setCarrierTitle("USPS");
				$rate->setMethod("hyleteshipping_shipping_USPSPRI");
				$rate->setMethodTitle("USPS Priority");
				$rate->setCost("5.99");
				$rate->setPrice("5.99");

				Mage::log($e->getMessage(), null, 'hylete-shipping-error.log', true);
				Mage::log($response, null, 'hylete-shipping-error.log', true);

				$result->append($rate);
			}
		}

		return $result;
	}

	protected function _getQuotes()
	{
		$r = $this->_rawRequest;

		$debugData = array('request' => $r);
		try {
			$client = new Zend_Http_Client();
			$client->setUri($this->getConfigData('api_url'));
			$client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
			$client->setHeaders('Content-type','application/json');

			$json = Mage::helper('core')->jsonEncode($r);
			$client->setParameterPost('data', $json);
			$response = $client->request(Zend_Http_Client::POST);

			$responseBody = $response->getBody();

			$debugData['result'] = $responseBody;

			//$this->_setCachedQuotes($request, $responseBody);
		} catch (Exception $e) {
			$debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
			$responseBody = '';

			Mage::log($e->getMessage(), null, 'hylete-shipping.log', true);
		}

		$this->_debug($debugData);

		return $this->_parseJson($responseBody);
	}

    public function getTrackingInfo($tracking)
    {
        $track = mage::getmodel('shipping/tracking_result_status');
        $track->setUrl('https://www.hylete.com/tracking?orderId='.$tracking);
        $track->setTracking($tracking);
        $track->setCarrier('hyleteshipping_shipping');
        $track->setCarrierTitle($this->getConfigData('shipping_method_title'));

        return $track;
    }
    
    public function isTrackingAvailable()
    {
        return true;
    }

	/**
	 * Returns Allowed shipping methods
	 *
	 * @return array
	 */
	public function getAllowedMethods()
	{
		return array(
			'standard'    =>  'Standard Delivery',
		);
	}

	/**
	 * Get Express rate object
	 *
	 * @return Mage_Shipping_Model_Rate_Result_Method
	 */
	protected function _getExpressRate()
	{
		/** @var Mage_Shipping_Model_Rate_Result_Method $rate */
		$rate = Mage::getModel('shipping/rate_result_method');

		$rate->setCarrier($this->_code);
		$rate->setCarrierTitle($this->getConfigData('title'));
		$rate->setMethod('express');
		$rate->setMethodTitle('Express Delivery');
		$rate->setPrice(12.3);
		$rate->setCost(0);

		return $rate;
	}

	/**
	 * Get Standard rate object
	 *
	 * @return Mage_Shipping_Model_Rate_Result_Method
	 */
	protected function _getStandardRate()
	{
		/** @var Mage_Shipping_Model_Rate_Result_Method $rate */
		$rate = Mage::getModel('shipping/rate_result_method');

		$baseRate = $this->getConfigData('base_price');
		if($baseRate == "") {
			$baseRate= 5.99;
		}

		$rate->setCarrier($this->_code);
		$rate->setCarrierTitle($this->getConfigData('title'));
		$rate->setMethod('parcel_select');
		$rate->setMethodTitle($this->getConfigData('shipping_method_title'));
		$rate->setPrice($baseRate);
		$rate->setCost(0);

		return $rate;
	}

	protected function _getFreeShippingRate()
	{
		$rate = Mage::getModel('shipping/rate_result_method');
		/* @var $rate Mage_Shipping_Model_Rate_Result_Method */
		$rate->setCarrier($this->_code);
		$rate->setCarrierTitle($this->getConfigData('title'));
		$rate->setMethod('parcel_select');
		$rate->setMethodTitle($this->getConfigData('free_shipping_title'));
		$rate->setPrice(0);
		$rate->setCost(0);
		return $rate;
	}

	protected function _getCountryName($countryId)
	{
		$countries = array (
			'AD' => 'Andorra',
			'AE' => 'United Arab Emirates',
			'AF' => 'Afghanistan',
			'AG' => 'Antigua and Barbuda',
			'AI' => 'Anguilla',
			'AL' => 'Albania',
			'AM' => 'Armenia',
			'AN' => 'Netherlands Antilles',
			'AO' => 'Angola',
			'AR' => 'Argentina',
			'AT' => 'Austria',
			'AU' => 'Australia',
			'AW' => 'Aruba',
			'AX' => 'Aland Island (Finland)',
			'AZ' => 'Azerbaijan',
			'BA' => 'Bosnia-Herzegovina',
			'BB' => 'Barbados',
			'BD' => 'Bangladesh',
			'BE' => 'Belgium',
			'BF' => 'Burkina Faso',
			'BG' => 'Bulgaria',
			'BH' => 'Bahrain',
			'BI' => 'Burundi',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BN' => 'Brunei Darussalam',
			'BO' => 'Bolivia',
			'BR' => 'Brazil',
			'BS' => 'Bahamas',
			'BT' => 'Bhutan',
			'BW' => 'Botswana',
			'BY' => 'Belarus',
			'BZ' => 'Belize',
			'CA' => 'Canada',
			'CC' => 'Cocos Island (Australia)',
			'CD' => 'Congo, Democratic Republic of the',
			'CF' => 'Central African Republic',
			'CG' => 'Congo, Republic of the',
			'CH' => 'Switzerland',
			'CI' => 'Ivory Coast (Cote d Ivoire)',
			'CK' => 'Cook Islands (New Zealand)',
			'CL' => 'Chile',
			'CM' => 'Cameroon',
			'CN' => 'China',
			'CO' => 'Colombia',
			'CR' => 'Costa Rica',
			'CU' => 'Cuba',
			'CV' => 'Cape Verde',
			'CX' => 'Christmas Island (Australia)',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DE' => 'Germany',
			'DJ' => 'Djibouti',
			'DK' => 'Denmark',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'DZ' => 'Algeria',
			'EC' => 'Ecuador',
			'EE' => 'Estonia',
			'EG' => 'Egypt',
			'ER' => 'Eritrea',
			'ES' => 'Spain',
			'ET' => 'Ethiopia',
			'FI' => 'Finland',
			'FJ' => 'Fiji',
			'FK' => 'Falkland Islands',
			'FM' => 'Micronesia, Federated States of',
			'FO' => 'Faroe Islands',
			'FR' => 'France',
			'GA' => 'Gabon',
			'GB' => 'Great Britain and Northern Ireland',
			'GD' => 'Grenada',
			'GE' => 'Georgia, Republic of',
			'GF' => 'French Guiana',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GM' => 'Gambia',
			'GN' => 'Guinea',
			'GP' => 'Guadeloupe',
			'GQ' => 'Equatorial Guinea',
			'GR' => 'Greece',
			'GS' => 'South Georgia (Falkland Islands)',
			'GT' => 'Guatemala',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HK' => 'Hong Kong',
			'HN' => 'Honduras',
			'HR' => 'Croatia',
			'HT' => 'Haiti',
			'HU' => 'Hungary',
			'ID' => 'Indonesia',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IN' => 'India',
			'IQ' => 'Iraq',
			'IR' => 'Iran',
			'IS' => 'Iceland',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JO' => 'Jordan',
			'JP' => 'Japan',
			'KE' => 'Kenya',
			'KG' => 'Kyrgyzstan',
			'KH' => 'Cambodia',
			'KI' => 'Kiribati',
			'KM' => 'Comoros',
			'KN' => 'Saint Kitts (Saint Christopher and Nevis)',
			'KP' => 'North Korea (Korea, Democratic People\'s Republic of)',
			'KR' => 'South Korea (Korea, Republic of)',
			'KW' => 'Kuwait',
			'KY' => 'Cayman Islands',
			'KZ' => 'Kazakhstan',
			'LA' => 'Laos',
			'LB' => 'Lebanon',
			'LC' => 'Saint Lucia',
			'LI' => 'Liechtenstein',
			'LK' => 'Sri Lanka',
			'LR' => 'Liberia',
			'LS' => 'Lesotho',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'LV' => 'Latvia',
			'LY' => 'Libya',
			'MA' => 'Morocco',
			'MC' => 'Monaco (France)',
			'MD' => 'Moldova',
			'MG' => 'Madagascar',
			'MK' => 'Macedonia, Republic of',
			'ML' => 'Mali',
			'MM' => 'Burma',
			'MN' => 'Mongolia',
			'MO' => 'Macao',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MS' => 'Montserrat',
			'MT' => 'Malta',
			'MU' => 'Mauritius',
			'MV' => 'Maldives',
			'MW' => 'Malawi',
			'MX' => 'Mexico',
			'MY' => 'Malaysia',
			'MZ' => 'Mozambique',
			'NA' => 'Namibia',
			'NC' => 'New Caledonia',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NI' => 'Nicaragua',
			'NL' => 'Netherlands',
			'NO' => 'Norway',
			'NP' => 'Nepal',
			'NR' => 'Nauru',
			'NZ' => 'New Zealand',
			'OM' => 'Oman',
			'PA' => 'Panama',
			'PE' => 'Peru',
			'PF' => 'French Polynesia',
			'PG' => 'Papua New Guinea',
			'PH' => 'Philippines',
			'PK' => 'Pakistan',
			'PL' => 'Poland',
			'PM' => 'Saint Pierre and Miquelon',
			'PN' => 'Pitcairn Island',
			'PT' => 'Portugal',
			'PY' => 'Paraguay',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RS' => 'Serbia',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'SA' => 'Saudi Arabia',
			'SB' => 'Solomon Islands',
			'SC' => 'Seychelles',
			'SD' => 'Sudan',
			'SE' => 'Sweden',
			'SG' => 'Singapore',
			'SH' => 'Saint Helena',
			'SI' => 'Slovenia',
			'SK' => 'Slovak Republic',
			'SL' => 'Sierra Leone',
			'SM' => 'San Marino',
			'SN' => 'Senegal',
			'SO' => 'Somalia',
			'SR' => 'Suriname',
			'ST' => 'Sao Tome and Principe',
			'SV' => 'El Salvador',
			'SY' => 'Syrian Arab Republic',
			'SZ' => 'Swaziland',
			'TC' => 'Turks and Caicos Islands',
			'TD' => 'Chad',
			'TG' => 'Togo',
			'TH' => 'Thailand',
			'TJ' => 'Tajikistan',
			'TK' => 'Tokelau (Union Group) (Western Samoa)',
			'TL' => 'East Timor (Timor-Leste, Democratic Republic of)',
			'TM' => 'Turkmenistan',
			'TN' => 'Tunisia',
			'TO' => 'Tonga',
			'TR' => 'Turkey',
			'TT' => 'Trinidad and Tobago',
			'TV' => 'Tuvalu',
			'TW' => 'Taiwan',
			'TZ' => 'Tanzania',
			'UA' => 'Ukraine',
			'UG' => 'Uganda',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VA' => 'Vatican City',
			'VC' => 'Saint Vincent and the Grenadines',
			'VE' => 'Venezuela',
			'VG' => 'British Virgin Islands',
			'VN' => 'Vietnam',
			'VU' => 'Vanuatu',
			'WF' => 'Wallis and Futuna Islands',
			'WS' => 'Western Samoa',
			'YE' => 'Yemen',
			'YT' => 'Mayotte (France)',
			'ZA' => 'South Africa',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
			'US' => 'United States',
		);

		if (isset($countries[$countryId])) {
			return $countries[$countryId];
		}

		return false;
	}

}