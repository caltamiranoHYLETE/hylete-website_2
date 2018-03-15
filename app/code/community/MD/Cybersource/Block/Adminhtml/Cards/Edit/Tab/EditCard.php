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
    class MD_Cybersource_Block_Adminhtml_Cards_Edit_Tab_EditCard extends MD_Cybersource_Block_Adminhtml_Cards_Edit_Tab_Add
    {
        public function __construct() {
            parent::__construct();
            $this->setTemplate("md/cybersource/cards/edit.phtml");

            $customerId = $this->getRequest()->getParam("id",null);
            $customer = Mage::getModel("customer/customer")->load($customerId);
            $this->setCustomer($customer);

            $editId = $this->getRequest()->getParam("customer_card_id",null);
            if(!is_null($editId)){
                $cardModel=Mage::getModel('md_cybersource/cards')->load($editId);
                if($cardModel->getId()){
                    $this->setCard(Mage::helper('core')->jsonEncode($cardModel->getData()));
                }
            }
        }
    }

