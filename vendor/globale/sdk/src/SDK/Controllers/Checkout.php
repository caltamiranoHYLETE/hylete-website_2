<?php
namespace GlobalE\SDK\Controllers;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\Models\Common\Request;


/**
 * Class Checkout
 * Interface Methods
 * @package GlobalE\SDK\Controllers
 */
class Checkout extends BaseController {

    /**
     * Send cart request
     * @param Request\SendCart $SendCartRequest
     * @return Response
     */
    protected function SendCart(Request\SendCart $SendCartRequest){

        /**@var $Customer Models\Customer */
        $Customer = Models\Customer::getSingleton();
        if($Customer->IsUserSupportedByGlobale()) {

            $ModelCheckout = new Models\Checkout();
            $ApiParams = $ModelCheckout->buildApiParamsForSendCart($SendCartRequest);

			Core\Log::log('SendCart Request '.Models\Json::encode($ApiParams), Core\Log::LEVEL_INFO);

			$Processor = $ModelCheckout->getSendCartProcessor($ApiParams);
            $ResponseData = $Processor->processRequest();
            $ResponseData = $ResponseData[0];

            if (isset($ResponseData->CartToken)) {
                $_SESSION['GlobalE_CartToken'] = $ResponseData->CartToken;

                Core\Log::log('SendCart succeeded' .json_encode($ResponseData), Core\Log::LEVEL_INFO );
                return new Response\Data(true, $ResponseData);
            } else {
                Core\Log::log('SendCart failed'.json_encode($ResponseData), Core\Log::LEVEL_CRITICAL, (array)$ResponseData);
                return new Response\Data(false, $ResponseData);
            }
        } else {
            $Msg = "User's country is NOT supported by Global-E.";
            /**@var $Customer Models\Customer */
            $Customer =  Models\Customer::getSingleton();
            Core\Log::log("Can't send cart: $Msg", Core\Log::LEVEL_NOTICE, (array) $Customer->getInfo());
            return new Response(false, $Msg);
        }
    }

    /**
     * Generate Checkout page
     * @param string $CartToken
     * @param string $ContainerId
     * @return Response
     */
    protected function GenerateCheckoutPage($CartToken = null, $ContainerId = 'checkoutContainer'){

        /**@var $Customer Models\Customer */
        $Customer =  Models\Customer::getSingleton();

        if ($Customer->IsUserSupportedByGlobale()) {
            $Checkout = new Models\Checkout();
            $CheckoutString = $Checkout->generateCheckout($CartToken, $ContainerId);
            return new Response\Data(true, $CheckoutString);
        } else {
            $Msg = "User's country is NOT supported by Global-E.";
            Core\Log::log("Can't generate checkout page: $Msg", Core\Log::LEVEL_NOTICE, $Customer->getInfo());
            return new Response(false, $Msg);
        }
    }
}