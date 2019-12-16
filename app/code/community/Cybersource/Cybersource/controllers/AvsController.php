<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_AvsController extends Mage_Core_Controller_Front_Action
{
    const STATUS_ACCEPT = 100;

    /**
     * Sales Qoute Shipping Address instance
     *
     * @var Mage_Sales_Model_Quote_Address
     */
    protected $_address = null;
    
    /**
     * Store Id
     * @var mixed
     */
    private $_storeId = null;
    
    protected $_quote;
    protected $_checkout;
    
    public function indexAction()
    {
        $shippingAddress     =  $this->getAddress();
        $this->_storeId      = isset($this->_storeId)?$this->_storeId: Mage::app()->getStore()->getStoreId();
        $shippingAddressData = $shippingAddress->getData();
        $region_id           = (isset($shippingAddressData['region_id']))?$shippingAddressData['region_id']:null;
        $region              = (isset($shippingAddressData['region']))?$shippingAddressData['region']:null;
        $state               = '';
        $regionCode          = '';
        if(isset($region_id)){
            $region = Mage::getModel('directory/region')->load($region_id);
            if($region->getRegionId()){
              $state = $region->getCode();
              $regionCode = $region->getCode();
            }
        }else{
            $state   = $region;
        }
        $streetLineOne = $shippingAddress->getStreet(1);
        $streetLineTwo = $shippingAddress->getStreet(2);
        $shippingAddress = array(
            'city'        => $shippingAddressData['city'],
            'country'     => $shippingAddressData['country_id'],
            'firstname'   => $shippingAddressData['firstname'],
            'lastname'    => $shippingAddressData['lastname'],
            'postcode'    => $shippingAddressData['postcode'],
            'region_code' => (!empty($regionCode))? $regionCode : '',
            'street1'     => (!empty($streetLineOne))? $streetLineOne:null,
            'street2'     => (!empty($streetLineTwo))? $streetLineTwo:null,
            'telephone'   => $shippingAddressData['telephone']
        );
        $addressValidator = Mage::getModel('cybersource_core/avs_validator');
        $addressValidator->setData(array(
              'street1'    => $shippingAddress['street1'],
              'street2'    => $shippingAddress['street2'],
              'city'       => $shippingAddress['city'],
              'state'      => $state,
              'postcode'   => $shippingAddress['postcode'],
              'country_id' => $shippingAddress['country']
              )
          );

        $needUpdate = false;
        $fieldsUpdate = array();
        $displayAddress = array();
        $data = array(
            'isValid' => true
        );

        if (Mage::helper('cybersource_core')->getAvsActive($this->_storeId)) {
            $data = array(
                'needCheck' => true,
                'needForce' => Mage::helper('cybersource_core')->getAvsForceNormalization($this->_storeId),
                'updateFields' => array(),
                'needUpdate' => false,
                'isValid' => false,
                'message' => '',
                'normalizationData' => array()
            );

            $response = $addressValidator->execute();
            $status = $response->reasonCode;
            $davReply = $response->davReply;
            if (empty($response) || $status != self::STATUS_ACCEPT) {
                if (!empty($response) && $status == 102 && !empty($response->invalidField)) {
                    $data['message'] = __('Invalid field: ') . $response->invalidField;
                } else {
                    $data['updateFields'] = implode(',', $fieldsUpdate);
                    $data['message'] = 'Avs error code: ' . $status;
                }
            } else {
                $data['isValid'] = true;
                $davReply = json_decode(json_encode($davReply), 1);
                $fieldsToCheck = array(
                    'standardizedAddress1' => 'street1',
                    'standardizedAddress2' => 'street2',
                    'standardizedCity' => 'city',
                    'standardizedPostalCode' => 'postcode',
                );
                foreach ($fieldsToCheck as $cybersourceKey => $magentoKey) {
                    if ((!empty($davReply[$cybersourceKey])) &&
                        $davReply[$cybersourceKey] != $shippingAddress[$magentoKey]
                    ) {
                        $needUpdate = true;
                        $fieldsUpdate[$magentoKey] = $davReply[$cybersourceKey];
                        if (preg_match('/^street(\d+)/', $magentoKey, $matches)) {
                            $data['normalizationData']['street[' . ($matches[1] - 1) . ']'] = $davReply[$cybersourceKey];
                        } else {
                            $data['normalizationData'][$magentoKey] = $davReply[$cybersourceKey];
                        }
                    }
                }
                if ($needUpdate) {
                    foreach ($davReply as $key => $value) {
                        if (preg_match('/^standardized(.+)/', $key, $match) &&
                            !in_array($match[1], array('CSP', 'ISOCountry', 'AddressNoApt'))
                        ) {
                            $displayAddress[] = $match[1] . ': ' . $value;
                        }
                    }
                    $data['updateFields'] = implode(',', $fieldsUpdate);
                    $newLine = "\r\n";
                    $data['message'] = __(
                            'Our address verification system has suggested your address should read as follows.' . $newLine .
                            'Please review and confirm the suggested address is correct.'
                        ) . $newLine . $newLine . implode($newLine, $displayAddress);
                }
            }
        }

        $data['needUpdate']   = $needUpdate;
        $data['updateFields'] = $fieldsUpdate;               
        $responseJson         = json_encode( $data);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($responseJson);
    }
    
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
    
    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }
    
    /**
     * Retrieve sales quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }
    
    public function getAddress()
    {        
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }

        return $this->_address;
    }
}
