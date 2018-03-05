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
class MD_Cybersource_Block_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate("md/cybersource/checkout/onepage/payment/method/form.phtml");
    }

    public function getCustomerSavedCards(){       
        $cards = array();
        if(Mage::getModel("md_cybersource/config")->getIsActive()){
            if( Mage::app()->getStore()->isAdmin() ) {
                $customerId=Mage::getSingleton('adminhtml/session_quote')->getCustomer()->getId();
            }else{
                $customer = $this->getCustomer();
                $customerId=$customer->getId();    
            }                
            if(!empty($customerId)){
                $cards= Mage::getModel('md_cybersource/cards')->getCollection()
                ->addFieldToFilter('customer_id',$customerId)
                ->getData();
            }
        }
        return $cards;
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

    public function getCcAvailableTypes()
    {
        $types = Mage::getModel('md_cybersource/cardconfig')->getCcTypesCybersource();

        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

}

