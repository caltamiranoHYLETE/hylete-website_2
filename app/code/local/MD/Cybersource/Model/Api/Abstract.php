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
    class MD_Cybersource_Model_Api_Abstract extends Varien_Object
    {       
        protected $_configModel = "md_cybersource/config";

        protected $_inputData = array();

        protected $_responseData = array();

        protected $_merchantId = null;
        protected $_transKey = null;
        protected $_apiGatewayUrl = null;
        protected $_cvvEnabled = null;
        protected $_additionalfield=null;
        protected $_additionalfield1=null;
        protected $_additionalfield2=null;
        protected $_additionalfield3=null;
        protected $_additionalfield4=null;
        protected $_additionalfield5=null;
        protected $_additionalfield6=null;
        protected $_additionalfield7=null;
        protected $_cardCode=array(
            'VI'=>'001',
            'MC'=>'002',
            'AE'=>'003',
            'DI'=>'004',
            'JCB'=>'007',
            'DC'=>'005',
            'MAESTRO'=>'042',
            'SWITCH'=>'024'         
        );

        public function __construct() {
            $this->_merchantId = $this->getConfigModel()->getMerchantId();
            $this->_transKey = $this->getConfigModel()->getTransKey();
            $this->_apiGatewayUrl = $this->getConfigModel()->getGatewayUrl();
            $this->_cvvEnabled = $this->getConfigModel()->isCardVerificationEnabled();
            $this->_additionalfield=$this->getConfigModel()->getAdditonalFieldActive();
            $this->_additionalfield1=$this->getConfigModel()->getAdditonalField1();
            $this->_additionalfield2=$this->getConfigModel()->getAdditonalField2();
            $this->_additionalfield3=$this->getConfigModel()->getAdditonalField3();
            $this->_additionalfield4=$this->getConfigModel()->getAdditonalField4();
            $this->_additionalfield5=$this->getConfigModel()->getAdditonalField5();
            $this->_additionalfield6=$this->getConfigModel()->getAdditonalField6();
            $this->_additionalfield7=$this->getConfigModel()->getAdditonalField7();
        }

        public function setInputData($input = null){
            $this->_inputData = $input;
            return $this;
        }

        public function getInputData(){
            return $this->_inputData;
        }

        public function setResponseData($response = array()){
            $this->_responseData = $response;
            return $this;
        }

        public function getResponseData(){
            return $this->_responseData;
        }

        public function getConfigModel(){
            $model = Mage::getSingleton("md_cybersource/config");
            return $model;
        }
}