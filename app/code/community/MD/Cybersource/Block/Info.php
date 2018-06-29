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
    class MD_Cybersource_Block_Info extends Mage_Payment_Block_Info_Cc
    {
        protected $_isCheckoutProgressBlockFlag = true;
        protected function _construct(){
            parent::_construct();
            $this->setTemplate('md/cybersource/info/cc.phtml');
        }

        public function getInfo(){
            if ($this->hasCardInfoObject()) {
                return $this->getCardInfoObject();
            }
            return parent::getInfo();
        }

        /**
        * Set checkout progress information block flag
        * to avoid showing credit card information from payment quote
        * in Previously used card information block
        *
        * @param bool $flag
        * @return Mage_Paygate_Block_Authorizenet_Info_Cc
        */
        public function setCheckoutProgressBlock($flag){
            $this->_isCheckoutProgressBlockFlag = $flag;
            return $this;
        }

        public function getCards(){
            $cardsData = $this->getMethod()->getCardsStorage()->getCards();
            $cards = array();
            if (is_array($cardsData)) {
                foreach ($cardsData as $cardInfo) {
                    $data = array();
                    $lastTransactionId=$this->getData('info')->getData('cc_trans_id');
                    $cardTransactionId=$cardInfo->getTransactionId();
                    if($lastTransactionId==$cardTransactionId){
                        if ($cardInfo->getProcessedAmount()) {
                            $amount = Mage::helper('core')->currency($cardInfo->getProcessedAmount(), true, false);
                            $data[Mage::helper('md_cybersource')->__('Processed Amount')] = $amount;
                        }
                        if ($cardInfo->getBalanceOnCard() && is_numeric($cardInfo->getBalanceOnCard())) {
                            $balance = Mage::helper('core')->currency($cardInfo->getBalanceOnCard(), true, false);
                            $data[Mage::helper('md_cybersource')->__('Remaining Balance')] = $balance;
                        }
                        if( Mage::app()->getStore()->isAdmin() ) {
                            if ($cardInfo->getApprovalCode() && is_string($cardInfo->getApprovalCode())) {
                                $data[Mage::helper('md_cybersource')->__('Approval Code')] = $cardInfo->getApprovalCode();
                            }
                            if ($cardInfo->getMethod() && is_numeric($cardInfo->getMethod())) {
                                $data[Mage::helper('md_cybersource')->__('Method')] = ($cardInfo->getMethod() == 'CC') ? $this->__('Credit Card'): $this->__('eCheck');
                            }

                            if($cardInfo->getLastTransId() && $cardInfo->getLastTransId())
                            {
                                $data[Mage::helper('md_cybersource')->__('Transaction Id')] = str_replace(array('-capture','-void','-refund'), '', $cardInfo->getLastTransId());
                            }

                            if($cardInfo->getAvsResultCode() && is_string($cardInfo->getAvsResultCode())){
                                $data[Mage::helper('md_cybersource')->__('AVS Response')] = Mage::helper('md_cybersource')->getAvsLabel($cardInfo->getAvsResultCode());     
                            }

                            if($cardInfo->getCVNResultCode() && is_string($cardInfo->getCVNResultCode())){
                                $data[Mage::helper('md_cybersource')->__('CVN Response')] = Mage::helper('md_cybersource')->getCvnLabel($cardInfo->getCVNResultCode());
                            }

                            if($cardInfo->getCardCodeResponseCode() && is_string($cardInfo->getreconciliationID())){
                                $data[Mage::helper('md_cybersource')->__('CCV Response')] = $cardInfo->getCardCodeResponseCode();
                            }

                            if($cardInfo->getMerchantReferenceCode() && is_string($cardInfo->getMerchantReferenceCode())){
                                $data[Mage::helper('md_cybersource')->__('Merchant Reference Code')] = $cardInfo->getMerchantReferenceCode();
                            }
                        }
                        $this->setCardInfoObject($cardInfo);
                        $cards[] = array_merge($this->getSpecificInformation(), $data);
                        $this->unsCardInfoObject();
                        $this->_paymentSpecificInformation = null;
                    }
                }
            }
            if ($this->getInfo()->getCcType() && $this->_isCheckoutProgressBlockFlag && count($cards) == 0) {
                $cards[] = $this->getSpecificInformation();
            }
            return $cards;
        }
    }

