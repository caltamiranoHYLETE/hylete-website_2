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
    class MD_Cybersource_Block_Adminhtml_Cards_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget
    {
        protected $_customer = null;
        public function __construct() {
            parent::__construct();
            $this->setTemplate("md/cybersource/cards/form.phtml");
            $id = $this->getRequest()->getParam("id");
            $customer = Mage::getModel("customer/customer")->load($id);
            $this->_customer = $customer;
        }

        public function getCustomer()
        {
            return $this->_customer;
        }

        public function getCustomerCards()
        {
            $customerId = $this->getCustomer()->getId();
            $result = array();
            if(!empty($customerId)){
                $result=Mage::getModel('md_cybersource/cards')->getCollection()
                ->addFieldToFilter('customer_id',$customerId)
                ->getData();
            }
            return $result;
        }

        protected function _prepareLayout() {
            $this->setChild("md.cybersource.add.form",$this->getLayout()->createBlock("md_cybersource/adminhtml_cards_edit_tab_add"));
            return parent::_prepareLayout();
        }

        public function getAddCardHtml()
        {
            return $this->getChildHtml("md.cybersource.add.form");
        }
    }

