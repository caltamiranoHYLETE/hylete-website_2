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
    class MD_Cybersource_Helper_Data extends Mage_Core_Helper_Abstract
    {
        protected $_errorMessage=array(
            "100"=> "Successful transaction",
            "101"=>"The request is missing one or more required fields",
            "102"=>"One or more fields in the request contains invalid data",
            "104"=>"Resend the request with a unique merchant reference code",
            "110"=>"Only a partial amount was approved",
            "150"=>"General system failure",
            "151"=>"The request was received but there was a server timeout. This error does not include timeouts between the client and the server",
            "152"=>"The request was received, but a service did not finish running in time",
            "200"=>"The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the Address Verification System check",
            "201"=>"The issuing bank has questions about the request. You will not receive an authorization code programmatically, but you can obtain one verbally by calling the processor",
            "202"=>"Expired card,Request a different card or other form of payment",
            "203"=>"General decline of the card. Request a different card or other form of payment",
            "204"=>"Insufficient funds in the account,Request a different card or other form of payment",
            "205"=>"Stolen or lost card",
            "207"=>"Issuing bank unavailable,Wait a few minutes and resend the request",
            "208"=>"Inactive card or card not authorized for card-not-present transactions,Request a different card or other form of payment",
            "209"=>"CVN did not match",
            "210"=>"The card has reached the credit limit",
            "211"=>"Invalid card verification number",
            "220"=>"The processor declined the request based on a general issue with the customer’s account",
            "221"=>"The customer matched an entry on the processor’s negative file,Review the order and contact the payment processor",
            "222"=>"The customer’s bank account is frozen",
            "230"=>"The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the CVN check",
            "231"=>"Invalid account number",
            "232"=>"The card type is not accepted by the payment processor",
            "233"=>"General decline by the processor",
            "234"=>"There is a problem with your CyberSource merchant configuration",
            "236"=>"Processor failure",
            "237"=>"The authorization has already been reversed",
            "238"=>"The authorization has already been captured",
            "239"=>"The requested transaction amount must match the previous transaction amount",
            "240"=>"The card type sent is invalid or does not correlate with the card number",
            "241"=>"The request ID is invalid",
            "242"=>"You requested a capture, but there is no corresponding, unused authorization record,Request a new authorization, and if successful, proceed with the capture",
            "243"=>"The transaction has already been settled or reversed",
            "246"=>"The capture or credit is not voidable because the capture or credit information has already been submitted to your processor OR You requested a void for a type of transaction that cannot be voided",
            "247"=>"You requested a credit for a capture that was previously voided",
            "250"=>"The request was received, but there was a timeout at the payment processor,do not resend the request until you have reviewed the transaction status in the Business Center",            
            "254"=>"Stand-alone credits are not allowed"
        );

        protected $_avsResponses = array(
            'A'    =>'Street address matches, but 5-digit and 9-digit postal code do not match.',
            'B'    => 'Street address matches, but postal code not verified.',
            'C'    => 'Street address and postal code do not match.',
            'D'    => 'Street address and postal code match.',
            'M'   =>  'Street address and postal code match.',
            'E'    => 'AVS data is invalid or AVS is not allowed for this card type.',
            'F'    => 'Card members name does not match, but billing postal code matches.',
            'F'    => 'Card members name does not match, but billing postal code matches.',
            'G'    => 'Issuing bank does not support AVS.',
            'H'    => 'Card members name does not match. Street address and postal code match.',
            'I'    => 'Address not verified.',
            'K'    => 'Card members name and billing postal code match, but billing address does not match.',
            'M'    => 'Street address and postal code do not match.',
            'N'    => 'Card members name, street address and postal code do not match.',
            'P'    => 'Postal code matches, but street address not verified.',
            'R'    => 'System unavailable.',
            'S'    => 'Issuing bank does not support AVS.',
            'T'    => 'Card members name does not match, but street address matches.',
            'U'    => 'Address information unavailable.',
            'W'    => 'Street address does not match, but 9-digit postal code matches.',
            'X'    => 'Street address and 9-digit postal code match.',
            'Y'    => 'Street address and 5-digit postal code match.',
            'Z'    => 'Street address does not match, but 5-digit postal code matches.',
            '1'    => 'AVS is not supported for this processor or card type.',
            '2' => 'The processor returned an unrecognized value for the AVS response.',
        );

        protected $_cvnResponses = array(
            'D'    => 'Transaction determined suspicious by issuing bank.',
            'I' => 'Card verification number failed processors data validation check.',
            'M' => 'Card verification number matched.',
            'N' => 'Card verification number not matched.',
            'P' => 'Card verification number not processed by processor for unspecified reason.',
            'S' => 'Card verification number is on the card but was not included in the request.',
            'U'    => 'Card verification is not supported by the issuing bank.',
            'X'    => 'Card verification is not supported by the card association.',
            '1'    => 'Card verification is not supported for this processor or card type.',
            '2'    => 'Unrecognized result code returned by processor for card verification response.',
            '3'    => 'No result code returned by processor.',
        );

        /**
        * Return formated addres as per customer address configuration.
        *
        * @param   object $card
        * @return  string
        */
        public function getFormatedAddress($card)
        {
            $address = new Varien_Object();            
            $regionId = $card['region_id'];
            $regionName = ($regionId) ? Mage::getModel("directory/region")->load($regionId)->getName(): $card['state'];
            $address->addData(array(
                'firstname'=>(string) $card['firstname'],
                'lastname'=>(string)$card['lastname'],
                'company'=>(string)$card['company'],
                'street1'=>(string)$card['street'],
                'city'=>(string)$card['city'],
                'region'=>(string)$regionName,
                'postcode'=>(string)$card['postcode'],
                'telephone'=>(string)$card['telephone'],
                'country'=>(string)$card['country_id']
            ));
            $template = Mage::getStoreConfig("customer/address_templates/html");
            $formater   = new Varien_Filter_Template();
            $formater->setVariables($address->getData());
            return $formater->filter($template);
        }

        /**
        * Return transaction message based on payment gateway response and transaction type.
        *
        * @param  Mage_Payment_Model_Info $payment
        * @param  string $requestType
        * @param  string $lastTransactionId
        * @param  Varien_Object $card
        * @param float $amount
        * @param string $exception
        * @return bool|string
        */
        public function getTransactionMessage($payment, $requestType, $lastTransactionId, $card, $amount = false,
            $exception = false
        ) {
            return $this->getExtendedTransactionMessage(
                $payment, $requestType, $lastTransactionId, $card, $amount, $exception
            );
        }

        /**
        * Return message for gateway transaction request
        *
        * @param  Mage_Payment_Model_Info $payment
        * @param  string $requestType
        * @param  string $lastTransactionId
        * @param  Varien_Object $card
        * @param float $amount
        * @param string $exception
        * @param string $additionalMessage Custom message, which will be added to the end of generated message
        * @return bool|string
        */
        public function getExtendedTransactionMessage($payment, $requestType, $lastTransactionId, $card, $amount = false,
            $exception = false, $additionalMessage = false
        ) {
            $operation = $this->_getOperation($requestType);

            if (!$operation) {
                return false;
            }

            if ($amount) {
                $amount = $this->__('amount %s', $this->_formatPrice($payment, $amount));
            }

            if ($exception) {
                $result = $this->__('failed');
            } else {
                $result = $this->__('successful');
            }

            $card = $this->__('Credit Card: xxxx-%s', $card->getCcLast4());

            $pattern = '%s %s %s - %s.';
            $texts = array($card, $amount, $operation, $result);

            if (!is_null($lastTransactionId)) {
                $pattern .= ' %s.';
                $texts[] = $this->__('Cybersource Transaction ID %s', $lastTransactionId);
            }

            if ($additionalMessage) {
                $pattern .= ' %s.';
                $texts[] = $additionalMessage;
            }
            $pattern .= ' %s';
            $texts[] = $exception;

            return call_user_func_array(array($this, '__'), array_merge(array($pattern), $texts));
        }

        public function getAvsLabel($avs){
            if( isset( $this->_avsResponses[ $avs ] ) ) {
                return $this->__( sprintf( '%s (%s)', $avs, $this->_avsResponses[ $avs ] ) );
            }
            return $avs;
        }

        public function getCvnLabel($cvn)
        {
            if( isset( $this->_cvnResponses[ $cvn ] ) ) {
                return $this->__( sprintf( '%s (%s)', $cvn, $this->_cvnResponses[ $cvn ] ) );
            }
            return $cvn;
        }


        /**
        * Return operation name for request type
        *
        * @param  string $requestType
        * @return bool|string
        */
        protected function _getOperation($requestType)
        {
            switch ($requestType) {
                case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_ONLY:
                    return $this->__('authorize');
                case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_CAPTURE:
                    return $this->__('authorize and capture');
                case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                    return $this->__('capture');
                case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_CREDIT:
                    return $this->__('refund');
                case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_VOID:
                    return $this->__('void');
                default:
                    return false;
            }
        }

        /**
        * Format price with currency sign
        * @param  Mage_Payment_Model_Info $payment
        * @param float $amount
        * @return string
        */
        protected function _formatPrice($payment, $amount)
        {
            return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
        }

        public function getCheckversion($version,$operator = null){
            $currentVersion = Mage::getVersion();

            $result = version_compare($currentVersion,$version,$operator);

            return $result;
        }
}