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
    class MD_Cybersource_Model_Observer
    {
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

        public function addCardTab(Varien_Event_Observer $observer){
            $block = $observer->getEvent()->getBlock();
            $id = Mage::app()->getRequest()->getParam('id',null);
            if(Mage::getSingleton('md_cybersource/config')->getIsActive()){
                if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs) {
                    if($id){
                        if(method_exists($block, 'addTabAfter')){
                            $block->addTabAfter('md_cybersource_cards', array(
                                'label'     => Mage::helper('md_cybersource')->__('Cybersource(Saved Cards)'),
                                'title'       => Mage::helper('md_cybersource')->__('Cybersource(Saved Cards)'),
                                'content'     => $block->getLayout()->createBlock("md_cybersource/adminhtml_cards_edit_tab_form")->toHtml(),
                                ),'tags');}else{
                            $block->addTab('md_cybersource_cards', array(
                                'label'     => Mage::helper('md_cybersource')->__('Cybersource(Saved Cards)'),
                                'title'       => Mage::helper('md_cybersource')->__('Cybersource(Saved Cards)'),
                                'content'     => $block->getLayout()->createBlock("md_cybersource/adminhtml_cards_edit_tab_form")->toHtml(),
                            ));
                        }
                    }
                }
            }
        }

        public function cardSaveToCustomer(Varien_Event_Observer $observer){
            $quote=Mage::getSingleton('checkout/session')->getQuote();
            $isMultiShipping=$quote->getData('is_multi_shipping');
            $rerverOrderId=$quote->getData('reserved_order_id');
            $order = $isMultiShipping=="1"?Mage::getModel('sales/order')->loadByIncrementId($rerverOrderId):$observer->getEvent()->getOrder();
            if($order){
                $payment=$order->getPayment();
                $transactionId=$payment->getdata('last_trans_id');
                $customerid = Mage::getSingleton('customer/session')->getCustomer()->getId();               
                $checkOutMethod = $quote->getdata('checkout_method');
                $post = Mage::app()->getRequest()->getParam('payment');
                $saveCard=$isMultiShipping=="1"?$payment->getData('additional_information','md_save_card'):  $post['save_card'];
                if(!empty($transactionId) && $customerid && $saveCard == 1 &&  $post['cc_number']!='' && $checkOutMethod=="register"){                
                    $profileResponse = Mage::getModel("md_cybersource/api_soap")
                    ->createCustomerProfileFromTransaction($transactionId);
                    $code=$profileResponse->reasonCode;
                    $profileResponsecheck=$profileResponse->paySubscriptionCreateReply->reasonCode;
                    if($code=='100' && $profileResponsecheck=='100'){
                        $saveData=Mage::getModel("md_cybersource/payment")->saveCustomerProfileData($profileResponse,$payment,$customerid);
                    }
                    else{
                        $errorMessage=$this->_errorMessage[$code];
                        if($code=='102' || $code=='101'){
                            $errorDescription=$response->invalidField;
                            $errorDescription= is_array($errorDescription)?implode(",",$errorDescription):$errorDescription;
                            $errorDescription=$errorDescription." , ". $this->_errorMessage[$resonCode];
                        }
                        if(isset($errorDescription) && !empty($errorDescription)){
                            Mage::getSingleton("core/session")->addError("Error code: ".$code." : ". $errorDescription);    
                        }else{
                            Mage::getSingleton("core/session")->addError("Error code: ".$code." : ".$errorMessage);        
                        }
                    }

                }                    
            }
        }
    }

