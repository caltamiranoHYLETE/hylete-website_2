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
    class MD_Cybersource_Model_Config
    {
        const CYBERSOURCE_ACTIVE = 'payment/md_cybersource/active';
        const CYBERSOURCE_TITLE = 'payment/md_cybersource/title';
        const CYBERSOURCE_MERCHANT_ID = 'payment/md_cybersource/merchantid';
        const CYBERSOURCE_TRANS_KEY = 'payment/md_cybersource/trans_key';
        const CYBERSOURCE_TEST = 'payment/md_cybersource/test';
        const CYBERSOURCE_PAYMENT_ACTION = 'payment/md_cybersource/payment_action';    
        const CYBERSOURCE_DEBUG = 'payment/md_cybersource/debug';
        const CYBERSOURCE_CCTYPES = 'payment/md_cybersource/cctypes';
        const CYBERSOURCE_CCV = 'payment/md_cybersource/useccv';
        const CYBERSOURCE_SOAP_GATEWAY_URL = 'payment/md_cybersource/soap_gateway_url';
        const CYBERSOURCE_SOAP_TEST_GATEWAY_URL = 'payment/md_cybersource/test_soap_gateway_url';
        const CYBERSOURCE_VALIDATION_TYPE = 'payment/md_cybersource/validation_mode';
        const CYBERSOURCE_CARD_SAVE_OPTIONAL = 'payment/md_cybersource/save_optional';
        const CYBERSOURCE_NEW_ORDER_STATUS = 'payment/md_cybersource/order_status';
        const CYBERSOURCE_ADDITIONAL_FIELD = 'payment/md_cybersource/merchantdefineddata';
        const CYBERSOURCE_ADDITIONAL_FIELD1 = 'payment/md_cybersource/merchantdefine_data1';
        const CYBERSOURCE_ADDITIONAL_FIELD2 = 'payment/md_cybersource/merchantdefine_data2';
        const CYBERSOURCE_ADDITIONAL_FIELD3 = 'payment/md_cybersource/merchantdefine_data3';
        const CYBERSOURCE_ADDITIONAL_FIELD4 = 'payment/md_cybersource/merchantdefine_data4';
        const CYBERSOURCE_ADDITIONAL_FIELD5 = 'payment/md_cybersource/merchantdefine_data5';
        const CYBERSOURCE_ADDITIONAL_FIELD6 = 'payment/md_cybersource/merchantdefine_data6';
        const CYBERSOURCE_ADDITIONAL_FIELD7 = 'payment/md_cybersource/merchantdefine_data7';

        const CYBERSOURCE_VALIDATION_NONE = 'none';
        const CYBERSOURCE_VALIDATION_TEST = 'testMode';
        const CYBERSOURCE_VALIDATION_LIVE = 'liveMode';

        protected $_storeId = null;
        protected $_backend = false;


        public function __construct() {
            $this->_backend = (Mage::app()->getStore()->isAdmin()) ? true: false;
            if( $this->_backend && Mage::registry('current_order') != false ) {
                $this->setStoreId( Mage::registry('current_order')->getStoreId() );
                Mage::getSingleton('adminhtml/session')->setCustomerStoreId(null);                 
            }
            elseif( $this->_backend && Mage::registry('current_invoice') != false ) {
                $this->setStoreId( Mage::registry('current_invoice')->getStoreId() );
                Mage::getSingleton('adminhtml/session')->setCustomerStoreId(null);                 
            }
            elseif( $this->_backend && Mage::registry('current_creditmemo') != false ) {
                $this->setStoreId( Mage::registry('current_creditmemo')->getStoreId() );
                Mage::getSingleton('adminhtml/session')->setCustomerStoreId(null);                 
            }
            elseif( $this->_backend && Mage::registry('current_customer') != false ) {  
                $this->setStoreId( Mage::registry('current_customer')->getStoreId() );
                Mage::getSingleton('adminhtml/session')->setCustomerStoreId(Mage::registry('current_customer')->getStoreId());                 
            }
            elseif( $this->_backend && Mage::getSingleton('adminhtml/session_quote')->getStoreId() > 0 ) {
                $this->setStoreId( Mage::getSingleton('adminhtml/session_quote')->getStoreId() );
                Mage::getSingleton('adminhtml/session')->setCustomerStoreId(null);                 
            }
            else {     
                $customerStoreSessionId=Mage::getSingleton('adminhtml/session')->getCustomerStoreId();                 
                if($this->_backend && $customerStoreSessionId !=null){
                    $this->setStoreId( $customerStoreSessionId );
                }
                else{
                    $this->setStoreId( Mage::app()->getStore()->getId() );
                }                
            }
            return $this;
        }

        public function setStoreId($storeId = 0){
            $this->_storeId = $storeId;
            return $this;
        }

        /**
        * This method will return whether Cybersource Payment is enabled or not in configuration. 
        *
        * @return boolean
        */
        public function getIsActive(){
            return (boolean)Mage::getStoreConfig(self::CYBERSOURCE_ACTIVE,$this->_storeId);
        }

        /**
        * This metod will return CYBERSOURCE Gateway url depending on test mode enabled or not.
        *
        * @return string
        */
        public function getGatewayUrl(){
            $isTestMode = $this->getIsTestMode();
            $gatewayUrl = null;
            $gatewayUrl = ($isTestMode) ? Mage::getStoreConfig(self::CYBERSOURCE_SOAP_TEST_GATEWAY_URL,$this->_storeId): Mage::getStoreConfig(self::CYBERSOURCE_SOAP_GATEWAY_URL,$this->_storeId);
            return $gatewayUrl;
        }

        /**
        * This methos will return Cybersource payment method title set by admin to display on onepage checkout payment step.
        *
        * @return string
        */
        public function getMethodTitle(){
            return (string)Mage::getStoreConfig(self::CYBERSOURCE_TITLE,$this->_storeId);
        }

        /**
        * This method will return merchant api login id set by admin in configuration. 
        *
        * @return string
        */
        public function getMerchantId(){
            return Mage::helper('core')->decrypt(Mage::getStoreConfig(self::CYBERSOURCE_MERCHANT_ID,$this->_storeId)) ;
        }

        /**
        * This method will return merchant api transaction key set by admin in configuration.
        *
        * @return string
        */
        public function getTransKey(){
            return Mage::helper('core')->decrypt(Mage::getStoreConfig(self::CYBERSOURCE_TRANS_KEY,$this->_storeId));
        }

        /**
        * This method will return whether test mode is enabled or not.
        *
        * @return boolean
        */
        public function getIsTestMode(){
            return (boolean)Mage::getStoreConfig(self::CYBERSOURCE_TEST,$this->_storeId);
        }

        /**
        * This will returne payment action whether it is authorized or authorize and capture.
        *
        * @return string
        */
        public function getPaymentAction(){
            return (string)Mage::getStoreConfig(self::CYBERSOURCE_PAYMENT_ACTION,$this->_storeId);
        }
        /**
        * This method will return whether debug is enabled from config.
        *
        * @return boolean
        */
        public function getIsDebugEnabled(){
            return (boolean)Mage::getStoreConfig(self::CYBERSOURCE_DEBUG,$this->_storeId);
        }    

        /**
        * This method return whether card verification is enabled or not.
        *
        * @return boolean
        */
        public function isCardVerificationEnabled(){
            return (boolean)Mage::getStoreConfig(self::CYBERSOURCE_CCV,$this->_storeId);
        }

        /**
        * Cybersource validation mode.
        *
        * @return string
        */
        public function getValidationMode(){
            return (string)Mage::getStoreConfig(self::CYBERSOURCE_VALIDATION_TYPE,$this->_storeId);
        }

        /**
        * Method which will return whether customer must save credit card as profile of not.
        *
        * @return boolean
        */
        public function getSaveCardOptional(){
            return (boolean)Mage::getStoreConfig(self::CYBERSOURCE_CARD_SAVE_OPTIONAL,$this->_storeId);
        }    

        public function getCcTypes(){
            return Mage::getStoreConfig(self::CYBERSOURCE_CCTYPES,$this->_storeId);
        }

        public function getAdditonalFieldActive(){
            return (boolean)Mage::getStoreConfig(self::CYBERSOURCE_ADDITIONAL_FIELD,$this->_storeId);
        }

        public function getAdditonalField1(){
            return Mage::getStoreConfig(self::CYBERSOURCE_ADDITIONAL_FIELD1,$this->_storeId);
        }       

        public function getAdditonalField2(){
            return Mage::getStoreConfig(self::CYBERSOURCE_ADDITIONAL_FIELD2,$this->_storeId);
        }

        public function getAdditonalField3(){
            return Mage::getStoreConfig(self::CYBERSOURCE_ADDITIONAL_FIELD3,$this->_storeId);
        }

        public function getAdditonalField4(){
            return Mage::getStoreConfig(self::CYBERSOURCE_ADDITIONAL_FIELD4,$this->_storeId);
        }

        public function getAdditonalField5(){
            return Mage::getStoreConfig(self::CYBERSOURCE_ADDITIONAL_FIELD5,$this->_storeId);
        }

        public function getAdditonalField6(){
            return Mage::getStoreConfig(self::CYBERSOURCE_ADDITIONAL_FIELD6,$this->_storeId);
        }

        public function getAdditonalField7(){
            return Mage::getStoreConfig(self::CYBERSOURCE_ADDITIONAL_FIELD7,$this->_storeId);
        }
    }

