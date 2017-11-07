<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common\Response;

/**
 * Class Merchant
 * @package GlobalE\SDK\Models
 */
class Merchant {

    /**
     * Public method for handling orders by calling an action method
     * @param $Data
     * @param Common\iHandleAction $Action
     * @param $ActionName
     * @return Response|Response\Order
     * @throws \Exception
     * @access public
     */
    public function handleOrder($Data, Common\iHandleAction $Action, $ActionName){

        $Data = Json::decode($Data);
        if (!isset($Data->MerchantGUID)) {
            Core\Log::log("Empty merchant GUID provided in $ActionName request from core.", Core\Log::LEVEL_WARNING);
            $Response = new Response(false, 'Empty merchant GUID provided.');

        } elseif ($this->IsNotValidMerchantGuid($Data->MerchantGUID)){
			Core\Log::log("Wrong merchant GUID provided in $ActionName request from core.", Core\Log::LEVEL_WARNING);
			$Response = new Response(false, 'Wrong merchant GUID provided - '.$Data->MerchantGUID);

		} else {
            try {
                Core\Profiler::startTimer("Merchant $ActionName handleAction");
                $Response = $Action->handleAction($Data);
                Core\Profiler::endTimer("Merchant $ActionName handleAction");
                if ($Response->getSuccess()) {
                    Core\Log::log("$ActionName successfully done.", Core\Log::LEVEL_INFO);
                } else {
                    Core\Log::log("$ActionName failed in merchant's  handle action with message: " . $Response->getMessage(), Core\Log::LEVEL_ERROR);
                }
            } catch (\Exception $e) {
                Core\Log::log("Exception in merchant's $ActionName handle action. " . $e->getMessage(), Core\Log::LEVEL_ERROR);
                $Response = new Response(false, "Exception in merchant's $ActionName handle action. "  . $e->getMessage());
            }
        }

        return $Response;
    }

    /**
     * Validate merchant GUID
     * @param string $MerchantGuid
     * @return bool
     * @access protected
     */
    public function IsNotValidMerchantGuid($MerchantGuid) {
        return (strtolower($MerchantGuid) !== strtolower(Core\Settings::get('MerchantGUID')));
    }
}