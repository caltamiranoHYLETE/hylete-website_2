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
    class MD_Cybersource_Model_Api_Soapclient extends SoapClient
    {       
        private $merchantId;
        private $transactionKey; 

        function __construct($options=array()){     
            $propertiesWsdl = $this->getConfigModel()->getGatewayUrl();
            parent::__construct($propertiesWsdl, $options);
            $this->merchantId = $this->getConfigModel()->getMerchantId();
            $this->transactionKey ="". $this->getConfigModel()->getTransKey()."";
            $nameSpace = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
            $soapUsername = new SoapVar(
                $this->merchantId,
                XSD_STRING,
                NULL,
                $nameSpace,
                NULL,
                $nameSpace
            );
            $soapPassword = new SoapVar(
                $this->transactionKey,
                XSD_STRING,
                NULL,
                $nameSpace,
                NULL,
                $nameSpace
            );
            $auth = new stdClass();
            $auth->Username = $soapUsername;
            $auth->Password = $soapPassword; 
            $soapAuth = new SoapVar(
                $auth,
                SOAP_ENC_OBJECT,
                NULL, $nameSpace,
                'UsernameToken',
                $nameSpace
            ); 
            $token = new stdClass();
            $token->UsernameToken = $soapAuth; 
            $soapToken = new SoapVar(
                $token,
                SOAP_ENC_OBJECT,
                NULL,
                $nameSpace,
                'UsernameToken',
                $nameSpace
            );
            $security =new SoapVar(
                $soapToken,
                SOAP_ENC_OBJECT,
                NULL,
                $nameSpace,
                'Security',
                $nameSpace
            );
            $header = new SoapHeader($nameSpace, 'Security', $security, true); 
            $this->__setSoapHeaders(array($header)); 
        }

        /**
        * @return string The client's merchant ID.
        */
        public function getMerchantId(){
            return $this->merchantId;
        }

        /**
        * @return string The client's transaction key.
        */
        public function getTransactionKey(){
            return $this->transactionKey;
        }

        public function getConfigModel(){
            $model = Mage::getSingleton("md_cybersource/config");
            return $model;
        }

        public function createRequest($merchantReferenceCode){
            $request = new stdClass();
            $request->merchantID = $this->merchantId;
            $request->merchantReferenceCode = $merchantReferenceCode;
            $request->clientLibrary = "CyberSource PHP 1.0.0";
            $request->clientLibraryVersion = phpversion();
            $request->clientEnvironment = php_uname();
            return $request;
        } 
    }