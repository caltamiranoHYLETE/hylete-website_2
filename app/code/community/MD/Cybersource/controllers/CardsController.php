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
    class MD_Cybersource_CardsController extends Mage_Core_Controller_Front_Action
    {
        protected $_errorMessage=array(
            "100"=> "Successful transaction",
            "101"=>"Missing required fields",
            "102"=>"Invalid data",
            "110"=>"Partial amount approved",
            "150"=>"General system failure",
            "151"=>"The request was received but there was a server timeout. This error does not
            include timeouts between the client and the server",
            "152"=>"The request was received, but a service did not finish running in time",
            "200"=>"The authorization request was approved by the issuing bank but declined by
            CyberSource because it did not pass the AVS check",
            "201"=>"The issuing bank has questions about the request. You will not receive an
            authorization code programmatically, but you can obtain one verbally by calling
            the processor",
            "202"=>"Expired card",
            "203"=>"General decline of the card. No other information provided by the issuing bank",
            "204"=>"Insufficient funds in the account",
            "205"=>"Stolen or lost card",
            "207"=>"Issuing bank unavailable",
            "208"=>"Inactive card or card not authorized for card-not-present transactions",
            "209"=>"American Express Card Identification Digits (CIDs) did not match",
            "210"=>"The card has reached the credit limit",
            "211"=>"Invalid card verification number",
            "220"=>"The processor declined the request based on a general issue with the
            customer’s account",
            "221"=>"The customer matched an entry on the processor’s negative file",
            "222"=>"The customer’s bank account is frozen",
            "230"=>"The authorization request was approved by the issuing bank but declined by
            CyberSource because it did not pass the CVN check",
            "231"=>"Invalid account number",
            "232"=>"The card type is not accepted by the payment processor",
            "233"=>"General decline by the processor",
            "234"=>"There is a problem with your CyberSource merchant configuration",
            "236"=>"Processor failure",
            "240"=>"The card type sent is invalid or does not correlate with the card number",
            "250"=>"The request was received, but there was a timeout at the payment processor"
        );

        public function preDispatch(){        
            parent::preDispatch();
            $loginUrl = Mage::helper('customer')->getLoginUrl();

            if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }
            return $this;
        }

        public function listAction(){
            $this->loadLayout();
            $this->renderLayout();
        }

        public function addAction(){
            $this->loadLayout();
            $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('md_cybersource/cards/list');
            }
            $this->renderLayout();
        }       

        public function saveAction(){
            $errorMessage = '';
            $params = $this->getRequest()->getParams();
            $mode = $params['md_cybersource']['card_mode'];
            $ccAction = $params['payment_info']['cc_action'];
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            try{
                $requestObject = new Varien_Object();
                $requestObject->addData(array(
                    'customer_id'=> $customer->getId(),
                    'email'=> $customer->getEmail()
                ));
                $requestObject->addData($params['md_cybersource']['address_info']);
                $requestObject->addData($params['md_cybersource']['payment_info']);
                $response = Mage::getModel("md_cybersource/api_soap")
                ->setInputData($requestObject)
                ->createCustomerProfile();
                $code=$response->reasonCode;
                $profileResponsecheck=$response->paySubscriptionCreateReply->reasonCode;
                if($code=='100' && $profileResponsecheck=='100'){
                    $subscriptionId=$response->paySubscriptionCreateReply->subscriptionID;
                    if(!empty($subscriptionId)){
                        $model=Mage::getModel('md_cybersource/cards')
                        ->setData($params['md_cybersource']['address_info'])
                        ->setCustomerId($customer->getId())
                        ->setSubscriptionId($subscriptionId)
                        ->setccType($params['md_cybersource']['payment_info']['cc_type'])
                        ->setcc_exp_month($params['md_cybersource']['payment_info']['cc_exp_month'])
                        ->setcc_exp_year($params['md_cybersource']['payment_info']['cc_exp_year'])
                        ->setcc_last4(substr($params['md_cybersource']['payment_info']['cc_number'],-4,4))
                        ->setCreatedAt(now())
                        ->setUpdatedAt(now())
                        ->save()
                        ;                        
                    }
                    Mage::getSingleton('core/session')->addSuccess(Mage::helper('md_cybersource')->__('Credit card saved successfully.'));
                }else{
                    $errorMessage=$this->_errorMessage[$code];
                    if($code=='102' || $code=='101'){
                        $errorDescription=$response->invalidField. '' . $response->missingField;
                    }
                    if(isset($errorDescription) && !empty($errorDescription)){
                        Mage::getSingleton("core/session")->addError("Error code: ".$code." : ".$errorMessage." : ". $errorDescription);    
                    }else{
                        Mage::getSingleton("core/session")->addError("Error code: ".$code." : ".$errorMessage);        
                    }

                }
                $this->_redirect('*/*/list');
            }
            catch(Exception $e){
                Mage::getSingleton('core/session')->addError($e->getMessage());
                $this->_redirect('*/*/list');
            }

        } 

        public function deleteAction(){
            $deleteCardId = $this->getRequest()->getParam("card_id",null);
            if(!empty($deleteCardId)){
                $cardModel=Mage::getModel('md_cybersource/cards')->load($deleteCardId);
                $subscriptionId=$cardModel->getData('subscription_id');
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if($subscriptionId && $customer->getId()){
                    $requestObject = new Varien_Object();
                    $requestObject->addData(array(
                        'customer_subscription_id'=>$subscriptionId
                    ));
                    try{
                        $response = Mage::getModel("md_cybersource/api_soap")
                        ->setInputData($requestObject)
                        ->deleteCustomerPaymentProfile();

                        $code = $response->reasonCode;
                        $deleteResultCode = $response->paySubscriptionDeleteReply->reasonCode;

                        if($code == '100' && $deleteResultCode == '100'){
                            $cardModel->delete();
                            Mage::getSingleton("core/session")->addSuccess(Mage::helper('md_cybersource')->__('Card deleted successfully.'));
                        }else{
                            $errorMessage=$this->_errorMessage[$code];
                            Mage::getSingleton("core/session")->addError($code." : ".$errorMessage);
                        }
                    }
                    catch(Exception $e){
                        Mage::getSingleton('core/session')->addError($e->getMessage());
                        $this->_redirect('*/*/list');
                    } 
                }
                else{
                    Mage::getSingleton("core/session")->addError(Mage::helper('md_cybersource')->__('Card does not exists.')); 
                }
            }
            $this->_redirect('*/*/list');
        } 

        public function editAction(){
            $editId = $this->getRequest()->getParam('card_id', null);
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $cardModel=Mage::getModel('md_cybersource/cards')->load($editId);
            if($editId && $cardModel->getId()){                                
                $title = "XXXX-". $cardModel->getData('cc_last4');
                $this->loadLayout();
                $this->getLayout()->getBlock("head")->setTitle(sprintf('Editing Card: %s',substr_replace($title, '-', 4, 0 )));
                $this->getLayout()->getBlock('md.cybersource.cards.edit')->setCard(Mage::helper('core')->jsonEncode($cardModel->getData()));
                $this->renderLayout();
            }else{
                Mage::getSingleton('core/session')->addError($this->__('Card information missing.'));
                $this->_redirect('*/*/list');
            }
        }

        public function updateAction(){
            $params = $this->getRequest()->getParams();
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $updateCardId = $params['md_cybersource']['card_id'];
            if(!empty($updateCardId)){
                $cardModel=Mage::getModel('md_cybersource/cards')->load($updateCardId);
                if($cardModel->getId()){
                    $subscriptionId=$cardModel->getData('subscription_id');
                    $requestObject = new Varien_Object();
                    $requestObject->addData(array(
                        'customer_id'=> $customer->getId(),
                        'customer_subscription_id'=> $subscriptionId
                    ));
                    $requestObject->addData($params['md_cybersource']['address_info']);
                    $requestObject->addData($params['md_cybersource']['payment_info']);
                    $response = Mage::getModel("md_cybersource/api_soap")
                    ->setInputData($requestObject)
                    ->updateCustomerProfile();

                    $code = $response->reasonCode;
                    $updateResultCode = $response->paySubscriptionUpdateReply->reasonCode;

                    if($code == '100' && $updateResultCode == '100'){
                        $oldCardData=$cardModel->getData();
                        unset($oldCardData['card_id']);
                        try{
                            $newSubscriptionId=$response->paySubscriptionUpdateReply->subscriptionID;
                            $model=Mage::getModel('md_cybersource/cards')
                            ->setData($oldCardData)                          
                            ;
                            $model->setData($params['md_cybersource']['address_info']);
                            $cardUpdateCheck=$params['md_cybersource']['payment_info'];
                            if($cardUpdateCheck['cc_action']=="existing"){
                                $model->setccType($oldCardData['cc_type'])
                                ->setcc_exp_month($oldCardData['cc_exp_month'])
                                ->setcc_exp_year($oldCardData['cc_exp_year'])
                                ->setcc_last4($oldCardData['cc_last4']);
                            }else{
                                $model->setccType($params['md_cybersource']['payment_info']['cc_type'])
                                ->setcc_exp_month($params['md_cybersource']['payment_info']['cc_exp_month'])
                                ->setcc_exp_year($params['md_cybersource']['payment_info']['cc_exp_year'])
                                ->setcc_last4(substr($params['md_cybersource']['payment_info']['cc_number'],-4,4));
                            }  
                            $model->setSubscriptionId($newSubscriptionId)
                            ->setCustomerId($customer->getId())
                            ->setUpdatedAt(now());                          
                            $model->save()
                            ;                        
                        } 
                        catch(Exception $e){
                            Mage::getSingleton('core/session')->addError($e->getMessage());
                            $this->_redirect('*/*/list');
                            return;
                        } 
                        $cardModel->delete();
                        Mage::getSingleton("core/session")->addSuccess(Mage::helper('md_cybersource')->__('Card updated successfully.'));
                    }else{
                        $errorMessage=$this->_errorMessage[$code];
                        Mage::getSingleton("core/session")->addError($code." : ".$errorMessage);
                    }
                }
            }
            $this->_redirect('*/*/list');
        }  
}