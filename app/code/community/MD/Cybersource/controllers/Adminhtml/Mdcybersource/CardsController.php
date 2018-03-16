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
    class MD_Cybersource_Adminhtml_Mdcybersource_CardsController extends Mage_Adminhtml_Controller_Action
    {
        protected $_fieldsValidation = array(
            'firstname'=>'NotEmpty',
            'lastname'=>'NotEmpty',
            'company'=>'',
            'street'=>'NotEmpty',
            'city'=>'NotEmpty',
            'region_id'=>'',
            'state'=>'',
            'postcode'=>'NotEmpty',
            'country_id'=>'NotEmpty',
            'telephone'=>'NotEmpty',
            'email'=>'NotEmpty',            
        );
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

        public function indexAction(){
            $this->loadLayout();
            $this->renderLayout();
        }

        public function editAction(){
            if($this->getRequest()->getParam('customer_card_id') > 0){
                echo $this->getLayout()->createBlock("md_cybersource/adminhtml_cards_edit_tab_editCard")->toHtml();
            }else{
                echo $this->getLayout()->createBlock("md_cybersource/adminhtml_cards_edit_tab_add")->toHtml(); 
            }
        }

        public function saveAction(){
            $params = $this->getRequest()->getParams();            
            //we are validating data specifically for blank entries so it can be avoided before send to authorize payment gateway.
            $errorMessage = $this->validateData($params);
            if(is_string($errorMessage) && strlen($errorMessage) > 0){
                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$errorMessage.'</span></li></ul></li></ul></div></td></tr>';
            }else{
                $append = '';
                $formKey = $this->getRequest()->getParam('form_key');
                $customer = Mage::getModel('customer/customer')->load( $this->getRequest()->getParam('id') );
                if($customer->getId()){
                    $requestObject = new Varien_Object();
                    try{
                        $requestObject = new Varien_Object();
                        $requestObject->addData(array(
                            'customer_id'=> $customer->getId(),
                            'email'=> $customer->getEmail()
                        ));
                        $requestObject->addData($params);
                        $response = Mage::getModel("md_cybersource/api_soap")
                        ->setInputData($requestObject)
                        ->createCustomerProfile();
                        $code=$response->reasonCode;
                        $profileResponsecheck=$response->paySubscriptionCreateReply->reasonCode;

                        if($code == '100' && $profileResponsecheck == '100'){
                            try{
                                $subscriptionId=$response->paySubscriptionCreateReply->subscriptionID;
                                $model=Mage::getModel('md_cybersource/cards')
                                ->setData($params)
                                ->setCustomerId($customer->getId())
                                ->setSubscriptionId($subscriptionId)                        
                                ->setcc_last4(substr($params['cc_number'],-4,4))
                                ->setCreatedAt(now())
                                ->setUpdatedAt(now())
                                ->save()
                                ;    
                            }
                            catch(Exception $e){
                                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$e->getMessage().'</span></li></ul></li></ul></div></td></tr>';
                            }      
                            $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="success-msg"><ul><li><span>Credit card saved successfully.</span></li></ul></li></ul></div></td></tr>';
                        }else{
                            $responseErrorMessage=$this->_errorMessage[$code];
                            if($code=='102' || $code=='101'){
                                $errorDescription=$response->invalidField. '' . $response->missingField;
                                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$responseErrorMessage. " : " .$errorDescription.'</span></li></ul></li></ul></div></td></tr>';
                            }else{
                                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$responseErrorMessage.'</span></li></ul></li></ul></div></td></tr>';
                            }
                        }
                    }catch(Exception $e){
                        $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>Unable to process the request.Please refresh the page and try again.</span></li></ul></li></ul></div></td></tr>';
                    }
                }else{
                    $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>Unable to find card id in database.</span></li></ul></li></ul></div></td></tr>'; 
                }
            }            
            echo $append.Mage::app()->getLayout()->createBlock('md_cybersource/adminhtml_cards_edit_tab_form')->toHtml();
        }

        public function deleteAction(){
            $deleteCardId = $this->getRequest()->getParam('customer_card_id',null);
            $formKey = $this->getRequest()->getParam('form_key');
            $append = '';
            $customer = Mage::getModel('customer/customer')->load( $this->getRequest()->getParam('id') );
            if( $deleteCardId > 0 && $formKey == Mage::getSingleton('core/session')->getFormKey() ) {
                $cardModel=Mage::getModel('md_cybersource/cards')->load($deleteCardId);
                $subscriptionId=$cardModel->getData('subscription_id');
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
                        $responseArray['reasonCode'] = $code;
                        if($code == '100' && $deleteResultCode == '100'){
                            try{
                                $cardModel->delete();
                            }
                            catch(Exception $e){
                                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$e->getMessage().'</span></li></ul></li></ul></div></td></tr>';
                            }
                            $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="success-msg"><ul><li><span>Credit card deleted successfully.</span></li></ul></li></ul></div></td></tr>';
                        }
                        else{
                            $responseErrorMessage=$this->_errorMessage[$code];
                            if($code=='102' || $code=='101'){
                                $errorDescription=$response->invalidField. '' . $response->missingField;
                                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$responseErrorMessage. " : " .$errorDescription.'</span></li></ul></li></ul></div></td></tr>';
                            }else{
                                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$responseErrorMessage.'</span></li></ul></li></ul></div></td></tr>';
                            }
                        }
                    }
                    catch(Exception $e){
                        $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>Unable to process the request.Please refresh the page and try again.</span></li></ul></li></ul></div></td></tr>';
                    }
                }
            }
            else{
                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>Unable to find card id in database.</span></li></ul></li></ul></div></td></tr>'; 
            }
            echo $append.Mage::app()->getLayout()->createBlock('md_cybersource/adminhtml_cards_edit_tab_form')->toHtml();
        }

        protected function validateData($fields){
            $message = '';
            $errorKeys = array();
            $labels = array(
                'firstname'=>$this->__('First Name'),'lastname'=>$this->__('Last Name'),'company'=>$this->__('Company'),'street'=>$this->__('Street Address'),
                'city'=>$this->__('City'),'region_id'=>$this->__('State/Province'),'state'=>$this->__('State'),'postcode'=>$this->__('Zip Code'),'country_id'=>$this->__('Country'),
                'telephone'=>$this->__('Telephone'),'fax'=>$this->__('Fax'),'cc_type'=>$this->__('Credit Card Type'),'cc_number'=>$this->__('Credit Card Number'),'cc_exp_month'=>$this->__('Expiration Month'),
                'cc_exp_year'=>$this->__('Expiration Year'),'cc_cid'=>$this->__('Card Verification Number'),
            );
            $requiredStateCountry=explode(",",Mage::getStoreConfig('general/region/state_required'));
            $cardCountry=$fields['country_id'];
            in_array($cardCountry,$requiredStateCountry)?$this->_fieldsValidation['region_id']='NotEmpty':'';

            $cardAction=$fields['cc_action'];
            if($cardAction!='existing'){
                $this->_fieldsValidation['cc_type']= 'NotEmpty';
                $this->_fieldsValidation['cc_number']= 'NotEmpty';
                $this->_fieldsValidation['cc_exp_month']= 'NotEmpty';
                $this->_fieldsValidation['cc_exp_year']= 'NotEmpty';
            }

            foreach($fields as $key=>$value){
                if(array_key_exists($key,$this->_fieldsValidation) && strlen($this->_fieldsValidation[$key]) > 0){
                    if (!Zend_Validate::is($value, $this->_fieldsValidation[$key])) {
                        $errorKeys[] = $labels[$key];
                    }
                }
            }
            if(count($errorKeys) > 0){
                $message .= sprintf("Missing required fields: %s",implode(', ',$errorKeys));
            }
            return $message;
        }

        public function updateAction(){
            $params = $this->getRequest()->getParams();
            $errorMessage=$this->cardValidataion($params);
            if(is_string($errorMessage) && strlen($errorMessage) > 0){
                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$errorMessage.'</span></li></ul></li></ul></div></td></tr>';
            }else{
                if(!empty($params['customer_id'])){
                    $customer = Mage::getModel('customer/customer')->load($params['customer_id']);
                    $updateCardId = $params['card_id'];
                    if(!empty($updateCardId)){
                        try{
                            $cardModel=Mage::getModel('md_cybersource/cards')->load($updateCardId);
                            if($cardModel->getId()){
                                $subscriptionId=$cardModel->getData('subscription_id');
                                $requestObject = new Varien_Object();
                                $requestObject->addData(array(
                                    'customer_id'=> $customer->getId(),
                                    'customer_subscription_id'=> $subscriptionId
                                ));
                                $requestObject->addData($params);
                                $response = Mage::getModel("md_cybersource/api_soap")
                                ->setInputData($requestObject)
                                ->updateCustomerProfile();

                                $code = $response->reasonCode;
                                $updateResultCode = $response->paySubscriptionUpdateReply->reasonCode;

                                if($code == '100' && $updateResultCode == '100'){
                                    $oldCardData=$cardModel->getData();
                                    unset($oldCardData['card_id']);
                                    unset($params['card_id']);
                                    try{
                                        $newSubscriptionId=$response->paySubscriptionUpdateReply->subscriptionID;
                                        $model=Mage::getModel('md_cybersource/cards')
                                        ->setData($oldCardData)                                                              
                                        ;
                                        $model->setData($params);
                                        $cardUpdateCheck=$params['cc_action'];
                                        if($cardUpdateCheck=="existing"){
                                            $model->setccType($oldCardData['cc_type'])
                                            ->setcc_exp_month($oldCardData['cc_exp_month'])
                                            ->setcc_exp_year($oldCardData['cc_exp_year'])
                                            ->setcc_last4($oldCardData['cc_last4']);
                                        }else{
                                            $model->setccType($params['cc_type'])
                                            ->setcc_exp_month($params['cc_exp_month'])
                                            ->setcc_exp_year($params['cc_exp_year'])
                                            ->setcc_last4(substr($params['cc_number'],-4,4));
                                        }  
                                        $model->setSubscriptionId($newSubscriptionId)
                                        ->setCustomerId($customer->getId())
                                        ->setUpdatedAt(now())                         
                                        ->save();
                                    } 
                                    catch(Exception $e){
                                        $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$e->getMessage().'</span></li></ul></li></ul></div></td></tr>';
                                    } 
                                    $cardModel->delete();
                                    $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="success-msg"><ul><li><span>Credit card updated successfully.</span></li></ul></li></ul></div></td></tr>';
                                }
                                else{
                                    $responseErrorMessage=$this->_errorMessage[$code];
                                    if($code=='102' || $code=='101'){
                                        $errorDescription=$response->invalidField. '' . $response->missingField;
                                        $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$responseErrorMessage. " : " .$errorDescription.'</span></li></ul></li></ul></div></td></tr>';
                                    }else{
                                        $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>'.$responseErrorMessage.'</span></li></ul></li></ul></div></td></tr>';
                                    }
                                }

                            }else{
                                $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>Unable to find card id in database.</span></li></ul></li></ul></div></td></tr>'; 
                            }
                        }   
                        catch(Exception $e){
                            $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>Unable to process the request.Please refresh the page and try again.</span></li></ul></li></ul></div></td></tr>';
                        }
                    }else{
                        $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>Unable to find card id.</span></li></ul></li></ul></div></td></tr>'; 
                    }
                }
                else{
                    $append .= '<tr><td colspan="2"><div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>Unable to find customer id.</span></li></ul></li></ul></div></td></tr>'; 
                }
            }
            echo $append.Mage::app()->getLayout()->createBlock('md_cybersource/adminhtml_cards_edit_tab_form')->setData('update_Data',$params)->toHtml();
        }

        protected function cardValidataion($fields){
            $cardRequired=array(
                'firstname'=>'NotEmpty',
                'lastname'=>'NotEmpty',
                'company'=>'',
                'street'=>'NotEmpty',
                'city'=>'',
                'region_id'=>'',
                'state'=>'',
                'postcode'=>'NotEmpty',
                'country_id'=>'NotEmpty',
                'phone'=>'NotEmpty',
            );
            $cardAction=$fields['cc_action'];
            if($cardAction!='existing'){
                $cardRequired['cc_type']= 'NotEmpty';
                $cardRequired['cc_number']= 'NotEmpty';
                $cardRequired['cc_exp_month']= 'NotEmpty';
                $cardRequired['cc_exp_year']= 'NotEmpty';
            }
            $requiredStateCountry=explode(",",Mage::getStoreConfig('general/region/state_required'));
            $cardCountry=$fields['country_id'];
            in_array($cardCountry,$requiredStateCountry)?$cardRequired['region_id']='NotEmpty':'';

            $message = '';
            $errorKeys = array();
            $labels = array(
                'firstname'=>$this->__('First Name'),'lastname'=>$this->__('Last Name'),'company'=>$this->__('Company'),'street'=>$this->__('Street Address'),
                'city'=>$this->__('City'),'region_id'=>$this->__('State/Province'),'state'=>$this->__('State'),'postcode'=>$this->__('Telephone'),'country_id'=>$this->__('Country'),
                'phone'=>$this->__('Telephone'),'cc_type'=>$this->__('Credit Card Type'),'cc_number'=>$this->__('Credit Card Number'),'cc_exp_month'=>$this->__('Expiration Month'),
                'cc_exp_year'=>$this->__('Expiration Year'),'cc_cid'=>$this->__('Card Verification Number'),
            );
            foreach($fields as $key=>$value){
                if(array_key_exists($key,$cardRequired) && strlen($cardRequired[$key]) > 0){
                    if (!Zend_Validate::is($value, $cardRequired[$key])) {
                        $errorKeys[] = $labels[$key];
                    }
                }
            }
            if(count($errorKeys) > 0){
                $message .= sprintf("Missing required fields: <strong>%s</strong>",implode(', ',$errorKeys));
            }
            return $message;
        } 
}