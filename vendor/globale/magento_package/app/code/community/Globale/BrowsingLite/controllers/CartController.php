<?php
use GlobalE\SDK\Models\Common;

class Globale_BrowsingLite_CartController extends Mage_Core_Controller_Front_Action {


	/**
	 * Get cart Info from GEM side
	 */
	public function getInfoAction(){

		if(Mage::helper('core')->isModuleEnabled('Globale_Browsing')) {

			//if Globale_Browsing is on - return error message
			$CartInfo = new Common\Response\GetCartError();
			$CartInfo->setErrorMessage('This type of browsing mode not supported - Globale_Browsing module is active');
		}else{
			$CartInfo = $this->getCartInfo();
		}

		$ResponseBody = json_encode($CartInfo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/json')
			->setBody($ResponseBody);
	}

	/**
	 * Build GetCart object
	 * @return \GlobalE\SDK\Models\Common\Response\GetCart
	 */
	protected function getCartInfo(){
		$CartId = $this->getRequest()->getParam('CartId');

		//Set Current Store according to StoreCode request parameter
		$StoreCode = $this->getRequest()->getParam('StoreCode');
		if($StoreCode){
			Mage::app()->setCurrentStore($StoreCode);
		}

		//'MerchantGUID' - sent in case of server call
		$MerchantGUID = $this->getRequest()->getParam('MerchantGUID');

		//fixed price params
		$FixedPriceCountry = $this->getRequest()->getParam('Country');
		$FixedPriceCurrency = $this->getRequest()->getParam('Currency');


		/** @var Globale_BrowsingLite_Model_Cart $CartModel */
		$CartModel = Mage::getModel('globale_browsinglite/cart');
		$CartInfo = $CartModel->getInfo($CartId,$FixedPriceCountry,$FixedPriceCurrency, $MerchantGUID);


		return $CartInfo;

	}

    /**
     * Proxy GEM /checkout/GetCartToken through Magento to insert Loyalty to Params.
     * @return string $Response
     */
    public function getTokenAction(){

        $Params = $this->getRequest()->getParams();
        /** @var Globale_BrowsingLite_Model_Cart $CartModel */
        $CartModel = Mage::getModel('globale_browsinglite/cart');
        $CartToken = $CartModel->getCartToken($Params);



	    $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/javascript')
            ->setBody($CartToken);

    }

}