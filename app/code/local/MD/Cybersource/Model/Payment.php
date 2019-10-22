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
    class MD_Cybersource_Model_Payment extends Mage_Payment_Model_Method_Cc
    {        
        protected $_formBlockType = 'md_cybersource/form';
        protected $_infoBlockType = 'md_cybersource/info';
        protected $_code = 'md_cybersource';        

        const RESPONSE_CODE_SUCCESS = 100;
        const CC_CARDTYPE_SS = 'SS';

        const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
        const REQUEST_TYPE_AUTH_ONLY    = 'AUTH_ONLY';
        const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
        const REQUEST_TYPE_CREDIT       = 'CREDIT';
        const REQUEST_TYPE_VOID         = 'VOID';
        const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';


        /**
        * Bit masks to specify different payment method checks.
        * @see Mage_Payment_Model_Method_Abstract::isApplicableToQuote
        */
        const CHECK_USE_FOR_COUNTRY       = 1;
        const CHECK_USE_FOR_CURRENCY      = 2;
        const CHECK_USE_CHECKOUT          = 4;
        const CHECK_USE_FOR_MULTISHIPPING = 8;
        const CHECK_USE_INTERNAL          = 16;
        const CHECK_ORDER_TOTAL_MIN_MAX   = 32;
        const CHECK_RECURRING_PROFILES    = 64;
        const CHECK_ZERO_TOTAL            = 128;

        protected $_isGateway = true;
        protected $_canAuthorize = true;
        protected $_canCapture = true;
        protected $_canRefund = true;
        protected $_canVoid = true;
        protected $_canUseInternal = true;
        protected $_canUseCheckout = true;
        protected $_canUseForMultishipping = true;
        protected $_canSaveCc = false; 
        protected $_canReviewPayment = false;
        protected $_canManageRecurringProfiles = false;
        protected $_canFetchTransactionInfo = true; 
        protected $_canCapturePartial = true;
        protected $_canRefundInvoicePartial = true;

        protected $_store = 0;
        protected $_customer = null;
        protected $_backend = false;
        protected $_configModel = null;
        protected $_invoice = null;
        protected $_creditmemo = null;

        const RESPONSE_CODE_APPROVED = 1;
        const RESPONSE_CODE_DECLINED = 2;
        const RESPONSE_CODE_ERROR    = 3;
        const RESPONSE_CODE_HELD     = 4;

        const RESPONSE_REASON_CODE_APPROVED = 1;
        const RESPONSE_REASON_CODE_NOT_FOUND = 16;
        const RESPONSE_REASON_CODE_PARTIAL_APPROVE = 295;
        const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;
        const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;
        const RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED = 254;

        const PARTIAL_AUTH_CARDS_LIMIT = 5;

        const PARTIAL_AUTH_LAST_SUCCESS         = 'last_success';
        const PARTIAL_AUTH_LAST_DECLINED        = 'last_declined';
        const PARTIAL_AUTH_ALL_CANCELED         = 'all_canceled';
        const PARTIAL_AUTH_CARDS_LIMIT_EXCEEDED = 'card_limit_exceeded';
        const PARTIAL_AUTH_DATA_CHANGED         = 'data_changed';
        const TRANSACTION_STATUS_EXPIRED = 'expired';

        protected $_isTransactionFraud = 'is_transaction_fraud';
        protected $_realTransactionIdKey = 'real_transaction_id';
        protected $_isGatewayActionsLockedKey = 'is_gateway_actions_locked';
        protected $_partialAuthorizationLastActionStateSessionKey = 'md_cybersource_last_action_state';
        protected $_partialAuthorizationChecksumSessionKey = 'md_cybersource_checksum';

        protected $_allowCurrencyCode = array();

        protected $_errorMessage=array(
            "100"=> "Successful transaction",
            "101"=>"The request is missing one or more required fields",
            "102"=>"One or more fields in the request contains invalid data",
            "104"=>"Resend the request with a unique merchant reference code",
            "110"=>"Only a partial amount was approved",
            "150"=>"General system failure",
            "151"=>"The request was received but there was a server timeout. This error does not include timeouts between the client and the server",
            "152"=>"The request was received, but a service did not finish running in time",
            "200"=>"The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the Address Verification System check",
            "201"=>"The issuing bank has questions about the request. You will not receive an authorization code programmatically, but you can obtain one verbally by calling the processor",
            "202"=>"Expired card,Request a different card or other form of payment",
            "203"=>"General decline of the card. Request a different card or other form of payment",
            "204"=>"Insufficient funds in the account,Request a different card or other form of payment",
            "205"=>"Stolen or lost card",
            "207"=>"Issuing bank unavailable,Wait a few minutes and resend the request",
            "208"=>"Inactive card or card not authorized for card-not-present transactions,Request a different card or other form of payment",
            "209"=>"CVN did not match",
            "210"=>"The card has reached the credit limit",
            "211"=>"Invalid card verification number",
            "220"=>"The processor declined the request based on a general issue with the customer’s account",
            "221"=>"The customer matched an entry on the processor’s negative file,Review the order and contact the payment processor",
            "222"=>"The customer’s bank account is frozen",
            "230"=>"The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the CVN check",
            "231"=>"Invalid account number",
            "232"=>"The card type is not accepted by the payment processor",
            "233"=>"General decline by the processor",
            "234"=>"There is a problem with your CyberSource merchant configuration",
            "236"=>"Processor failure",
            "237"=>"The authorization has already been reversed",
            "238"=>"The authorization has already been captured",
            "239"=>"The requested transaction amount must match the previous transaction amount",
            "240"=>"The card type sent is invalid or does not correlate with the card number",
            "241"=>"The request ID is invalid",
            "242"=>"You requested a capture, but there is no corresponding, unused authorization record,Request a new authorization, and if successful, proceed with the capture",
            "243"=>"The transaction has already been settled or reversed",
            "246"=>"The capture or credit is not voidable because the capture or credit information has already been submitted to your processor OR You requested a void for a type of transaction that cannot be voided",
            "247"=>"You requested a credit for a capture that was previously voided",
            "250"=>"The request was received, but there was a timeout at the payment processor,do not resend the request until you have reviewed the transaction status in the Business Center",            
            "254"=>"Stand-alone credits are not allowed"
        );

        public function __construct() {
            $this->_backend = (Mage::app()->getStore()->isAdmin()) ? true: false;
            if( $this->_backend && Mage::registry('current_order') != false ) {
                $this->setStore( Mage::registry('current_order')->getStoreId() );
            }
            elseif( $this->_backend && Mage::registry('current_invoice') != false ) {
                $this->setStore( Mage::registry('current_invoice')->getStoreId() );
            }
            elseif( $this->_backend && Mage::registry('current_creditmemo') != false ) {
                $this->setStore( Mage::registry('current_creditmemo')->getStoreId() );
            }
            elseif( $this->_backend && Mage::registry('current_customer') != false ) {
                $this->setStore( Mage::registry('current_customer')->getStoreId() );
            }
            elseif( $this->_backend && Mage::getSingleton('adminhtml/session_quote')->getStoreId() > 0 ) {
                $this->setStore( Mage::getSingleton('adminhtml/session_quote')->getStoreId() );
            }
            else {
                $this->setStore( Mage::app()->getStore()->getId() );
            }
            return $this;
        }

        public function getConfigModel(){
            if(is_null($this->_configModel)){
                $this->_configModel = Mage::getModel("md_cybersource/config")->setStoreId($this->_storeId);
            }
            return $this->_configModel;
        }

        public function setStore( $id ) {
            $this->_storeId = $id;
            return $this;
        }

        public function setCustomer($customer) {
            $this->_customer = $customer;
            if( $customer->getStoreId() > 0 ) {
                $this->setStore( $customer->getStoreId() );
            }
            return $this;
        }

        public function getCustomer(){
            if( isset( $this->_customer ) ) {
                $customer = $this->_customer;
            }
            elseif( $this->_backend ) {
                $customer = Mage::getModel('customer/customer')->load( Mage::getSingleton('adminhtml/session_quote')->getCustomerId() );
            }
            else {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
            }
            $this->setCustomer($customer);
            return $customer;
        }

        public function getSubscriptionCardInfo($subscriptionId = null){
            $card = null;
            $customer = $this->getCustomer();
            if(!is_null($subscriptionId)){
                $card=Mage::getModel('md_cybersource/cards')->getCollection()
                ->addFieldToFilter('subscription_id',$subscriptionId)
                ->getData()
                ;                
            }
            return $card;
        }

        public function saveCustomerProfileData($response,$payment,$customerid=null){
            $subscriptionId=$response->paySubscriptionCreateReply->subscriptionID;
            if(empty($customerid)){
                $post = Mage::app()->getRequest()->getParam('payment');  
                $customer = $this->getCustomer();
                $customerid=$customer->getId(); 
                $ccType=$post['cc_type'];
                $ccExpMonth=$post['cc_exp_month'];
                $ccExpYear=$post['cc_exp_year'];
                $ccLast4= substr($post['cc_number'],-4,4);  
            }else{
                $ccType=$payment->getCcType();
                $ccExpMonth=$payment->getCcExpMonth();
                $ccExpYear=$payment->getCcExpYear();
                $ccLast4= $payment->getCcLast4();  
            }
            if(!empty($subscriptionId) && $customerid){
                $billing=$payment->getOrder()->getBillingAddress();
                $post = Mage::app()->getRequest()->getParam('payment');  
                try{                                       
                    $model=Mage::getModel('md_cybersource/cards')
                    ->setFirstname($billing->getFirstname())
                    ->setLastname($billing->getLastname())
                    ->setPostcode($billing->getPostcode())
                    ->setCountryId($billing->getCountry())
                    ->setRegionId($billing->getRegionId())
                    ->setState($billing->getRegion())
                    ->setCity($billing->getCity())
                    ->setCompany($billing->getCompany())
                    ->setStreet($billing->getStreet(1))
                    ->setTelephone($billing->getTelephone())
                    ->setCustomerId($customerid)
                    ->setSubscriptionId($subscriptionId)
                    ->setccType($ccType)
                    ->setcc_exp_month($ccExpMonth)
                    ->setcc_exp_year($ccExpYear)
                    ->setcc_last4($ccLast4)
                    ->setCreatedAt(now())
                    ->setUpdatedAt(now())
                    ->save()
                    ;
                    Mage::getSingleton('core/session')->addSuccess(Mage::helper('md_cybersource')->__('Credit card saved successfully.')); 
                    return;  
                }
                catch (Exception $e) {
                    $message=$e->getMessage();
                    Mage::log("Unable to save customer profile due to : => $message", null, 'MD_Cybersource.log', true);                                                
                    Mage::throwException(
                        Mage::helper('md_cybersource')->__('Unable to save customer profile due to: %s', $e->getMessage())
                    );
                }
            }
        }

        public function assignData($data) {
            parent::assignData($data);
            $post = Mage::app()->getRequest()->getParam('payment');
            if($post['subscription_id'] != 'new') {
                $subScriptionIdCheck=Mage::helper('core')->decrypt($post['subscription_id']); // md here we decrept subscription id
                $creditCard = $this->getSubscriptionCardInfo($subScriptionIdCheck);
                if($creditCard != '' && !empty($creditCard) ) {
                    $this->getInfoInstance()->setCcLast4($_card[0]['cc_last4'])
                    ->setCcType($_card[0]['cc_type'])
                    ->setAdditionalInformation('md_cybersource_subscription_id',$post['subscription_id']);
                }
            }else{
                $this->getInfoInstance()->setCcType($post['cc_type'])
                ->setCcLast4(substr($post['cc_number'], -4))
                ->setCcNumber($post['cc_number'])
                ->setCcCid($post['cc_cid'])
                ->setCcExpMonth($post['cc_exp_month'])
                ->setCcExpYear($post['cc_exp_year'])
                ->setAdditionalInformation('md_save_card',$post['save_card']);
            }
            return $this;
        }

        /**
        * Validate the transaction inputs.
        */
        public function validate() {
            $post = Mage::app()->getRequest()->getParam('payment');
            if( $post['subscription_id'] == 'new' || !empty($post['cc_number']) ) {
                try {
                    return parent::validate();
                }
                catch(Exception $e) {
                    return $e->getMessage();
                }
            }
            else {
                return true;
            }
        }

        public function authorize(Varien_Object $payment, $amount){
            $exceptionMessage = false;          
            if ($amount <= 0) {
                Mage::throwException(Mage::helper('md_cybersource')->__('Invalid amount for authorization.'));
            }
            $this->_initCardsStorage($payment);
            $post = Mage::app()->getRequest()->getParam('payment');           
            try{
                $isMultiShipping=Mage::getSingleton('checkout/session')->getQuote()->getData('is_multi_shipping');
                $subScriptionIdCheck=$isMultiShipping=="1"?$payment->getData('additional_information','md_cybersource_subscription_id'):$post['subscription_id'];
                if(( !empty($subScriptionIdCheck) && empty($post['cc_number'])) || ($isMultiShipping=="1" && !empty($subScriptionIdCheck)) ) { // md order using subscription id                   
                    $subScriptionIdCheck=Mage::helper('core')->decrypt($subScriptionIdCheck); // md here we decrept subscription id
                    $payment->setMdcybersourceSubscriptionId($subScriptionIdCheck);
                    $response = Mage::getModel("md_cybersource/api_soap")
                    ->prepareAuthorizeResponse($payment,$amount,true);            
                }else{
                    $response = Mage::getModel("md_cybersource/api_soap")
                    ->prepareAuthorizeResponse($payment,$amount,false); 
                }

                if ($response->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                    $quote    = Mage::getSingleton('checkout/session')->getQuote();
                    if(!empty($subScriptionIdCheck) && empty($post['cc_number']) ) { 
                        $card=$this->getSubscriptionCardInfo($subScriptionIdCheck);  
                        $payment->setCcLast4($card[0]['cc_last4']);
                        $payment->setCcType($card[0]['cc_type']);
                        $payment->setAdditionalInformation('md_cybersource_subscription_id',$subScriptionIdCheck);
                        $payment->setMdcybersourceSubscriptionid($subScriptionIdCheck);
                    }else{
                        $payment->setCcLast4(substr($post['cc_number'],-4,4));
                        $payment->setCcType($post['cc_type']);
                    }
                    $saveCard=$isMultiShipping=="1"?$payment->getData('additional_information','md_save_card'):  $post['save_card'];
                    if(($saveCard == 1 && $post['cc_number']!='') && (Mage::getSingleton('customer/session')->getCustomerId() || ( Mage::app()->getStore()->isAdmin() && Mage::getSingleton('adminhtml/session_quote')->getCustomer()->getId()) ) ) // md saved card only for register customer

                    {
                        $profileResponse = Mage::getModel("md_cybersource/api_soap")
                        ->createCustomerProfileFromTransaction($response->requestID);
                        $code=$profileResponse->reasonCode;
                        $profileResponsecheck=$profileResponse->paySubscriptionCreateReply->reasonCode;
                        if($code=='100' && $profileResponsecheck=='100'){
                            $customerid= Mage::app()->getStore()->isAdmin() ? Mage::getSingleton('adminhtml/session_quote')->getCustomer()->getId():Mage::getSingleton('customer/session')->getCustomer()->getId();
                            $saveData=$this->saveCustomerProfileData($profileResponse,$payment,$customerid);
                        }
                        else{
                            $errorMessage=$this->_errorMessage[$code];
                            if($code=='102' || $code=='101'){
                                $errorMessage=$response->invalidField.$response->missingField;
                                $errorMessage= is_array($errorMessage)?implode(",",$errorMessage):$errorMessage;
                                $errorMessage=$errorMessage." , ". $this->_errorMessage[$resonCode];
                            }
                            if(isset($errorMessage) && !empty($errorMessage)){
                                Mage::getSingleton("core/session")->addError("Error code: ".$code." : ". $errorMessage);    
                            }else{
                                Mage::getSingleton("core/session")->addError("Error code: ".$code." : ".$errorMessage);        
                            }
                        }
                    }
                    $csToRequestMap=self::REQUEST_TYPE_AUTH_ONLY;
                    $payment->setAnetTransType($csToRequestMap);
                    $payment->setAmount($amount);
                    $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;                
                    $card = $this->_registerCard($response, $payment);
                    if((class_exists('Enterprise_Reward_Helper_Data') && Mage::helper('md_cybersource')->getCheckversion("1.10.0.0",">=")) ||( !class_exists('Enterprise_Reward_Helper_Data'))){
                        $this->_addTransaction(
                            $payment,
                            $response->requestID,
                            $newTransactionType,
                            array('is_transaction_closed' => 0),
                            array($this->_realTransactionIdKey => $response->requestID),
                            Mage::helper('md_cybersource')->getTransactionMessage(
                                $payment, $csToRequestMap, $response->requestID, $card, $amount
                            )
                        );
                    }
                    $card->setLastTransId($response->requestID);

                    $payment->setLastTransId($response->requestID)
                    ->setCcTransId($response->requestID)
                    ->setTransactionId($response->requestID)
                    ->setmdCybersourceRequestid($response->requestID)
                    ->setCybersourceToken($response->requestToken)
                    ->setIsTransactionClosed(0)
                    ->setStatus(self::STATUS_APPROVED)
                    ->setCcAvsStatus($response->ccAuthReply->avsCode);
                    /*
                    * checking if we have cvCode in response bc
                    * if we don't send cvn we don't get cvCode in response
                    */
                    if (isset($response->ccAuthReply->cvCode)) {
                        $payment->setCcCidStatus($response->ccAuthReply->cvCode);
                    }
                } else {    
                    $card = $this->_registerCard($response, $payment);            
                    $resonCode=$response->reasonCode;
                    if($resonCode=='102' || $resonCode=='101'){
                        $exceptionMessage=$response->invalidField.$response->missingField;                
                        $exceptionMessage= is_array($exceptionMessage)?implode(",",$exceptionMessage):$exceptionMessage;
                        $exceptionMessage=empty($exceptionMessage)?$this->_errorMessage[$resonCode]:$exceptionMessage;
                        $exceptionMessage= $this->_wrapGatewayError($exceptionMessage);
                        $exceptionMessage=$exceptionMessage." , ". $this->_errorMessage[$resonCode];
                    }
                    else{
                        $exceptionMessage = $this->_wrapGatewayError($this->_errorMessage[$resonCode]);
                    }
                    $exceptionMessage = Mage::helper('md_cybersource')->getTransactionMessage(
                        $payment, self::REQUEST_TYPE_AUTH_ONLY, $response->requestID, $card, $amount, $exceptionMessage
                    );
                    Mage::throwException($exceptionMessage);
                }   
            }
            catch (Exception $e) {
                Mage::throwException(
                    Mage::helper('md_cybersource')->__('Cybersource Gateway request error: %s', $e->getMessage())
                );
            }
            if ($exceptionMessage !== false) {
                Mage::throwException($exceptionMessage);
            }
            $payment->setSkipTransactionCreation(true);
            return $this;
        }

        public function capture(Varien_Object $payment, $amount) { 
            $error = false;
            if ($amount <= 0) {
                Mage::throwException(Mage::helper('md_cybersource')->__('Invalid amount for capture.'));
            }
            $this->_initCardsStorage($payment);
            $post = Mage::app()->getRequest()->getParam('payment');
            $invoice = Mage::registry('current_invoice');
            try { 
                if ($this->_isPreauthorizeCapture($payment)) {
                    $this->_preauthorizeCapture($payment, $amount);
                }else{                   
                    $isMultiShipping=Mage::getSingleton('checkout/session')->getQuote()->getData('is_multi_shipping');
                    $subScriptionIdCheck=$isMultiShipping=="1"?$payment->getData('additional_information','md_cybersource_subscription_id'):$post['subscription_id'];
                    if(( !empty($subScriptionIdCheck) && empty($post['cc_number'])) || ($isMultiShipping=="1" && !empty($subScriptionIdCheck)) ) { // md order using subscription id                        
                        $subScriptionIdCheck=Mage::helper('core')->decrypt($subScriptionIdCheck); // md here we decrept subscription id
                        $payment->setMdcybersourceSubscriptionId($subScriptionIdCheck);
                        $response = Mage::getModel("md_cybersource/api_soap")
                        ->prepareCaptureResponse($payment,$amount,true);            
                    }else{
                        $response = Mage::getModel("md_cybersource/api_soap")
                        ->prepareCaptureResponse($payment,$amount,false);            
                    }
                    if ($response->reasonCode==self::RESPONSE_CODE_SUCCESS) {   
                        $quote    = Mage::getSingleton('checkout/session')->getQuote();             
                        if(!empty($subScriptionIdCheck) && empty($post['cc_number']) ) { 

                            $card=$this->getSubscriptionCardInfo($subScriptionIdCheck);  
                            $payment->setCcLast4($card[0]['cc_last4']);
                            $payment->setCcType($card[0]['cc_type']);
                            $payment->setAdditionalInformation('md_cybersource_subscription_id',$subScriptionIdCheck);
                            $payment->setMdcybersourceSubscriptionid($subScriptionIdCheck);
                        }else{
                            $payment->setCcLast4(substr($post['cc_number'],-4,4));
                            $payment->setCcType($post['cc_type']);
                        }
                        $saveCard=$isMultiShipping=="1"?$payment->getData('additional_information','md_save_card'):  $post['save_card'];
                        if(($saveCard == 1 && $post['cc_number']!='') && (Mage::getSingleton('customer/session')->getCustomerId() || ( Mage::app()->getStore()->isAdmin() && Mage::getSingleton('adminhtml/session_quote')->getCustomer()->getId()) ) ) // md saved card only for register customer
                        {
                            $profileResponse = Mage::getModel("md_cybersource/api_soap")
                            ->createCustomerProfileFromTransaction($response->requestID);
                            $code=$profileResponse->reasonCode;
                            $profileResponsecheck=$profileResponse->paySubscriptionCreateReply->reasonCode;
                            if($code=='100' && $profileResponsecheck=='100'){
                                $customerid= Mage::app()->getStore()->isAdmin() ? Mage::getSingleton('adminhtml/session_quote')->getCustomer()->getId():Mage::getSingleton('customer/session')->getCustomer()->getId();
                                $saveData=$this->saveCustomerProfileData($profileResponse,$payment,$customerid);                            
                            }
                            else{
                                $errorMessage=$this->_errorMessage[$code];
                                if($code=='102' || $code=='101'){
                                    $errorMessage=$response->invalidField;
                                    $errorMessage= is_array($errorMessage)?implode(",",$errorMessage):$errorMessage;
                                    $errorMessage=$errorMessage." , ". $this->_errorMessage[$resonCode];
                                }
                                if(isset($errorDescription) && !empty($errorDescription)){
                                    Mage::getSingleton("core/session")->addError("Error code: ".$code." : ".$errorMessage);    
                                }else{
                                    Mage::getSingleton("core/session")->addError("Error code: ".$code." : ".$errorMessage);        
                                }
                            }
                        }
                        $card = $this->_registerCard($response, $payment);
                        $csToRequestMap=self::REQUEST_TYPE_AUTH_CAPTURE;
                        $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
                        if((class_exists('Enterprise_Reward_Helper_Data') && Mage::helper('md_cybersource')->getCheckversion("1.10.0.0",">=")) ||( !class_exists('Enterprise_Reward_Helper_Data'))){
                            $this->_addTransaction(
                                $payment,
                                $response->requestID,
                                $newTransactionType,
                                array('is_transaction_closed' => 0),
                                array($this->_realTransactionIdKey => $response->requestID),
                                Mage::helper('md_cybersource')->getTransactionMessage(
                                    $payment, $csToRequestMap, $response->requestID, $card, $amount
                                )
                            );
                        }
                        $card->setLastTransId($response->requestID);
                        $card->setCapturedAmount($card->getProcessedAmount());
                        $captureTransactionId = $response->requestID;
                        $card->setLastCapturedTransactionId($captureTransactionId);
                        $this->getCardsStorage()->updateCard($card);

                        $payment->setLastTransId($response->requestID)
                        ->setLastCybersourceToken($response->requestToken)
                        ->setCcTransId($response->requestID)
                        ->setTransactionId($response->requestID)
                        ->setIsTransactionClosed(0)
                        ->setCybersourceToken($response->requestToken)
                        ;
                    } 
                    else {
                        $card = $this->_registerCard($response, $payment);
                        $resonCode=$response->reasonCode;
                        if($resonCode=='102' || $resonCode=='101'){
                            $exceptionMessage=$response->invalidField;
                            $exceptionMessage= is_array($exceptionMessage)?implode(",",$exceptionMessage):$exceptionMessage;
                            $exceptionMessage=empty($exceptionMessage)?$this->_errorMessage[$resonCode]:$exceptionMessage;
                            $exceptionMessage= $this->_wrapGatewayError($exceptionMessage);
                        }
                        else{
                            $exceptionMessage = $this->_wrapGatewayError($this->_errorMessage[$resonCode]);
                        }
                        $exceptionMessage = Mage::helper('md_cybersource')->getTransactionMessage(
                            $payment, self::REQUEST_TYPE_AUTH_CAPTURE, $response->requestID, $card, $amount, $exceptionMessage
                        );
                        Mage::throwException($exceptionMessage);
                    }
                }
            } catch (Exception $e) {
                Mage::throwException(
                    Mage::helper('md_cybersource')->__('Gateway request error: %s', $e->getMessage())
                );
            }      
            $payment->setSkipTransactionCreation(true);      
            return $this;                 
        }       

        public function refund(Varien_Object $payment, $amount) {
            $cardsStorage = $this->getCardsStorage($payment);

            if ($this->_formatAmount(
                $cardsStorage->getCapturedAmount() - $cardsStorage->getRefundedAmount()
                ) < $amount
            ) {
                Mage::throwException(Mage::helper('md_cybersource')->__('Invalid amount for refund.'));
            }
            $messages = array();
            $isSuccessful = false;
            $isFiled = false;
            // Grab the invoice in case partial invoicing
            $creditmemo = Mage::registry('current_creditmemo');
            if( !is_null( $creditmemo ) ) {
                $this->_invoice = $creditmemo->getInvoice();
            }
            foreach($cardsStorage->getCards() as $card) {
                $lastTransactionId=$payment->getData('cc_trans_id');
                $cardTransactionId=$card->getTransactionId();
                if($lastTransactionId==$cardTransactionId){
                    if ($amount > 0) {
                        $cardAmountForRefund = $this->_formatAmount($card->getCapturedAmount() - $card->getRefundedAmount());
                        if ($cardAmountForRefund <= 0) {
                            continue;
                        }
                        if ($cardAmountForRefund > $amount) {
                            $cardAmountForRefund = $amount;
                        }
                        try {
                            $newTransaction = $this->_refundCardTransaction($payment, $cardAmountForRefund, $card);
                            if($newTransaction!=null){
                                $messages[] = $newTransaction->getMessage();
                                $isSuccessful = true;
                            }
                        }catch (Exception $e) {
                            $messages[] = $e->getMessage();
                            $isFiled = true;
                            continue;
                        }
                        $card->setRefundedAmount($this->_formatAmount($card->getRefundedAmount() + $cardAmountForRefund));
                        $cardsStorage->updateCard($card);
                        $amount = $this->_formatAmount($amount - $cardAmountForRefund);
                    }else{
                        $payment->setSkipTransactionCreation(true);
                        return $this;
                    }
                }
            }

            if ($isFiled) {
                $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
            }

            $payment->setSkipTransactionCreation(true);
            return $this;
        }

        public function void(Varien_Object $payment) {
            $cardsStorage = $this->getCardsStorage($payment);
            $messages = array();
            $isSuccessful = false;
            $isFiled = false;
            foreach($cardsStorage->getCards() as $card) {
                $lastTransactionId=$payment->getData('cc_trans_id');
                $cardTransactionId=$card->getTransactionId();
                if($lastTransactionId==$cardTransactionId){
                    try {
                        $newTransaction = $this->_voidCardTransaction($payment, $card);
                        if($newTransaction!=null){
                            $messages[] = $newTransaction->getMessage();
                            $isSuccessful = true;
                        }
                    }catch (Exception $e) {
                        $messages[] = $e->getMessage();
                        $isFiled = true;
                        continue;
                    }
                    $cardsStorage->updateCard($card);
                }
            }
            if ($isFiled) {
                $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
            }

            $payment->setSkipTransactionCreation(true);
            return $this;
        }

        protected function _voidCardTransaction($payment, $card){
            $authTransactionId = $card->getLastTransId();        
            $authTransaction = $payment->getTransaction($authTransactionId);
            if($authTransaction!=false){
                $realAuthTransactionId = $authTransaction->getAdditionalInformation($this->_realTransactionIdKey);
                $payment->setAnetTransType(self::REQUEST_TYPE_VOID);
                $payment->setTransId($realAuthTransactionId);
                $response = Mage::getModel("md_cybersource/api_soap")
                ->prepareVoidResponse($payment,$card);  
                if ($response->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                    $voidTransactionId = $response->requestID . '-void';
                    $card->setLastTransId($voidTransactionId);
                    $payment->setTransactionId($response->requestID)
                    ->setCybersourceToken($response->requestToken)
                    ->setIsTransactionClosed(1);
                    if((class_exists('Enterprise_Reward_Helper_Data') && Mage::helper('md_cybersource')->getCheckversion("1.10.0.0",">=")) ||( !class_exists('Enterprise_Reward_Helper_Data'))){
                        {
                            return $this->_addTransaction(
                                $payment,
                                $voidTransactionId,
                                Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID,
                                array(
                                    'is_transaction_closed' => 1,
                                    'should_close_parent_transaction' => 1,
                                    'parent_transaction_id' => $authTransactionId
                                ),
                                array($this->_realTransactionIdKey => $response->requestID),
                                Mage::helper('md_cybersource')->getTransactionMessage(
                                    $payment, self::REQUEST_TYPE_VOID, $response->requestID, $card
                                )
                            );
                        }
                    }
                    else{
                        $code=$response->reasonCode;
                        $errorMessage=$this->_errorMessage[$code];
                        $exceptionMessage = Mage::helper('md_cybersource')->getTransactionMessage(
                            $payment, self::REQUEST_TYPE_VOID, $realAuthTransactionId, $card, false, $errorMessage
                        );
                        Mage::throwException($exceptionMessage);
                    }
                }
                return null;
            } 
        }
        public function cancel(Varien_Object $payment) {
            return $this->void($payment);
        }        

        /**
        * Payment method available? Yes.
        */
        public function isAvailable($quote=null) {
            $checkResult = new StdClass;
            $isActive = $this->getConfigModel()->getIsActive();
            $checkResult->isAvailable = $isActive;
            $checkResult->isDeniedInConfig = !$isActive;
            if ($checkResult->isAvailable && $quote) {
                $checkResult->isAvailable = $this->isApplicableToQuote($quote, self::CHECK_RECURRING_PROFILES);
            }
            return $checkResult->isAvailable;
        }


        protected function _isPreauthorizeCapture($payment){
            if ($this->getCardsStorage()->getCardsCount() <= 0) {
                return false;
            }
            foreach($this->getCardsStorage()->getCards() as $card) {
                $lastTransactionId=$payment->getData('cc_trans_id');
                $cardTransactionId=$card->getTransactionId();
                if($lastTransactionId==$cardTransactionId){
                    $lastTransaction = $payment->getTransaction($card->getLastTransId());
                    if (!$lastTransaction
                        || $lastTransaction->getTxnType() != Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH
                    ) {
                        return false;
                    }
                    return true;
                }
            }
        }


        public function getCardsStorage($payment = null){
            if (is_null($payment)) {
                $payment = $this->getInfoInstance();
            }
            if (is_null($this->_cardsStorage)) {
                $this->_initCardsStorage($payment);
            }
            return $this->_cardsStorage;
        }

        /**
        * Init cards storage model
        *
        * @param Mage_Payment_Model_Info $payment
        */
        protected function _initCardsStorage($payment){
            $this->_cardsStorage = Mage::getModel('md_cybersource/payment_cards')->setPayment($payment);
        }

        protected function _registerCard($response, Mage_Sales_Model_Order_Payment $payment){
            $cardsStorage = $this->getCardsStorage($payment);
            $card = $cardsStorage->registerCard();
            if($payment->getMdcybersourceSubscriptionId()!=''){
                $customerCard=$this->getSubscriptionCardInfo($payment->getMdcybersourceSubscriptionId());
                $card->setCcType($customerCard[0]['cc_type'])
                ->setCcLast4($customerCard[0]['cc_last4'])
                ->setCcExpMonth($customerCard[0]['cc_exp_month'])
                ->setCcOwner($customerCard[0]['firstname'])
                ->setCcExpYear($customerCard[0]['cc_exp_year']);
            }else{
                $post=Mage::app()->getRequest()->getParam('payment');
                $card->setCcType($post['cc_type'])
                ->setCcLast4(substr($post['cc_number'],-4,4))
                ->setCcExpMonth($post['cc_exp_month'])
                ->setCcOwner($post['firstname'])
                ->setCcExpYear($post['cc_exp_year']);
            }
            $card
            ->setRequestedAmount($response->ccAuthReply->amount)
            ->setLastTransId($response->requestID)
            ->setProcessedAmount($response->ccAuthReply->amount)
            ->setMerchantReferenceCode($response->merchantReferenceCode)
            ->setreconciliationID($response->ccAuthReply->reconciliationID)
            ->setauthorizationCode($response->ccAuthReply->authorizationCode)
            ->setAvsResultCode($response->ccAuthReply->avsCode)
            ->setCVNResultCode($response->ccAuthReply->cvCode)
            ->setTransactionId($response->requestID);
            $cardsStorage->updateCard($card);
            return $card;
        }

        protected function _preauthorizeCapture($payment, $requestedAmount){
            $cardsStorage = $this->getCardsStorage($payment);
            if ($this->_formatAmount(
                $cardsStorage->getProcessedAmount() - $cardsStorage->getCapturedAmount()
                ) < $requestedAmount
            ) {
                Mage::throwException(Mage::helper('md_cybersource')->__('Invalid amount for capture.'));
            }
            $messages = array();
            $isSuccessful = false;
            $isFiled = false;
            foreach($cardsStorage->getCards() as $card) {
                $lastTransactionId=$payment->getData('cc_trans_id');
                $cardTransactionId=$card->getTransactionId();
                if($lastTransactionId==$cardTransactionId){
                    if ($requestedAmount > 0) {
                        $prevCaptureAmount = $card->getCapturedAmount();
                        $cardAmountForCapture = $card->getProcessedAmount();
                        if ($cardAmountForCapture > $requestedAmount) {
                            $cardAmountForCapture = $requestedAmount;
                        }
                        try {          
                            $newTransaction = $this->_preauthorizeCaptureCardTransaction(
                                $payment, $cardAmountForCapture , $card
                            );
                            if($newTransaction!=null){
                                $messages[] = $newTransaction->getMessage();
                                $isSuccessful = true;
                            }  
                        }catch (Exception $e) {
                            $messages[] = $e->getMessage();
                            $isFiled = true;
                            continue;
                        }
                        $newCapturedAmount = $prevCaptureAmount + $cardAmountForCapture;
                        $card->setCapturedAmount($newCapturedAmount);
                        $cardsStorage->updateCard($card);
                        $requestedAmount = $this->_formatAmount($requestedAmount - $cardAmountForCapture);
                        if ($isSuccessful) {
                            $balance = $card->getProcessedAmount() - $card->getCapturedAmount();
                            if ($balance > 0) {
                                $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_ONLY);
                                $payment->setAmount($balance);
                                //$this->processCimRequest($payment, $balance, 'profileTransAuthOnly', $card, true); // md hjave a look for remaining amount capture
                            }
                        }
                    }    
                }           
            }
            if ($isFiled) {
                $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
            }
        }

        protected function _preauthorizeCaptureCardTransaction($payment, $amount, $card){
            $authTransactionId = $card->getLastTransId();
            $authTransaction = $payment->getTransaction($authTransactionId);
            if($authTransaction!=false){
                $realAuthTransactionId = $authTransaction->getAdditionalInformation($this->_realTransactionIdKey);
                $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
                $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
                $payment->setTransId($realAuthTransactionId);
                $payment->setAmount($amount);

                $response = Mage::getModel("md_cybersource/api_soap")
                ->prepareAuthorizeCaptureResponse($payment,$amount,false);  
                if ($response->reasonCode == self::RESPONSE_CODE_SUCCESS) {
                    $captureTransactionId = $response->requestID . '-capture';
                    $card->setLastCapturedTransactionId($captureTransactionId);
                    if((class_exists('Enterprise_Reward_Helper_Data') && Mage::helper('md_cybersource')->getCheckversion("1.10.0.0",">=")) ||( !class_exists('Enterprise_Reward_Helper_Data'))){
                        return $this->_addTransaction(
                            $payment,
                            $captureTransactionId,
                            $newTransactionType,
                            array(
                                'is_transaction_closed' => 0,
                                'parent_transaction_id' => $authTransactionId
                            ),
                            array($this->_realTransactionIdKey => $response->requestID),
                            Mage::helper('md_cybersource')->getTransactionMessage(
                                $payment, self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE, $response->requestID, $card, $amount
                            )
                        );
                    }
                }
                else{
                    $resonCode=$response->reasonCode;
                    $exceptionMessage = $this->_wrapGatewayError($this->_errorMessage[$resonCode]);
                    $exceptionMessage = Mage::helper('md_cybersource')->getTransactionMessage(
                        $payment, self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE, $realAuthTransactionId, $card, $amount, $exceptionMessage
                    );
                    Mage::throwException($exceptionMessage);
                }            
            }else{
                return null;
            }
        }

        protected function _formatAmount($amount, $asFloat = false){
            $amount = sprintf('%.2F', $amount); // "f" depends on locale, "F" doesn't
            return $asFloat ? (float)$amount : $amount;
        }

        protected function _isGatewayActionsLocked($payment){
            return $payment->getAdditionalInformation($this->_isGatewayActionsLockedKey);
        }

        protected function _generateChecksum(Varien_Object $object, $checkSumDataKeys = array()){
            $data = array();
            foreach($checkSumDataKeys as $dataKey) {
                $data[] = $dataKey;
                $data[] = $object->getData($dataKey);
            }
            return md5(implode($data, '_'));
        }

        protected function _processFailureMultitransactionAction($payment, $messages, $isSuccessfulTransactions){
            if ($isSuccessfulTransactions) {
                $messages[] = Mage::helper('md_cybersource')->__('Gateway actions are locked because the gateway cannot complete one or more of the transactions. Please log in to your Cybersource account to manually resolve the issue(s).');
                $currentOrderId = $payment->getOrder()->getId();
                $copyOrder = Mage::getModel('sales/order')->load($currentOrderId);
                $copyOrder->getPayment()->setAdditionalInformation($this->_isGatewayActionsLockedKey, 1);
                foreach($messages as $message) {
                    $copyOrder->addStatusHistoryComment($message);
                }
                $copyOrder->save();
            }
            Mage::throwException(implode(' | ', $messages));
        }

        protected function _refundCardTransaction($payment, $amount, $card){
            $credit_memo = Mage::registry('current_creditmemo');        
            $captureTransactionId = $credit_memo->getInvoice()->getTransactionId();
            $captureTransaction = $payment->getTransaction($captureTransactionId);
            if($captureTransaction!=false){
                $realCaptureTransactionId = $captureTransaction->getAdditionalInformation($this->_realTransactionIdKey);
                $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
                $payment->setXTransId($realCaptureTransactionId);
                $payment->setAmount($amount);            
                $response = Mage::getModel("md_cybersource/api_soap")
                ->prepareRefundResponse($payment,$amount,$realCaptureTransactionId);

                if ($response->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                    $refundTransactionId = $response->requestID . '-refund';
                    $shouldCloseCaptureTransaction = 0;

                    if ($this->_formatAmount($card->getCapturedAmount() - $card->getRefundedAmount()) == $amount) {
                        $card->setLastTransId($refundTransactionId);
                        $shouldCloseCaptureTransaction = 1;
                    }
                    if((class_exists('Enterprise_Reward_Helper_Data') && Mage::helper('md_cybersource')->getCheckversion("1.10.0.0",">=")) ||( !class_exists('Enterprise_Reward_Helper_Data'))){
                        return $this->_addTransaction(
                            $payment,
                            $refundTransactionId,
                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,
                            array(
                                'is_transaction_closed' => 1,
                                'should_close_parent_transaction' => $shouldCloseCaptureTransaction,
                                'parent_transaction_id' => $captureTransactionId
                            ),
                            array($this->_realTransactionIdKey => $response->requestID),
                            Mage::helper('md_cybersource')->getTransactionMessage(
                                $payment, self::REQUEST_TYPE_CREDIT, $response->requestID, $card, $amount
                            )
                        );
                    }
                }
                else{
                    $code=$response->reasonCode;
                    $errorMessage=$this->_errorMessage[$code];
                    $exceptionMessage = Mage::helper('md_cybersource')->getTransactionMessage(
                        $payment, self::REQUEST_TYPE_CREDIT, $realCaptureTransactionId, $card, $amount, $errorMessage
                    );
                    Mage::throwException($exceptionMessage);
                }
            }else{
                return null;
            }
        }        

        protected function _wrapGatewayError($text){
            return Mage::helper('md_cybersource')->__('Gateway error: %s', $text);
        }

        private function _clearAssignedData($payment){
            $payment->setCcType(null)
            ->setCcOwner(null)
            ->setCcLast4(null)
            ->setCcNumber(null)
            ->setCcCid(null)
            ->setCcExpMonth(null)
            ->setCcExpYear(null)
            ->setCcSsIssue(null)
            ->setCcSsStartMonth(null)
            ->setCcSsStartYear(null)
            ;
            return $this;
        }

        protected function _addTransaction(Mage_Sales_Model_Order_Payment $payment, $transactionId, $transactionType,
            array $transactionDetails = array(), array $transactionAdditionalInfo = array(), $message = false
        ) {
            if((class_exists('Enterprise_Reward_Helper_Data') && Mage::helper('md_cybersource')->getCheckversion("1.10.0.0",">=")) ||( !class_exists('Enterprise_Reward_Helper_Data'))){
                $payment->resetTransactionAdditionalInfo();
            }
            $payment->setTransactionId($transactionId);
            $payment->setLastTransId($transactionId);

            foreach ($transactionDetails as $key => $value) {
                $payment->setData($key, $value);
            }
            foreach ($transactionAdditionalInfo as $key => $value) {
                $payment->setTransactionAdditionalInfo($key, $value);
            }
            $transaction = $payment->addTransaction($transactionType, null, false , $message);

            $transaction->setMessage($message);

            return $transaction;
        }

        /**
        * we are doing any further processing if we need for invoice.
        * @param Mage_Sales_Model_Order_Invoice $invoice
        * @param Mage_Sales_Model_Order_Payment $payment
        * @return MD_Cybersource_Model_Payment
        */
        public function processInvoice($invoice, $payment)
        {
            $lastCaptureTransId = '';
            $cardsStorage = $this->getCardsStorage($payment);      
            foreach($cardsStorage->getCards() as $card) {
                $lastTransactionId=$payment->getData('cc_trans_id');
                $cardTransactionId=$card->getTransactionId();
                if($lastTransactionId==$cardTransactionId){
                    $lastCapId = $card->getData('last_captured_transaction_id');
                    if ($lastCapId && !empty($lastCapId) && !is_null($lastCapId)) {
                        $lastCaptureTransId = $lastCapId;
                        break;
                    }
                }
            }

            $invoice->setTransactionId($lastCaptureTransId);
            return $this;
        }

        /**
        * we are doing any further processing if we need for creditmemo.
        * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
        * @param Mage_Sales_Model_Order_Payment $payment
        * @return MD_Cybersource_Model_Payment
        */

        public function processCreditmemo($creditmemo, $payment)
        {
            $lastRefundedTransId = '';
            $cardsStorage = $this->getCardsStorage($payment);
            foreach($cardsStorage->getCards() as $card) {
                $lastTransactionId=$payment->getData('cc_trans_id');
                $cardTransactionId=$card->getTransactionId();
                if($lastTransactionId==$cardTransactionId){
                    $lastCardTransId = $card->getData('last_refunded_transaction_id');
                    if ($lastCardTransId && !empty($lastCardTransId) && !is_null($lastCardTransId)) {
                        $lastRefundedTransId = $lastCardTransId;
                        break;
                    }
                }
            }
            $creditmemo->setTransactionId($lastRefundedTransId);
            return $this;
        }


        /**
        * return accepted currencies that are valid for this payment methdo for purchase.
        * @return string
        */
        public function getAcceptedCurrencyCodes()
        {
            if (!$this->hasData('_accepted_currency')) {
                $acceptedCurrencyCodes = $this->_allowCurrencyCode;
                $acceptedCurrencyCodes[] = $this->getConfigModel()->getAcceptedCurrency();
                $this->setData('_accepted_currency', $acceptedCurrencyCodes);
            }
            return $this->_getData('_accepted_currency');
        }

        /**
        * Check method for processing with base currency
        *
        * @param string $currencyCode
        * @return boolean
        */
        public function canUseForCurrency($currencyCode)
        {
            return true;  // md check 2562015
            if (!in_array($currencyCode, $this->getAcceptedCurrencyCodes())) {
                return false;
            }
            return true;
        }

        /**
        * check is this payment method is applicable with curent customer billing and shipping informations
        * @param string Mage_Sales_Model_Quote $quote
        * @param int $checksBitMask
        * @return boolean true | false
        */

        public function isApplicableToQuote($quote, $checksBitMask)
        {
            if ($checksBitMask & self::CHECK_USE_FOR_COUNTRY) {
                if (!$this->canUseForCountry($quote->getBillingAddress()->getCountry())) {
                    return false;
                }
            }
            if ($checksBitMask & self::CHECK_USE_FOR_CURRENCY) {
                if (!$this->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
                    return false;
                }
            }
            if ($checksBitMask & self::CHECK_USE_CHECKOUT) {
                if (!$this->canUseCheckout()) {
                    return false;
                }
            }
            if ($checksBitMask & self::CHECK_USE_FOR_MULTISHIPPING) {
                if (!$this->canUseForMultishipping()) {
                    return false;
                }
            }
            if ($checksBitMask & self::CHECK_USE_INTERNAL) {
                if (!$this->canUseInternal()) {
                    return false;
                }
            }
            if ($checksBitMask & self::CHECK_ORDER_TOTAL_MIN_MAX) {
                $total = $quote->getBaseGrandTotal();
                $minTotal = $this->getConfigData('min_order_total');
                $maxTotal = $this->getConfigData('max_order_total');
                if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
                    return false;
                }
            }
            if ($checksBitMask & self::CHECK_RECURRING_PROFILES) {
                if (!$this->canManageRecurringProfiles() && $quote->hasRecurringItems()) {
                    return false;
                }
            }
            if ($checksBitMask & self::CHECK_ZERO_TOTAL) {
                #$total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount(); // MD 29102015 
                $total = $quote->getBaseGrandTotal();
                if ($total < 0.0001 && $this->getCode() != 'free'
                    && !($this->canManageRecurringProfiles() && $quote->hasRecurringItems())
                ) {
                    return false;
                }
            }
            return true;
        }
        protected function _formatCcType($ccType)
        {
            $allTypes = Mage::getSingleton('payment/config')->getCcTypes();
            $allTypes = array_flip($allTypes);

            if (isset($allTypes[$ccType]) && !empty($allTypes[$ccType])) {
                return $allTypes[$ccType];
            }

            return $ccType;
        }        


        protected function _processPartialAuthorizationResponse($response, $orderPayment) 
        {
            if (!$response->getSplitTenderId()) {
                return false;
            }
            $quotePayment = $orderPayment->getOrder()->getQuote()->getPayment();
            $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
            $exceptionMessage = null;
            try {
                switch ($response->getResponseCode()) {
                    case self::RESPONSE_CODE_APPROVED:
                        $this->_registerCard($response, $orderPayment);
                        $this->_clearAssignedData($quotePayment);
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_SUCCESS);
                        return true;
                    case self::RESPONSE_CODE_HELD:
                        if ($response->getResponseReasonCode() != self::RESPONSE_REASON_CODE_PARTIAL_APPROVE) {
                            return false;
                        }
                        if ($this->getCardsStorage($orderPayment)->getCardsCount() + 1 >= self::PARTIAL_AUTH_CARDS_LIMIT) {
                            $this->cancelPartialAuthorization($orderPayment);
                            $this->_clearAssignedData($quotePayment);
                            $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_CARDS_LIMIT_EXCEEDED);
                            $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                            $exceptionMessage = Mage::helper('paygate')->__('You have reached the maximum number of credit card allowed to be used for the payment.');
                            break;
                        }
                        $orderPayment->setAdditionalInformation($this->_splitTenderIdKey, $response->getSplitTenderId());
                        $this->_registerCard($response, $orderPayment);
                        $this->_clearAssignedData($quotePayment);
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_SUCCESS);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = null;
                        break;
                    case self::RESPONSE_CODE_DECLINED:
                    case self::RESPONSE_CODE_ERROR:
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = $this->_wrapGatewayError($response->getResponseReasonText());
                        break;
                    default:
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = $this->_wrapGatewayError(
                            Mage::helper('paygate')->__('Payment partial authorization error.')
                        );
                }
            }catch(Exception $e){
                $exceptionMessage = $e->getMessage();
            }
            throw new Mage_Payment_Model_Info_Exception($exceptionMessage);
        }        
    }

