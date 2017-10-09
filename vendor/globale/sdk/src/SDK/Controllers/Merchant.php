<?php
namespace GlobalE\SDK\Controllers;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Response;

/**
 * Class Merchant
 * Interface Methods
 * @package GlobalE\SDK\Controllers
 */
class Merchant extends BaseController {

     /**
      * Wrap merchant handle method order creation
      * @param string $Data
      * @param Common\iHandleAction $Action
      * @param bool $Output
      * @return Response
      */
     protected function HandleOrderCreation($Data, Common\iHandleAction $Action, $Output = true){
               
          $MerchantModel = new Models\Merchant();
          $Response = $MerchantModel->handleOrder($Data, $Action, __FUNCTION__);

          if ($Output) {
               echo $Response;
          }
          return $Response;
     }

     /**
      * Wrap merchant handle method order create payment
      * @param string $Data
      * @param Common\iHandleAction $Action
      * @param bool $Output
      * @return Response
      */
     protected function HandleOrderPayment($Data, Common\iHandleAction $Action, $Output = true){

          $MerchantModel = new Models\Merchant();
          $Response = $MerchantModel->handleOrder($Data, $Action, __FUNCTION__);

          if ($Output) {
               echo $Response;
          }
          return $Response;
     }

     /**
      * Wrap merchant handle method order status update
      * @param string $Data
      * @param Common\iHandleAction $Action
      * @param bool $Output
      * @return Response
      */
     protected function HandleOrderStatusUpdate($Data, Common\iHandleAction $Action, $Output = true){

          $MerchantModel = new Models\Merchant();
          $Response = $MerchantModel->handleOrder($Data, $Action, __FUNCTION__);

          if ($Output) {
               echo $Response;
          }
          return $Response;
     }

     /**
      * Wrap merchant handle method order shipping info update
      * @param string $Data
      * @param Common\iHandleAction $Action
      * @param bool $Output
      * @return Response
      */
     public function HandleOrderShippingInfo($Data, Common\iHandleAction $Action, $Output = true){

          $MerchantModel = new Models\Merchant();
          $Response = $MerchantModel->handleOrder($Data, $Action, __FUNCTION__);

          if ($Output) {
               echo $Response;
          }

          return $Response;
     }
}