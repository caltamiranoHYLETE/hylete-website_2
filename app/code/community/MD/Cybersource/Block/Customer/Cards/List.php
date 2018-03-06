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
    class MD_Cybersource_Block_Customer_Cards_List extends Mage_Core_Block_Template
    {
        protected $_customer = null;
        public function __construct() {
            parent::__construct();
            $this->setTemplate("md/cybersource/customer/cards/list.phtml");
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        public function getCustomer(){
            return $this->_customer;
        }

        public function getCustomerCards(){
            $customerId = $this->getCustomer()->getId();
            $result = array();
            if(!empty($customerId)){
                $result=Mage::getModel('md_cybersource/cards')->getCollection()
                ->addFieldToFilter('customer_id',$customerId)
                ->getData();
            }
            return $result;
        }

        public function getBackUrl(){
            return $this->getUrl('customer/account/');
        }

        public function getAddCardUrl(){
            return $this->getUrl('*/*/add');
        }

        public function getPostUrl(){
            return $this->getUrl('*/*/edit');
        }

        public function getDeleteAction(){
            return $this->getUrl('*/*/delete');
        }
}