<?php

use GlobalE\SDK;
use GlobalE\SDK\Models\Common;


class Globale_Browsing_Helper_Price {

	/**
	 * Convert Amount According to Global-e logic with zeto tax rate
	 * @param float $Amount
	 * @param boolean $UseRounding default true
	 * @return float
	 */
	public function convertAmount($Amount, $UseRounding = true){
		if(empty($Amount)){
			return $Amount;
		}
		/**@var $GlobaleSDK SDK\SDK */
		$GlobaleSDK = Mage::registry('globale_sdk');

		//we don't know tax settings so using 0 tax
		$Rate = 0;
		$Name = 'Local Magento';
		$VATRateTypeCode ='Magento Zero Tax';

		$LocalVATRateType = new Common\VatRateType($Rate, $Name, $VATRateTypeCode);
		$RawPriceRequestData = $this->buildRawPriceRequestData($Amount, $LocalVATRateType);

		$PriceIncludesVAT = Globale_Browsing_Model_Product::isPriceIncludesVAT();

		$SDKResponse = $GlobaleSDK->Browsing()->GetCalculatedRawPrice(array($RawPriceRequestData),$PriceIncludesVAT,$UseRounding);

		if ($SDKResponse->getSuccess()) {
			/**@var $SDKResponse  Common\ProductResponseData[] */
			$Amount = $SDKResponse->getData()[0]->getSalePrice();
		}
		return $Amount;
	}


	/**
	 * Build RawPriceRequestData object for "Amount" item
	 * @param float $Price
	 * @param Common\VatRateType $LocalVATRateType
	 * @return Common\Request\RawPriceRequestData $ProductRequestData
	 */
	protected function buildRawPriceRequestData($Price, $LocalVATRateType){

		/**@var $globaleSDK SDK\SDK */
		$globaleSDK = Mage::registry('globale_sdk');
		$GlobalEVATRateType = $globaleSDK::$MerchantVatRateType;

		$RawPriceRequestData = new Common\Request\RawPriceRequestData();
		$RawPriceRequestData->setOriginalListPrice($Price);
		$RawPriceRequestData->setOriginalSalePrice($Price);
		$RawPriceRequestData->setIsFixedPrice(false);
		$RawPriceRequestData->setVATRateType($GlobalEVATRateType);
		$RawPriceRequestData->setLocalVATRateType($LocalVATRateType);

		return $RawPriceRequestData;
	}




}