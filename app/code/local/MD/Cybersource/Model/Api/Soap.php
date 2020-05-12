<?php
    /**
    * Magedelight
    * Copyright (C) 2015 Magedelight <info@magedelight.com>
    *
    * NOTICE OF LICENSE
    *
    * This program is free software: you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation, either version 3 of the License, or
    * (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
    *
    * @category MD
    * @package MD_Cybersource
    * @copyright Copyright (c) 2015 Mage Delight (http://www.magedelight.com/)
    * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
    * @author Magedelight <info@magedelight.com>
    */
    class MD_Cybersource_Model_Api_Soap extends MD_Cybersource_Model_Api_Abstract
    {       
        protected $_orderRequest=array();
        protected $_request;

        public function createCustomerPaymentProfileRequest(){
            $inputData = $this->getInputData();
            $regionId = $inputData->getRegionId();
            $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
            $regionCode = ($regionId) ? Mage::getModel("directory/region")->load($regionId)->getCode(): $inputData->getState();
            $cardType=$this->_cardCode[$inputData->getcc_type()];  // md to get card id from card code
            $request=array('paySubscriptionCreateService'
                =>array(
                    'run'=>'true'
                ),
                'recurringSubscriptionInfo'
                =>array(
                    'frequency'=>'on-demand'
                ),
                'billTo'=>array(
                    'firstName'=>$inputData->getFirstname(),
                    'lastName'=>$inputData->getLastname(),
                    'street1'=>$inputData->getStreet(),
                    'city'=>$inputData->getCity(),
                    'state'=>$regionCode,
                    'postalCode'=>$inputData->getPostcode(),
                    'country'=>$inputData->getCountryId(),
                    'email'=>$inputData->getEmail(),
                    'customerID'=>$inputData->getcustomer_id()
                ),
                'card'=>array(
                    'accountNumber'=>$inputData->getcc_number(),
                    'expirationMonth'=>$inputData->getcc_exp_month(),
                    'expirationYear'=>$inputData->getcc_exp_year(),
                    'cardType'=>$cardType
                ),
                'purchaseTotals'=>array(
                    'currency'=>Mage::app()->getStore()->getCurrentCurrencyCode()        
                ),
                'merchantID'=>$this->_merchantId,
                'merchantReferenceCode'=>$this->_generateMerchantReferenceCode(), // md have to ask 2362015
            );
            $company=$inputData->getCompany();
            if(!empty($company)){
                $request['billTo']['company']= $company;
            }            
            if($this->_cvvEnabled){
                $request['card']['cvNumber'] = $inputData->getcc_cid();
            }
            if($this->_additionalfield){
                $request['merchantDefinedData']['field1'] = sprintf('Store URL : %s',Mage::getBaseUrl());
                $request['merchantDefinedData']['field2'] = sprintf('Store Name : %s',Mage::app()->getStore()->getName());
            }
            return $request;
        }

        public function createCustomerProfile(){
            $request = $this->createCustomerPaymentProfileRequest();
            $response = $this->_postRequest($request);
            return $response;
        }

        public function deleteCustomerPaymentProfile(){
            $request = $this->deleteCustomerPaymentProfileRequest();
            $response = $this->_postRequest($request);
            return $response;
        }  

        public function deleteCustomerPaymentProfileRequest(){
            $inputData = $this->getInputData();
            $request=array('recurringSubscriptionInfo'
                =>array(
                    'subscriptionID'=>$inputData->getCustomerSubscriptionId()
                ),
                'paySubscriptionDeleteService'
                =>array(
                    'run'=>'true'
                ),               
                'merchantID'=>$this->_merchantId,
                'merchantReferenceCode'=>$this->_generateMerchantReferenceCode(), // md have to ask 2362015
            );
            if($this->_additionalfield){
                $request['merchantDefinedData']['field1'] = sprintf('Store URL : %s',Mage::getBaseUrl());
                $request['merchantDefinedData']['field2'] = sprintf('Store Name : %s',Mage::app()->getStore()->getName());
            } 
            return $request;
        }     

        public function updateCustomerProfile(){
            $request = $this->updateCustomerProfileRequest();
            $response = $this->_postRequest($request);
            return $response;
        }

        public function updateCustomerProfileRequest(){

            $inputData = $this->getInputData();
            $regionId = $inputData->getRegionId();
            $regionCode = ($regionId) ? Mage::getModel("directory/region")->load($regionId)->getCode(): $inputData->getState();
            $cardType=$this->_cardCode[$inputData->getcc_type()];  // md to get card id from card code
            $cardUpdateCheck=$inputData->getcc_action();
            $request=array('recurringSubscriptionInfo'
                =>array(
                    'subscriptionID'=>$inputData->getCustomerSubscriptionId()
                ),
                'paySubscriptionUpdateService'
                =>array(
                    'run'=>'true'
                ),
                'billTo'=>array(
                    'firstName'=>$inputData->getFirstname(),
                    'lastName'=>$inputData->getLastname(),
                    'street1'=>$inputData->getStreet(),
                    'city'=>$inputData->getCity(),
                    'state'=>$regionCode,
                    'postalCode'=>$inputData->getPostcode(),
                    'country'=>$inputData->getCountryId(),
                    'email'=>$inputData->getEmail(),
                    'customerID'=>$inputData->getcustomer_id()
                ),                               
                'merchantID'=>$this->_merchantId,
                'merchantReferenceCode'=>$this->_generateMerchantReferenceCode(), // md have to ask 2362015
            );
            if($cardUpdateCheck!="existing"){
                $request['card']['accountNumber']=$inputData->getcc_number();
                $request['card']['expirationMonth']=$inputData->getcc_exp_month();
                $request['card']['expirationYear']=$inputData->getcc_exp_year();
                $request['card']['cardType']=$cardType;                
                if($this->_cvvEnabled){
                    $request['card']['cvNumber'] = $inputData->getcc_cid();
                }
            }
            $company=$inputData->getCompany();
            if(!empty($company)){
                $request['billTo']['company']= $company;
            }            
            if($this->_additionalfield){
                $request['merchantDefinedData']['field1'] = sprintf('Store URL : %s',Mage::getBaseUrl());
                $request['merchantDefinedData']['field2'] = sprintf('Store Name : %s',Mage::app()->getStore()->getName());
            }
            return $request;
        }

        public function createCustomerProfileFromTransaction($requestid){
            if(!empty($requestid)){
                $this->_request->merchantID = $this->_merchantId;
                $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();

                $paySubscriptionCreateService=new stdClass();
                $paySubscriptionCreateService->run="true";
                $paySubscriptionCreateService->paymentRequestID=$requestid;
                $this->_request->paySubscriptionCreateService=$paySubscriptionCreateService;

                $recurringSubscriptionInfo=new stdClass() ;
                $recurringSubscriptionInfo->frequency="on-demand";
                $this->_request->recurringSubscriptionInfo=$recurringSubscriptionInfo;

                $response = $this->_postRequest($this->_request);
                return $response;
            }
        }


        public function prepareCaptureResponse(Varien_Object $payment,$amount,$subscription=false){
            $this->_request = $this->prepareCaptureRequest($payment,$amount,$subscription);   
            $response = $this->_postRequest($this->_request);
            return $response;   
        }                

        public function prepareCaptureRequest(Varien_Object $payment,$amount,$subscription){
            $billingAddress=$payment->getOrder()->getBillingAddress();
            $shippingAddress=$payment->getOrder()->getShippingAddress(); 
            $this->_request->merchantID = $this->_merchantId;
            $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
            $ccAuthService = new stdClass();
            $ccAuthService->run = "true";
            $this->_request->ccAuthService = $ccAuthService;

            $ccCaptureService = new stdClass();
            $ccCaptureService->run = "true";
            $this->_request->ccCaptureService = $ccCaptureService;

            $customeremail=$payment->getOrder()->getCustomerEmail();
            if (!$customeremail) {
                $customeremail = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getEmail();
            }
            $this->createBillingAddressRequest($customeremail, $billingAddress);

            $this->createShippingAddressRequest($shippingAddress);

            if($subscription==false){
                $this->createCardInfoRequest($payment);
            }else{
                $subscription_info=new stdClass();         // md here we set subscerption id insted of card info
                $subscription_info->subscriptionID=$payment->getMdcybersourceSubscriptionId();
                $this->_request->recurringSubscriptionInfo = $subscription_info;
                if($this->_cvvEnabled){
                    $card = new stdClass();
                    $card->cvNumber = $payment->getcc_cid();
                    $this->_request->card = $card;                     
                }
            }

            $this->createItemInfoRequest($payment);

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $amount;
            if($payment->getBaseShippingAmount()){
                $purchaseTotals->additionalAmount0=(string)round( $payment->getBaseShippingAmount(), 4);
                $purchaseTotals->additionalAmountType0=(string)'055';
            }

            $this->_request->purchaseTotals = $purchaseTotals;

            if($this->_additionalfield){
                $this->getAdditionalData($payment);    
            }
            return  $this->_request;
        }

        protected function createItemInfoRequest(Varien_Object $payment,$quantity=false){
            if(is_object($payment)){
                $order = $payment->getOrder();
                if($order instanceof Mage_Sales_Model_Order){                    
                    $i=0;
                    foreach($order->getAllVisibleItems() as $_item){
                        $item=new stdClass();
                        $item->unitPrice=round($_item->getBasePrice(),2);
                        $item->taxAmount=round($_item->getData('tax_amount'),2);
                        $quantity==false?$item->quantity=(int) $_item->getQtyOrdered():"";
                        $item->productName=substr($_item->getName(),0,30);
                        $item->productSKU=$_item->getSku();
                        $item->id = $i;                        
                        $this->_request->item[$i] = $item;
                        $i++;
                    }
                }    
            } 
        }

        protected function createBillingAddressRequest($customeremail,$billing){
            if (!$customeremail) {
                $customeremail = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getEmail();
            }
            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerData = Mage::getSingleton('customer/session')->getCustomer();
                $customerId=$customerData->getId();
            }
            $billTo = new stdClass();
            $billTo->firstName = $billing->getFirstname();
            $billTo->lastName = $billing->getLastname();
            $billTo->company = $billing->getCompany();
            $billTo->street1 = $billing->getStreet(1);
            $billTo->street2 = $billing->getStreet(2);
            $billTo->city = $billing->getCity();
            $billTo->state = $billing->getRegion();
            $billTo->postalCode = $billing->getPostcode();
            $billTo->country = $billing->getCountry();
            $billTo->phoneNumber = $billing->getTelephone();
            $billTo->email = $customeremail ;
            $billTo->ipAddress = $this->getIpAddress();
            if($customerId){
                $billTo->customerID = $customerId;
            }
            $this->_request->billTo = $billTo;                           
        }

        protected function createShippingAddressRequest($shipping){
            if ($shipping) {
                $shipTo = new stdClass();
                $shipTo->firstName = $shipping->getFirstname();
                $shipTo->lastName = $shipping->getLastname();
                $shipTo->company = $shipping->getCompany();
                $shipTo->street1 = $shipping->getStreet(1);
                $shipTo->street2 = $shipping->getStreet(2);
                $shipTo->city = $shipping->getCity();
                $shipTo->state = $shipping->getRegion();
                $shipTo->postalCode = $shipping->getPostcode();
                $shipTo->country = $shipping->getCountry();
                $shipTo->phoneNumber = $shipping->getTelephone();
                $this->_request->shipTo = $shipTo;
            }
        }

        protected function createCardInfoRequest($payment){
            if(is_object($payment)){
                $post = Mage::app()->getRequest()->getParam('payment');
                $ccNumber=$payment->getCcNumber();
                $expMonth=$payment->getCcExpMonth();
                $expYear=$payment->getCcExpYear();
                $ccType=$payment->getcc_type();

                $cardType=$this->_cardCode[empty($ccType)?$post['cc_type']:$ccType];  // md to get card id from card code
                $card = new stdClass();
                $card->accountNumber = empty($ccNumber)?$post['cc_number']:$ccNumber;
                $card->expirationMonth = empty($expMonth)?$post['cc_exp_month']:$expMonth;
                $card->expirationYear =  empty($expYear)?$post['cc_exp_year']:$expYear;
                $card->cardType = $cardType;
                if($this->_cvvEnabled){
                    $ccId=$payment->getcc_cid();
                    $card->cvNumber = empty($ccId)?$post['cc_cid']:$ccId;
                }
                $this->_request->card = $card;
            }            
        }

        protected function getAdditionalData($payment){
            if(is_object($payment)){
                $helper=Mage::helper('md_cybersource');
                $additonalData=array(
                    'store_url'=>Mage::getBaseUrl(),
                    'store_name'=>strip_tags($payment->getOrder()->getStoreName()),
                    'order_id'=>$payment->getOrder()->getIncrementId(),
                    'shipping_amount'=>round( $payment->getBaseShippingAmount(),4),
                    'shipping_name'=>$payment->getOrder()->getShippingDescription(),
                    'discount'=>$payment->getOrder()->getDiscountAmount(),
                    'coupon'=>$payment->getOrder()->getCouponCode()
                );
                $requiredAdditionalData=array(
                    $this->_additionalfield1,
                    $this->_additionalfield2,
                    $this->_additionalfield3,
                    $this->_additionalfield4,
                    $this->_additionalfield5,
                    $this->_additionalfield6,
                    $this->_additionalfield7
                );
                $additonalDataLabel=array(
                    'store_url'=>$helper->__('Store URL :'),
                    'store_name'=>$helper->__('Store Name :'),
                    'order_id'=>$helper->__('Order ID # :'),
                    'shipping_amount'=>$helper->__('Shipping Amount :'),
                    'shipping_name'=>$helper->__('Shipping Method Name :'),
                    'discount'=>$helper->__('Discount Amount :'),
                    'coupon'=>$helper->__('Coupon Code :'),
                );
                $merchantDefinedata=new stdClass(); 
                $count=0;     
                $requiredAdditionalData= array_values(array_filter($requiredAdditionalData));
                for($t=1;$t<=count($requiredAdditionalData);$t++){                    
                    if((!empty($additonalData[$requiredAdditionalData[$count]]) && $additonalData[$requiredAdditionalData[$count]] !='') || $requiredAdditionalData[$count]=='shipping_amount'){
                        $merchantDefinedata->{'field'.$t}=$additonalDataLabel[$requiredAdditionalData[$count]].' '.$additonalData[$requiredAdditionalData[$count]];
                        $count++;
                    }
                }
                $this->_request->merchantDefinedData = $merchantDefinedata;
            }
        }

        public function prepareAuthorizeResponse(Varien_Object $payment,$amount,$subscription=false){
            $this->_request = $this->prepareAuthorizeRequest($payment,$amount,$subscription);   
            $response = $this->_postRequest($this->_request);
            return $response;   
        }

        public function prepareAuthorizeRequest(Varien_Object $payment,$amount,$subscription){
            $billingAddress=$payment->getOrder()->getBillingAddress();
            $shippingAddress=$payment->getOrder()->getShippingAddress(); 
            $this->_request->merchantID = $this->_merchantId;
            $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
            $ccAuthService = new stdClass();
            $ccAuthService->run = "true";
            $this->_request->ccAuthService = $ccAuthService;

            $customeremail=$payment->getOrder()->getCustomerEmail();
            if (!$customeremail) {
                $customeremail = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getEmail();
            }
            $this->createBillingAddressRequest($customeremail, $billingAddress);

            $this->createShippingAddressRequest($shippingAddress);

            if($subscription==false){
                $this->createCardInfoRequest($payment);
            }else{
                $subscription_info=new stdClass();         // md here we set subscerption id insted of card info
                $subscription_info->subscriptionID=$payment->getMdcybersourceSubscriptionId();
                $this->_request->recurringSubscriptionInfo = $subscription_info;
                if($this->_cvvEnabled){
                    $card = new stdClass();
                    $card->cvNumber = $payment->getcc_cid();
                    $this->_request->card = $card;                     
                }
            }

            $this->createItemInfoRequest($payment);

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $amount;
            if($payment->getBaseShippingAmount()){
                $purchaseTotals->additionalAmount0=(string)round( $payment->getBaseShippingAmount(), 4);
                $purchaseTotals->additionalAmountType0=(string)'055';
            }

            $this->_request->purchaseTotals = $purchaseTotals;

            if($this->_additionalfield){
                $this->getAdditionalData($payment);    
            }
            return  $this->_request;
        }

        public function prepareAuthorizeCaptureResponse(Varien_Object $payment,$amount,$subscription=false){
            $this->_request = $this->prepareAuthorizeCaptureRequest($payment,$amount,$subscription);   
            $response = $this->_postRequest($this->_request);
            return $response;    
        }

        public function prepareAuthorizeCaptureRequest(Varien_Object $payment,$amount,$subscription){
            $billingAddress=$payment->getOrder()->getBillingAddress();
            $shippingAddress=$payment->getOrder()->getShippingAddress(); 
            $this->_request->merchantID = $this->_merchantId;
            $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
            $csCaptureService = new stdClass();
            $csCaptureService->run = "true";
            $csCaptureService->authRequestToken = $payment->getCybersourceToken();
            $csCaptureService->authRequestID = $payment->getmdCybersourceRequestid();
            $this->_request->ccCaptureService = $csCaptureService;
            $item0 = new stdClass();
            $item0->unitPrice = $amount;
            $item0->id = 0;
            $this->_request->item = array($item0);

            $customeremail=$payment->getOrder()->getCustomerEmail();

            $this->createBillingAddressRequest($customeremail, $billingAddress);

            $this->createShippingAddressRequest($shippingAddress);

            $this->createItemInfoRequest($payment);

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $amount;
            if($payment->getBaseShippingAmount()){
                $purchaseTotals->additionalAmount0=(string)round( $payment->getBaseShippingAmount(), 4);
                $purchaseTotals->additionalAmountType0=(string)'055';
            }

            $this->_request->purchaseTotals = $purchaseTotals;

            if($this->_additionalfield){
                $this->getAdditionalData($payment);    
            }

            return  $this->_request;
        }

        public function prepareVoidResponse(Varien_Object $payment,$card){
            $billingAddress=$payment->getOrder()->getBillingAddress();
            $this->_request->merchantID = $this->_merchantId;
            $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();

            $ccAuthReversalService = new stdClass();
            $ccAuthReversalService->run = "true";
            $ccAuthReversalService->authRequestID = (string)$payment->getParentTransactionId();
            $ccAuthReversalService->authRequestToken = (string)$payment->getCybersourceToken();
            $this->_request->ccAuthReversalService = $ccAuthReversalService;                       

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $payment->getBaseAmountAuthorized();
            $this->_request->purchaseTotals = $purchaseTotals;


            $customeremail=$payment->getOrder()->getCustomerEmail();

            $this->createBillingAddressRequest($customeremail, $billingAddress);


            $this->_request->merchantDefinedData = $merchantDefinedata;

            if($this->_additionalfield){
                $this->getAdditionalData($payment);    
            }

            $response = $this->_postRequest($this->_request);
            return $response;   

        }

        public function prepareRefundResponse(Varien_Object $payment,$amount,$realCaptureTransactionId){
            $this->_request = $this->prepareRefundRequest($payment,$amount,$realCaptureTransactionId);   
            $response = $this->_postRequest($this->_request);
            return $response;  
        }

        protected function prepareRefundRequest(Varien_Object $payment,$amount,$realCaptureTransactionId){
            $billingAddress=$payment->getOrder()->getBillingAddress();
            $shippingAddress=$payment->getOrder()->getShippingAddress(); 
            if(empty($realCaptureTransactionId)){
                $credit_memo = Mage::registry('current_creditmemo');        
                $captureTransactionId = $credit_memo->getInvoice()->getTransactionId();
                $captureTransaction = $payment->getTransaction($captureTransactionId);
                $realCaptureTransactionId = $captureTransaction->getAdditionalInformation('real_transaction_id');
            }

            $this->_request->merchantID = $this->_merchantId;
            $this->_request->merchantReferenceCode = $this->_generateMerchantReferenceCode();
            $ccCreditService = new stdClass();
            $ccCreditService->run = (string) "true";
            $ccCreditService->captureRequestID =(string) $realCaptureTransactionId;
            $ccCreditService->captureRequestToken = (string) $payment->getCybersourceToken();
            $this->_request->ccCreditService = $ccCreditService;


            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $amount;            
            $this->_request->purchaseTotals = $purchaseTotals;

            $customeremail=$payment->getOrder()->getCustomerEmail();

            $this->createBillingAddressRequest($customeremail, $billingAddress);

            $this->createShippingAddressRequest($shippingAddress);

            $this->createItemInfoRequest($payment);

            if($this->_additionalfield){
                $this->getAdditionalData($payment);    
            }

            return  $this->_request;
        }

        protected function getIpAddress(){
            return Mage::helper('core/http')->getRemoteAddr();
        }

        public function _postRequest($request,$type=null){
            return;
            $debug = array();
            $client= new MD_Cybersource_Model_Api_Soapclient();  // md for ws security we have to override soap  because we have to add username token in soap request         
            try{
                $response = $client->runTransaction($request);
            }catch(SoapFault $sf){
                Mage::log("Cybersource SOAP Request Error  : => $sf", null, 'MD_Cybersource_SOAPError.log', true);                                                
                Mage::throwException(Mage::helper('md_cybersource')->__('local Soap request error due to invalid configuration.'));
            }catch(Exception $e){
                $message=$e->getMessage();
                Mage::log("Cybersource Exception Error Due to  : => $message", null, 'MD_Cybersource_Error.log', true);                                                
                Mage::throwException(Mage::helper('md_cybersource')->__('Error when request for payment process.'));
            }

            if($this->getConfigModel()->getIsDebugEnabled()){
                $this->prepareSoapForDebug($request,$response);
            }
            return $response;
        } 


        protected function _generateMerchantReferenceCode(){
            return Mage::helper('core')->uniqHash();
        }

        public function prepareSoapForDebug($request,$response){            
            $requstArray = json_decode(json_encode($request), true);
            $responseArray=json_decode(json_encode($response), true);
            $cybersourceRequest = new SimpleXMLElement("<?xml version=\"1.0\"?><cybersource_request_info></cybersource_request_info>");
            $this->array_to_xml($requstArray,$cybersourceRequest);
            $cybersourceRequestXMLFile = $cybersourceRequest->asXML();
            $dom = new DOMDocument();
            $dom->loadXML($cybersourceRequestXMLFile);
            $dom->formatOutput = true;
            $RequestXML .= "Request:\n\n";
            $RequestXML .= $dom->saveXML();
            Mage::log($RequestXML,false,'md_cybersource.log');
            $cybersourceResponse=new SimpleXMLElement("<?xml version=\"1.0\"?><cybersource_response_info></cybersource_response_info>");
            $this->array_to_xml($responseArray,$cybersourceResponse);
            $cybersourceResponseXMLFile = $cybersourceResponse->asXML();            
            $dom = new DOMDocument();
            $dom->loadXML($cybersourceResponseXMLFile);
            $dom->formatOutput = true;
            $ResponseXML .= "Response:\n\n";
            $ResponseXML .= $dom->saveXML();
            Mage::log($ResponseXML,false,'md_cybersource.log');
        } 

        public function array_to_xml($array, &$xml_user_info) {
            foreach($array as $key => $value) {
                if(is_array($value)) {
                    if(!is_numeric($key)){
                        $subnode = $xml_user_info->addChild("$key");
                        $this->array_to_xml($value, $subnode);
                    }else{
                        $subnode = $xml_user_info->addChild("item$key");
                        $this->array_to_xml($value, $subnode);
                    }
                }else {
                    if($key=="accountNumber"){      // md 672015 for security reasion we cant log sensitive inbfromation so we have put  XXXX
                        $value=substr($value,-4,4); 
                        $value="XXXX-". $value;
                    }
                    if($key=="expirationMonth"){
                        $value="XX";
                    }
                    if($key=="expirationYear"){
                        $value="XXXX";
                    }
                    if($key=="cvNumber"){
                        $value="XX";
                    }
                    if($key=="cardType"){
                        $value="XX";
                    }
                    if($key=="merchantID"){
                        $value="XXXX";
                    }
                    $xml_user_info->addChild("$key",htmlspecialchars("$value"));
                }
            }
        }
}