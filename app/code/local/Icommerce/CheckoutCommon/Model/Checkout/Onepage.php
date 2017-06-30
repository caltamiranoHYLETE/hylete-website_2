<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Icommerce_CheckoutCommon_Model_Checkout_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Save billing address information to quote
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     * Icommerce addon makes this method behave differently depending on current mage version
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
	public function saveBilling($data, $customerAddressId)
	{
		if( Icommerce_Default::getMagentoVersion()<1400 ){
            $res = $this->_saveBilling13($data, $customerAddressId);
		} else {
            $res = $this->_saveBilling14($data, $customerAddressId);
		}

        // Save the social security number on the customer ?
        if( isset($data["personid"]) && isset($data["email"]) &&
            Mage::getStoreConfig("paymentshared/payments/save_pno_with_billing") ){

            // Need a support table, since customer is created last in checkout process
            if( !Icommerce_Db::tableExists("ic_checkout_email_save") ){
                Icommerce_Db::write("CREATE TABLE `ic_checkout_email_save` (
                                     `email` VARCHAR( 64 ) NOT NULL ,`personid` VARCHAR( 16 ) NOT NULL ,
                                      UNIQUE (`email` ) ) ENGINE = MYISAM ;" );
            }
            $email=$data["email"]; $personid=$data["personid"];
            Icommerce_Db::write( "REPLACE INTO ic_checkout_email_save (email,personid) VALUES (?,?)", array($email, $personid) );
        }

		return $res;
	}

    /**
     * We don't want saveBilling to check if customer exists if QuickCheckout is
     * enabled.
     */
    protected function _saveBilling13($data, $customerAddressId)
    {
        if(empty($data)){
            $res = array(
                'error'     => -1,
                'message'   => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }

    	// Icommerce addon to support Desitex_CheckoutNewsletter
    	if(Icommerce_Default_Helper_Data::isModuleActive('Desitex_Checkoutnewsletter')){
        	if (isset($data['is_subscribed']) && !empty($data['is_subscribed'])){
            	$this->getCheckout()->setCustomerIsSubscribed(1);
        	}
        	else {
           	 $this->getCheckout()->setCustomerIsSubscribed(0);
        	}
        }

        // Icommerce addon to support Icommerce_Creditcheck
        if (array_key_exists('personid',$data)) {
			if ($data['personid']!="") {
				$this->getCheckout()->setPersonId($data['personid']);
			}
		}
        // Icommerce addon to support Icommerce_Creditcheck
        if (array_key_exists('telephone',$data)) {
			if ($data['telephone']!="") {
				$this->getCheckout()->setPhoneNumber($data['telephone']);
			}
		}

        /**
        * Icommerce addon, we don't want to check if customer exists if
        * QuickCheckout is enabled.
        */
    	if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout')){
            $address = $this->getQuote()->getBillingAddress();
            if (!empty($customerAddressId)) {
                $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
                if ($customerAddress->getId()) {
                    if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                        return array('error' => 1,
                            'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                        );
                    }
                    $address->importCustomerAddress($customerAddress);
                }
            } else {
                unset($data['address_id']);
                $address->addData($data);
                //$address->setId(null);
            }

            if (($validateRes = $address->validate())!==true) {
                $res = array(
                    'error'     => 1,
                    'message'   => $validateRes
                );
                return $res;
            }

            /**
            * Icommerce addon, we don't want to check if customer exists if
            * QuickCheckout is enabled.
            */
            if(!Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout')){
                if (!$this->getQuote()->getCustomerId() && Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
                    if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                        return array('error' => 1,
                            'message' => Mage::helper('checkout')->__('There is already a customer registered using this email address')
                        );
                    }
                }
            }

            $address->implodeStreetAddress();

            if (!$this->getQuote()->isVirtual()) {
                /**
                 * Billing address using otions
                 */
                $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;

                switch($usingCase) {
                    case 0:
                        $shipping = $this->getQuote()->getShippingAddress();
                        $shipping->setSameAsBilling(0);
                        break;
                    case 1:
                        $billing = clone $address;
                        $billing->unsAddressId()->unsAddressType();
                        $shipping = $this->getQuote()->getShippingAddress();
                        $shippingMethod = $shipping->getShippingMethod();
                        $shipping->addData($billing->getData())
                            ->setSameAsBilling(1)
                            ->setShippingMethod($shippingMethod)
                            ->setCollectShippingRates(true);
                        $this->getCheckout()->setStepData('shipping', 'complete', true);
                        break;
                }
            }

            if (true !== $result = $this->_processValidateCustomer($address)) {
                return $result;
            }

            $this->getQuote()->collectTotals();
            $this->getQuote()->save();

            $this->getCheckout()
                ->setStepData('billing', 'allow', true)
                ->setStepData('billing', 'complete', true)
                ->setStepData('shipping', 'allow', true);

            return array();
        }
        else {
            return parent::saveBilling($data, $customerAddressId);
        }
    }

    protected function _saveBilling14($data, $customerAddressId)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid data.'));
        }

    	// Icommerce addon to support Desitex_CheckoutNewsletter
    	if(Icommerce_Default_Helper_Data::isModuleActive('Desitex_Checkoutnewsletter')){
        	if (isset($data['is_subscribed']) && !empty($data['is_subscribed'])){
            	$this->getCheckout()->setCustomerIsSubscribed(1);
        	}
        	else {
           	 $this->getCheckout()->setCustomerIsSubscribed(0);
        	}
        }

        // Icommerce addon to support Icommerce_Creditcheck
        if (array_key_exists('personid',$data)) {
			if ($data['personid']!="") {
				$this->getCheckout()->setPersonId($data['personid']);
			}
		}
        // Icommerce addon to support Icommerce_Creditcheck
        if (array_key_exists('telephone',$data)) {
			if ($data['telephone']!="") {
				$this->getCheckout()->setPhoneNumber($data['telephone']);
			}
		}

        /**
        * Icommerce addon, we don't want to check if customer exists if
        * QuickCheckout is enabled.
        */
        if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout')){
            $address = $this->getQuote()->getBillingAddress();
            if (!empty($customerAddressId)) {
                $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
                if ($customerAddress->getId()) {
                    if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                        return array('error' => 1,
                            'message' => $this->_helper->__('Customer Address is not valid.')
                        );
                    }
                    $address->importCustomerAddress($customerAddress);
                }
            } else {
                unset($data['address_id']);
                $address->addData($data);
                //$address->setId(null);
            }

// Introduced to support JC/Brothers and now POP code that does some magic during saveBilling...
            $status = new Varien_Object();
            Mage::dispatchEvent('checkoutcommon_save_billing_14_additional', array('quote'=>$this->getQuote(), 'data_array' => $data, 'status' => $status, 'address' => $address));
            if ($status->getError()) {
                return array('error' => $status->getError(),'message' => $status->getMessage());
            }

            if (($validateRes = $address->validate())!==true) {
                return array('error' => 1, 'message' => $validateRes);
            }

            /**
            * Icommerce addon, we don't want to check if customer exists if
            * QuickCheckout is enabled.
            */
            if(!Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout')){
                if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
                    if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                        return array('error' => 1, 'message' => $this->_helper->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.') );
                    }
                }
            }

            $address->implodeStreetAddress();

            if (!$this->getQuote()->isVirtual()) {
                /**
                 * Billing address using otions
                 */
                $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;

                switch($usingCase) {
                    case 0:
                        $shipping = $this->getQuote()->getShippingAddress();
                        $shipping->setSameAsBilling(0);
                        break;
                    case 1:
                        $billing = clone $address;
                        $billing->unsAddressId()->unsAddressType();
                        $shipping = $this->getQuote()->getShippingAddress();
                        $shippingMethod = $shipping->getShippingMethod();
                        $shipping->addData($billing->getData())
                            ->setSameAsBilling(1)
                            ->setShippingMethod($shippingMethod)
                            ->setCollectShippingRates(true);
                        $this->getCheckout()->setStepData('shipping', 'complete', true);
                        break;
                }
            }

            if (array_key_exists('personid',$data) && !$address->hasTaxvat()) {
                if ($data['personid']!="") {
                    $address->setTaxvat($data['personid']);
                }
            }

            if (true !== $result = $this->_processValidateCustomer($address)) {
                return $result;
            }

            $this->getQuote()->collectTotals();
            $this->getQuote()->save();

            $this->getCheckout()
                ->setStepData('billing', 'allow', true)
                ->setStepData('billing', 'complete', true)
                ->setStepData('shipping', 'allow', true);

            return array();
        }
        else {
            return parent::saveBilling($data, $customerAddressId);
        }
    }

	/**
	 * Icommerce addon, this function is overriden to support
	 * AdjustWare_Deliverydate
	 *
	 * @param $shippingMethod
     */
    public function saveShippingMethod($shippingMethod)
    {
    	if(Icommerce_Default_Helper_Data::isModuleActive('AdjustWare_Deliverydate')){
        	$errors = Mage::getModel('adjdeliverydate/step')->process('shippingMethod');
        	if ($errors) {
            	return $errors;
        	}
    	}

        return parent::saveShippingMethod($shippingMethod);
    }

	/**
	 * Icommerce addon: This function was first made to temporary save the country-id
	 * that the user had chosen in QuickCheckout before reloading shipping methods.
	 *
	 * It now also has added support for Creditcheck.
	 * This function is called from QuickCheckout/controllers/QuickCheckoutController.php
	 *
	 * @param $data
	 * @param bool $shipping
	 * @return array
	 */
	public function saveCheckoutData($data, $shipping = true)
	{
        if(is_array($data))
		    $data = new Varien_Object($data);

		$shipAddress = $this->getQuote()->getShippingAddress();
		if($shipping or $data->getUseForShipping()){
			$shipAddress->setCountryId($data->getCountryId());
			$shipAddress->setFirstname($data->getFirstname());
			$shipAddress->setLastname($data->getLastname());
            $shipAddress->setTelephone($data->getTelephone());
			$shipAddress->setPostcode($data->getPostcode());
			$shipAddress->setCompany($data->getCompany());
			$shipAddress->setCity($data->getCity());
			$shipAddress->setStreet($data->getStreet());
			$shipAddress->setEmail($data->getEmail()); //magnus
			$shipAddress->setPrefix($data->getPrefix());

			if (Icommerce_Default_Helper_Data::isModuleActive('Icommerce_Creditcheck')) {
				$shipAddress->setCcidnr($data->getCcidnr());
				$shipAddress->setIdtype($data->getIdtype());
				$shipAddress->setIdnr($data->getIdnr());
			}

            $shipAddress->setRegion($data->getRegion());
            $shipAddress->setRegionId($data->getRegionId());

            //Used for getting custom address attributes
            if (isset($data['forced_fields'])){
                $forced_fields = explode(',', $data['forced_fields']);
                foreach ($forced_fields as $f_field){
                    $f_field = trim($f_field);
                    if (isset($data[$f_field])){
                        $shipAddress->setData($f_field, $data[$f_field]);
                    }else{
                        $shipAddress->setData($f_field, null);
                    }
                }
            }
		}

		$billAddress = $this->getQuote()->getBillingAddress();
		if(!$shipping){
			$billAddress->setCountryId($data->getCountryId());
			$billAddress->setFirstname($data->getFirstname());
			$billAddress->setLastname($data->getLastname());
            $billAddress->setTelephone($data->getTelephone());
			$billAddress->setPostcode($data->getPostcode());
			$billAddress->setCompany($data->getCompany());
			$billAddress->setCity($data->getCity());
			$billAddress->setStreet($data->getStreet());
			$billAddress->setEmail($data->getEmail()); //magnus
			$billAddress->setPrefix($data->getPrefix());

			$billAddress->setUseForShipping($data->getUseForShipping());

			if (Icommerce_Default_Helper_Data::isModuleActive('Icommerce_Creditcheck')) {
				$billAddress->setCcidnr($data->getCcidnr());
				$billAddress->setIdtype($data->getIdtype());
				$billAddress->setIdnr($data->getIdnr());
			}

            $billAddress->setRegion($data->getRegion());
            $billAddress->setRegionId($data->getRegionId());

            //Used for getting custom address attributes
            if (isset($data['forced_fields'])){
                $forced_fields = explode(',', $data['forced_fields']);
                foreach ($forced_fields as $f_field){
                    $f_field = trim($f_field);
                    if (isset($data[$f_field])){
                        $billAddress->setData($f_field, $data[$f_field]);
                    }else{
                        $billAddress->setData($f_field, null);
                    }
                }
            }
		}

		return array();
	}

    /**
     * @deprecated in newer version of QuickCheckout
     * @param $data
     * @return array
     */
	public function saveCountryId($data){
		return $this->saveCheckoutData($data);
    }

	/**
	 * Kjell
	 *
     * billing has ccidnr to identify the table row of the credid check request
     * three fields from that request is transferred to the customer record
     *
     * @return array
     */
    protected function CustomerIDExists($idnr, $idtype, $custid, $websiteId = null)
    {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByPersonalID($idnr,$idtype,$custid);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    /**
	 * Kjell
	 *
     * billing has ccidnr to identify the table row of the credid check request
     * three fields from that request is transferred to the customer record
     *
     * @return array
     */
	protected function AddCustomizedFields(&$customer,$billing,$forcedf)
	{
        if ($billing->getCcidnr()>0) {
	    	$creditcheck = Mage::getModel('creditcheck/creditcheck');
        	$creditcheck->getCreditcheck($billing->getCcidnr());
        	$customer->setCustomer_idnumber($creditcheck->getIdnr());
        	$customer->setCustomer_idtype($creditcheck->getIdtype());

	        if ($creditcheck->getCanuseinv()==1) {
    	    	$customer->setCustomer_creditlimit($creditcheck->getConfigData('creditlimit'));
    	    } else {
	        	$customer->setCustomer_creditlimit(1);
    	    };
        } else {
			if ($forcedf) {
	        	$customer->setCustomer_creditlimit(1);
			}
        };
	}

    /**
     * Prepare quote for customer order submit
     * This function is overridden since we want to load customer if customer email
     * exists, this places order on that existing customer account but does not login
     * user since we don't want that. This function doesn't exist in < 1.4 so we don't need to have
     * multiple versions.
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _prepareCustomerQuote()
    {
        $helper = Mage::helper('checkoutcommon');

        $quote      = $this->getQuote();
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

		$customer = $this->getCustomerSession()->getCustomer();

		/**
		 * Icommerce QuickCheckout addon
		 */
		if(Icommerce_Default::isModuleActive('Icommerce_QuickCheckout')){
		    $_qcQuote = $this->getQuote();
            if(!is_null($_qcQuote) && !is_null($_qcQuote->getCustomerId())){
                $customer = Mage::getModel('customer/customer')->load($_qcQuote->getCustomerId());
                if (!$customer) {
                    $customer = $this->getCustomerSession()->getCustomer();
                    $_qcQuote->setCustomerId(null);
                }
            }
		    if(!is_null($_qcQuote) && is_null($_qcQuote->getCustomerId())){
		        $_qcCustEmail = $_qcQuote->getBillingAddress()->getEmail();
		        if ($this->_customerEmailExists($_qcCustEmail, Mage::app()->getWebsite()->getId())) {
		            $customer = Mage::getModel('customer/customer');
				    $customer->setStore(Mage::app()->getStore()); // We need to set store before we can load customer by email
				    $customer->loadByEmail($_qcCustEmail);
		        }
		    }
		}

        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $customerBilling = $billing->exportCustomerAddress();

            if ($helper->isSkipAddDuplicateAddress()) {
                if (!$helper->isAddressDuplicate($customerBilling, $customer->getAddresses())) {
                    $customer->addAddress($customerBilling);
                }
            } else {
                $customer->addAddress($customerBilling);
            }

            $billing->setCustomerAddress($customerBilling);
        }
        if ($shipping && ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling())
            || (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {
            $customerShipping = $shipping->exportCustomerAddress();

            if ($helper->isSkipAddDuplicateAddress()) {
                if (!$helper->isAddressDuplicate($customerShipping, $customer->getAddresses())) {
                    $customer->addAddress($customerShipping);
                }
            } else {
                $customer->addAddress($customerShipping);
            }

            $shipping->setCustomerAddress($customerShipping);
        }

        if (isset($customerBilling) && !$customer->getDefaultBilling()) {
            $customerBilling->setIsDefaultBilling(true);
        }
        if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
            $customerShipping->setIsDefaultShipping(true);
        } elseif (isset($customerBilling) && !$customer->getDefaultShipping()) {
            $customerBilling->setIsDefaultShipping(true);
        }
        $quote->setCustomer($customer);
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _prepareNewCustomerQuote()
    {
        $quote      = $this->getQuote();
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        //$customer = Mage::getModel('customer/customer');
        $customer = $quote->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } elseif ($shipping) {
            $customerBilling->setIsDefaultShipping(true);
        }
        /**
         * @todo integration with dynamica attributes customer_dob, customer_taxvat, customer_gender
         */
        if ($quote->getCustomerDob() && !$billing->getCustomerDob()) {
            $billing->setCustomerDob($quote->getCustomerDob());
        }

        if ($quote->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
            $billing->setCustomerTaxvat($quote->getCustomerTaxvat());
        }

        if ($quote->getCustomerGender() && !$billing->getCustomerGender()) {
            $billing->setCustomerGender($quote->getCustomerGender());
        }

        Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
        $quote->setCustomer($customer)
            ->setCustomerId(true);
    }


    protected function _saveOrderQuickAndCredit13()
    {
        $this->validateOrder();
        $billing = $this->getQuote()->getBillingAddress();
        if (!$this->getQuote()->isVirtual()) {
            $shipping = $this->getQuote()->getShippingAddress();
        }

        /**
         *    QuickCheckout
        */
        $returningNotLoggedIn = false;

        $quickCustId = $this->getQuote()->getCustomerId();

        if($quickCustId == '0'){
            // If no custid exists we make sure that register method is used
            $this->getQuote()->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
        }

        switch ($this->getQuote()->getCheckoutMethod(true)) {
        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
            if (!$this->getQuote()->isAllowedGuestCheckout()) {
                Mage::throwException(Mage::helper('checkout')->__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
            }
            $this->getQuote()->setCustomerId(null)
                ->setCustomerEmail($billing->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            break;

        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
            /**
             *  QuickCheckout
             *  We check if the customer exists, if so we handle the
             *  saveOrder as if the customer was logged in.
             */
            if ($this->_customerEmailExists($billing->getEmail(), Mage::app()->getWebsite()->getId())) {
                $returningNotLoggedIn = true; // It's a returning customer (that hasn't logged in)
                $customer = Mage::getModel('customer/customer');
                $customer->setStore(Mage::app()->getStore()); // We need to set store before we can load customer by email
                $customer->loadByEmail($billing->getEmail());

                if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                    $customerBilling = $billing->exportCustomerAddress();

                    /**
                     * QuickCheckout
                     * If customer adress already exist we don't save it again
                     */
                    $addresses = $customer->getAddresses();

                    $addressAlreadyExists = false;

                    foreach ($addresses as $address) {
                        if($address->getFirstname() == $billing->getFirstname()
                           && $address->getLastname() == $billing->getLastname()
                           && $address->getStreet1() == $billing->getStreet1()
                           && $address->getCity() == $billing->getCity()
                           && $address->getPostCode() == $billing->getPostCode()
                           && $address->getCountryId() == $billing->getCountryId())
                        {
                            $addressAlreadyExists = true;
                            break;
                        }
                    }

                    if(!$addressAlreadyExists){
                        $customer->addAddress($customerBilling);
                    }
                }
                if (!$this->getQuote()->isVirtual() &&
                    ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) ||
                    (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {
                    $customerShipping = $shipping->exportCustomerAddress();
                    /**
                     * QuickCheckout
                     * If customer shipping-adress already exist we don't save it again
                     */
                    $addresses = $customer->getAddresses();

                    $addressAlreadyExists = false;

                    foreach ($addresses as $address) {
                        if($address->getFirstname() == $shipping->getFirstname()
                           && $address->getLastname() == $shipping->getLastname()
                           && $address->getStreet1() == $shipping->getStreet1()
                           && $address->getCity() == $shipping->getCity()
                           && $address->getPostCode() == $shipping->getPostCode()
                           && $address->getCountryId() == $shipping->getCountryId())
                        {
                            $addressAlreadyExists = true;
                            break;
                        }
                    }

                    if(!$addressAlreadyExists){
                        $customer->addAddress($customerShipping);
                    }
                }
                $customer->setSavedFromQuote(true);
                $customer->save();

                $changed = false;
                if (isset($customerBilling) && !$customer->getDefaultBilling()) {
                    $customer->setDefaultBilling($customerBilling->getId());
                    $changed = true;
                }
                if (!$this->getQuote()->isVirtual() && isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
                    $customer->setDefaultShipping($customerBilling->getId());
                    $changed = true;
                }
                elseif (!$this->getQuote()->isVirtual() && isset($customerShipping) && !$customer->getDefaultShipping()){
                    $customer->setDefaultShipping($customerShipping->getId());
                    $changed = true;
                }

                // CreditCheck
                $this->AddCustomizedFields($customer,$billing,false);
                if ($this->CustomerIDExists($customer->getCustomer_idnumber(),$customer->getCustomer_idtype(),$customer->getEntity_id(), Mage::app()->getWebsite()->getId())) {
                    if ($customer->getCustomer_idtype()=="person") {
                        Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
                    } else {
                        Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
                    }
                }

                if ($changed) {
                    $customer->save();
                }
                $this->getQuote()->setCustomer($customer);
            } else {
            $customer = Mage::getModel('customer/customer');
            /* @var $customer Mage_Customer_Model_Customer */

            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);

            if (!$this->getQuote()->isVirtual() && !$shipping->getSameAsBilling()) {
                $customerShipping = $shipping->exportCustomerAddress();
                $customer->addAddress($customerShipping);
            }

            if ($this->getQuote()->getCustomerDob() && !$billing->getCustomerDob()) {
                $billing->setCustomerDob($this->getQuote()->getCustomerDob());
            }

            if ($this->getQuote()->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
                $billing->setCustomerTaxvat($this->getQuote()->getCustomerTaxvat());
            }

            Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);

            $customer->setPassword($customer->decryptPassword($this->getQuote()->getPasswordHash()));
            $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));

            // Kjell
            $this->AddCustomizedFields($customer,$billing,true);

            $this->getQuote()->setCustomer($customer);
            }
            break;

        default:
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                $customerBilling = $billing->exportCustomerAddress();
                $customer->addAddress($customerBilling);
            }
            if (!$this->getQuote()->isVirtual() &&
                ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) ||
                (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {

                $customerShipping = $shipping->exportCustomerAddress();
                $customer->addAddress($customerShipping);
            }
            $customer->setSavedFromQuote(true);
            $customer->save();

            $changed = false;
            if (isset($customerBilling) && !$customer->getDefaultBilling()) {
                $customer->setDefaultBilling($customerBilling->getId());
                $changed = true;
            }
            if (!$this->getQuote()->isVirtual() && isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
                $customer->setDefaultShipping($customerBilling->getId());
                $changed = true;
            }
            elseif (!$this->getQuote()->isVirtual() && isset($customerShipping) && !$customer->getDefaultShipping()){
                $customer->setDefaultShipping($customerShipping->getId());
                $changed = true;
            }
            // Kjell
            $this->AddCustomizedFields($customer,$billing,false);
            if ($this->CustomerIDExists($customer->getCustomer_idnumber(),$customer->getCustomer_idtype(),$customer->getEntity_id(), Mage::app()->getWebsite()->getId())) {
                if ($customer->getCustomer_idtype()=="person") {
                    Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
                } else {
                    Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
                }
            }

            if ($changed) {
                $customer->save();
            }
        }

        $this->getQuote()->reserveOrderId();
        $convertQuote = Mage::getModel('sales/convert_quote');
        /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
        //$order = Mage::getModel('sales/order');
        if ($this->getQuote()->isVirtual()) {
            $order = $convertQuote->addressToOrder($billing);
        }
        else {
            $order = $convertQuote->addressToOrder($shipping);
        }
        /* @var $order Mage_Sales_Model_Order */
        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));

        if (!$this->getQuote()->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));

        foreach ($this->getQuote()->getAllItems() as $item) {
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));
        // check again, if customer exists
        /**
         *  QuickCheckout
         *  Added the && !$returningNotLoggedIn to the if-clause
         */
        if ($this->getQuote()->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER && !$returningNotLoggedIn) {
            if ($this->_customerEmailExists($customer->getEmail(), Mage::app()->getWebsite()->getId())) {
                Mage::throwException(Mage::helper('checkout')->__('There is already a customer registered using this email address'));
            }
            // Kjell
            if ($this->CustomerIDExists($customer->getCustomer_idnumber(),$customer->getCustomer_idtype(),-1, Mage::app()->getWebsite()->getId())) {
                if ($customer->getCustomer_idtype()=="person") {
                    Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
                } else {
                    Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
                }
            }
        }

        // QuickCheckout
        $creditcheck = Mage::getModel('creditcheck/creditcheck');
        if ($this->getQuote()->getPayment()->getMethodInstance()->getCode() == $creditcheck->getConfigData('paymentmode')) { // checkmo (invoice payment)
            $creditcheck->getCreditcheck($billing->getCcidnr());
            $customer->setCustomer_idnumber($creditcheck->getIdnr());
            $customer->setCustomer_idtype($creditcheck->getIdtype());
            if($creditcheck->getCanuseinv() != 1){
                // We don't allow the customer to use invoice payment but we just ask him/her to choose another payment method
                Mage::throwException(Mage::helper('checkout')->__('Invoice payment is currently unavailable, please use another payment method'));
            }
        }

        // Kjell
        if ($this->getQuote()->getPayment()->getMethodInstance()->getCode() == $creditcheck->getConfigData('paymentmode')) { // checkmo (invoice payment)
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::OKButChangedAddress($this->getQuote()->getBillingAddress(), $this->getQuote()->getShippingAddress())){
                Mage::throwException(Mage::helper('checkout')->__('You will not be able to use Invoice payment method because you changed the Identified name and address.'));
            }
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::CustomerOverCreditLimit($this->getQuote())) {
                Mage::throwException(Mage::helper('checkout')->__('Total amount higher than your credit limit, please use another payment method'));
            }
        }
        $order->place();

        /**
         *  QuickCheckout
         *  Added the && !$returningNotLoggedIn to the if-clause
         */
        if ($this->getQuote()->getCheckoutMethod()==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER && !$returningNotLoggedIn) {
            $customer->save();
            $customerBillingId = $customerBilling->getId();
            if (!$this->getQuote()->isVirtual()) {
                $customerShippingId = isset($customerShipping) ? $customerShipping->getId() : $customerBillingId;
                $customer->setDefaultShipping($customerShippingId);
            }
            $customer->setDefaultBilling($customerBillingId);
            $customer->save();

            $this->getQuote()->setCustomerId($customer->getId());

            $order->setCustomerId($customer->getId());
            Mage::helper('core')->copyFieldset('customer_account', 'to_order', $customer, $order);

            $billing->setCustomerId($customer->getId())->setCustomerAddressId($customerBillingId);
            if (!$this->getQuote()->isVirtual()) {
                $shipping->setCustomerId($customer->getId())->setCustomerAddressId($customerShippingId);
            }

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail('confirmation');
            }
            else {
                $customer->sendNewAccountEmail();
            }
        }

        /**
         *  QuickCheckout
         *  We need to connect the order with the customerId
         */
        $this->getQuote()->setCustomerId($customer->getId());
        $order->setCustomerId($customer->getId());

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        if(!$redirectUrl){
            $order->setEmailSent(true);
        }

        $order->save();

        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         * need to have somelogic to set order as new status to make sure order is not finished yet
         * quote will be still active when we send the customer to paypal
         */

        $orderId = $order->getIncrementId();
        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
        $this->getCheckout()->setLastOrderId($order->getId());
        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());
        $this->getCheckout()->setRedirectUrl($redirectUrl);

        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl){
            $order->sendNewOrderEmail();
        }

        /**
         *  QuickCheckout
         *  Added the && !$returningNotLoggedIn to the if-clause
         */
        if ($this->getQuote()->getCheckoutMethod(true)==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER
            && !Mage::getSingleton('customer/session')->isLoggedIn() && !$returningNotLoggedIn) {
            /**
             * we need to save quote here to have it saved with Customer Id.
             * so when loginById() executes checkout/session method loadCustomerQuote
             * it would not create new quotes and merge it with old one.
             */
            $this->getQuote()->save();
            if ($customer->isConfirmationRequired()) {
                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.',
                    Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
                ));
            }
            else {
                Mage::getSingleton('customer/session')->loginById($customer->getId());
            }
        }

        //Setting this one more time like control flag that we haves saved order
        //Must be checkout on success page to show it or not.
        $this->getCheckout()->setLastSuccessQuoteId($this->getQuote()->getId());

        $this->getQuote()->setIsActive(false);
        $this->getQuote()->save();

        return $this;
    }

    protected function _saveOrderCredit13()
    {
        $this->validateOrder();
        $billing = $this->getQuote()->getBillingAddress();
        if (!$this->getQuote()->isVirtual()) {
            $shipping = $this->getQuote()->getShippingAddress();
        }
        switch ($this->getQuote()->getCheckoutMethod()) {
        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
            if (!$this->getQuote()->isAllowedGuestCheckout()) {
                Mage::throwException(Mage::helper('checkout')->__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
            }
            $this->getQuote()->setCustomerId(null)
                ->setCustomerEmail($billing->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            break;

        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
            $customer = Mage::getModel('customer/customer');
            /* @var $customer Mage_Customer_Model_Customer */

            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);

            if (!$this->getQuote()->isVirtual() && !$shipping->getSameAsBilling()) {
                $customerShipping = $shipping->exportCustomerAddress();
                $customer->addAddress($customerShipping);
            }

            if ($this->getQuote()->getCustomerDob() && !$billing->getCustomerDob()) {
                $billing->setCustomerDob($this->getQuote()->getCustomerDob());
            }

            if ($this->getQuote()->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
                $billing->setCustomerTaxvat($this->getQuote()->getCustomerTaxvat());
            }

            Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);

            $customer->setPassword($customer->decryptPassword($this->getQuote()->getPasswordHash()));
            $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));

            // Kjell
            $this->AddCustomizedFields($customer,$billing,true);

            $this->getQuote()->setCustomer($customer);
            break;

        default:
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                $customerBilling = $billing->exportCustomerAddress();
                $customer->addAddress($customerBilling);
            }
            if (!$this->getQuote()->isVirtual() &&
                ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) ||
                (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {

                $customerShipping = $shipping->exportCustomerAddress();
                $customer->addAddress($customerShipping);
            }
            $customer->setSavedFromQuote(true);
            $customer->save();

            $changed = false;
            if (isset($customerBilling) && !$customer->getDefaultBilling()) {
                $customer->setDefaultBilling($customerBilling->getId());
                $changed = true;
            }
            if (!$this->getQuote()->isVirtual() && isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
                $customer->setDefaultShipping($customerBilling->getId());
                $changed = true;
            }
            elseif (!$this->getQuote()->isVirtual() && isset($customerShipping) && !$customer->getDefaultShipping()){
                $customer->setDefaultShipping($customerShipping->getId());
                $changed = true;
            }
            // Kjell
            $this->AddCustomizedFields($customer,$billing,false);
            if ($this->CustomerIDExists($customer->getCustomer_idnumber(),$customer->getCustomer_idtype(),$customer->getEntity_id(), Mage::app()->getWebsite()->getId())) {
                if ($customer->getCustomer_idtype()=="person") {
                    Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
                } else {
                    Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
                }
            }

            if ($changed) {
                $customer->save();
            }
        }

        $this->getQuote()->reserveOrderId();
        $convertQuote = Mage::getModel('sales/convert_quote');
        /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
        //$order = Mage::getModel('sales/order');
        if ($this->getQuote()->isVirtual()) {
            $order = $convertQuote->addressToOrder($billing);
        }
        else {
            $order = $convertQuote->addressToOrder($shipping);
        }
        /* @var $order Mage_Sales_Model_Order */
        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));

        if (!$this->getQuote()->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));

        foreach ($this->getQuote()->getAllItems() as $item) {
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));
        // check again, if customer exists
        if ($this->getQuote()->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER) {
            if ($this->_customerEmailExists($customer->getEmail(), Mage::app()->getWebsite()->getId())) {
                Mage::throwException(Mage::helper('checkout')->__('There is already a customer registered using this email address'));
            }
            // Kjell
            if ($this->CustomerIDExists($customer->getCustomer_idnumber(),$customer->getCustomer_idtype(),-1, Mage::app()->getWebsite()->getId())) {
                if ($customer->getCustomer_idtype()=="person") {
                    Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
                } else {
                    Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
                }
            }
        }
        // Kjell (uppdaterad av Simon med kontroll om det verkligen •r faktura som anv•nds
        $creditcheck = Mage::getModel('creditcheck/creditcheck');
        if ($this->getQuote()->getPayment()->getMethodInstance()->getCode() == $creditcheck->getConfigData('paymentmode')) { // checkmo (invoice payment)
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::CustomerOverCreditLimit($this->getQuote())) {
                Mage::throwException(Mage::helper('checkout')->__('Total amount higher than your credit limit, please use another payment method'));
            }
        }
        $order->place();

        if ($this->getQuote()->getCheckoutMethod()==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER) {
            $customer->save();
            $customerBillingId = $customerBilling->getId();
            if (!$this->getQuote()->isVirtual()) {
                $customerShippingId = isset($customerShipping) ? $customerShipping->getId() : $customerBillingId;
                $customer->setDefaultShipping($customerShippingId);
            }
            $customer->setDefaultBilling($customerBillingId);
            $customer->save();

            $this->getQuote()->setCustomerId($customer->getId());

            $order->setCustomerId($customer->getId());
            Mage::helper('core')->copyFieldset('customer_account', 'to_order', $customer, $order);

            $billing->setCustomerId($customer->getId())->setCustomerAddressId($customerBillingId);
            if (!$this->getQuote()->isVirtual()) {
                $shipping->setCustomerId($customer->getId())->setCustomerAddressId($customerShippingId);
            }

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail('confirmation');
            }
            else {
                $customer->sendNewAccountEmail();
            }
        }

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        if(!$redirectUrl){
            $order->setEmailSent(true);
        }

        $order->save();

        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         * need to have somelogic to set order as new status to make sure order is not finished yet
         * quote will be still active when we send the customer to paypal
         */

        $orderId = $order->getIncrementId();
        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
        $this->getCheckout()->setLastOrderId($order->getId());
        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());
        $this->getCheckout()->setRedirectUrl($redirectUrl);

        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl){
            $order->sendNewOrderEmail();
        }

        if ($this->getQuote()->getCheckoutMethod(true)==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER
            && !Mage::getSingleton('customer/session')->isLoggedIn()) {
            /**
             * we need to save quote here to have it saved with Customer Id.
             * so when loginById() executes checkout/session method loadCustomerQuote
             * it would not create new quotes and merge it with old one.
             */
            $this->getQuote()->save();
            if ($customer->isConfirmationRequired()) {
                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.',
                    Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
                ));
            }
            else {
                Mage::getSingleton('customer/session')->loginById($customer->getId());
            }
        }

        //Setting this one more time like control flag that we haves saved order
        //Must be checkout on success page to show it or not.
        $this->getCheckout()->setLastSuccessQuoteId($this->getQuote()->getId());

        $this->getQuote()->setIsActive(false);
        $this->getQuote()->save();

        return $this;
    }

    protected function _saveOrderQuick13()
    {
        $this->validateOrder();
        $billing = $this->getQuote()->getBillingAddress();
        if (!$this->getQuote()->isVirtual()) {
            $shipping = $this->getQuote()->getShippingAddress();
        }

//        $this->LogIfGuestStart("_saveOrderQuick13");
//        $this->LogIfGuest("A",NULL);

        /**
         *    QuickCheckout
        */
        $returningNotLoggedIn = false;

        $quickCustId = $this->getQuote()->getCustomerId();

        if($quickCustId == '0'){
            // If no custid exists we make sure that register method is used
            $this->getQuote()->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
        }

//        $this->LogIfGuest("B",NULL);

        switch ($this->getQuote()->getCheckoutMethod(true)) {
        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
            if (!$this->getQuote()->isAllowedGuestCheckout()) {
                Mage::throwException(Mage::helper('checkout')->__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
            }
            $this->getQuote()->setCustomerId(null)
                ->setCustomerEmail($billing->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            break;

        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
            /**
             *  QuickCheckout
             *  We check if the customer exists, if so we handle the
             *  saveOrder as if the customer was logged in.
             */
            if ($this->_customerEmailExists($billing->getEmail(), Mage::app()->getWebsite()->getId())) {
                $returningNotLoggedIn = true; // It's a returning customer (that hasn't logged in)
                $customer = Mage::getModel('customer/customer');
                $customer->setStore(Mage::app()->getStore()); // We need to set store before we can load customer by email
                $customer->loadByEmail($billing->getEmail());

                if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                    $customerBilling = $billing->exportCustomerAddress();

                    /**
                     * QuickCheckout
                     * If customer adress already exist we don't save it again
                     */
                    $addresses = $customer->getAddresses();

                    $addressAlreadyExists = false;

                    foreach ($addresses as $address) {
                        if($address->getFirstname() == $billing->getFirstname()
                           && $address->getLastname() == $billing->getLastname()
                           && $address->getStreet1() == $billing->getStreet1()
                           && $address->getCity() == $billing->getCity()
                           && $address->getPostCode() == $billing->getPostCode()
                           && $address->getCountryId() == $billing->getCountryId())
                        {
                            $addressAlreadyExists = true;
                            break;
                        }
                    }

                    if(!$addressAlreadyExists){
                        $customer->addAddress($customerBilling);
                    }
                }
                if (!$this->getQuote()->isVirtual() &&
                    ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) ||
                    (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {
                    $customerShipping = $shipping->exportCustomerAddress();
                    /**
                     * QuickCheckout
                     * If customer shipping-adress already exist we don't save it again
                     */
                    $addresses = $customer->getAddresses();

                    $addressAlreadyExists = false;

                    foreach ($addresses as $address) {
                        if($address->getFirstname() == $shipping->getFirstname()
                           && $address->getLastname() == $shipping->getLastname()
                           && $address->getStreet1() == $shipping->getStreet1()
                           && $address->getCity() == $shipping->getCity()
                           && $address->getPostCode() == $shipping->getPostCode()
                           && $address->getCountryId() == $shipping->getCountryId())
                        {
                            $addressAlreadyExists = true;
                            break;
                        }
                    }

                    if(!$addressAlreadyExists){
                        $customer->addAddress($customerShipping);
                    }
                }
                $customer->setSavedFromQuote(true);
                $customer->save();

                $changed = false;
                if (isset($customerBilling) && !$customer->getDefaultBilling()) {
                    $customer->setDefaultBilling($customerBilling->getId());
                    $changed = true;
                }
                if (!$this->getQuote()->isVirtual() && isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
                    $customer->setDefaultShipping($customerBilling->getId());
                    $changed = true;
                }
                elseif (!$this->getQuote()->isVirtual() && isset($customerShipping) && !$customer->getDefaultShipping()){
                    $customer->setDefaultShipping($customerShipping->getId());
                    $changed = true;
                }

                if ($changed) {
                    $customer->save();
                }
                $this->getQuote()->setCustomer($customer);
            } else {
            $customer = Mage::getModel('customer/customer');
            /* @var $customer Mage_Customer_Model_Customer */

            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);

            if (!$this->getQuote()->isVirtual() && !$shipping->getSameAsBilling()) {
                $customerShipping = $shipping->exportCustomerAddress();
                $customer->addAddress($customerShipping);
            }

            if ($this->getQuote()->getCustomerDob() && !$billing->getCustomerDob()) {
                $billing->setCustomerDob($this->getQuote()->getCustomerDob());
            }

            if ($this->getQuote()->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
                $billing->setCustomerTaxvat($this->getQuote()->getCustomerTaxvat());
            }

            Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);

            $customer->setPassword($customer->decryptPassword($this->getQuote()->getPasswordHash()));
            $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));

            $this->getQuote()->setCustomer($customer);
            }
            break;

        default:
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                $customerBilling = $billing->exportCustomerAddress();
                $customer->addAddress($customerBilling);
            }
            if (!$this->getQuote()->isVirtual() &&
                ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) ||
                (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {

                $customerShipping = $shipping->exportCustomerAddress();
                $customer->addAddress($customerShipping);
            }
            $customer->setSavedFromQuote(true);
            $customer->save();

            $changed = false;
            if (isset($customerBilling) && !$customer->getDefaultBilling()) {
                $customer->setDefaultBilling($customerBilling->getId());
                $changed = true;
            }
            if (!$this->getQuote()->isVirtual() && isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
                $customer->setDefaultShipping($customerBilling->getId());
                $changed = true;
            }
            elseif (!$this->getQuote()->isVirtual() && isset($customerShipping) && !$customer->getDefaultShipping()){
                $customer->setDefaultShipping($customerShipping->getId());
                $changed = true;
            }

            if ($changed) {
                $customer->save();
            }
        }

//        $this->LogIfGuest("C",NULL);

        $this->getQuote()->reserveOrderId();
        $convertQuote = Mage::getModel('sales/convert_quote');
        /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
        //$order = Mage::getModel('sales/order');
        if ($this->getQuote()->isVirtual()) {
            $order = $convertQuote->addressToOrder($billing);
        }
        else {
            $order = $convertQuote->addressToOrder($shipping);
        }

//        $this->LogIfGuest("D",$order);

        /* @var $order Mage_Sales_Model_Order */
        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));

        if (!$this->getQuote()->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));

//        $this->LogIfGuest("E",$order);

        foreach ($this->getQuote()->getAllItems() as $item) {
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));

//        $this->LogIfGuest("F",$order);

        // check again, if customer exists
        /**
         *  QuickCheckout
         *  Added the && !$returningNotLoggedIn to the if-clause
         */
        if ($this->getQuote()->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER && !$returningNotLoggedIn) {
            if ($this->_customerEmailExists($customer->getEmail(), Mage::app()->getWebsite()->getId())) {
                Mage::throwException(Mage::helper('checkout')->__('There is already a customer registered using this email address'));
            }
        }
        $order->place();

//        $this->LogIfGuest("G",$order);

        /**
         *  QuickCheckout
         *  Added the && !$returningNotLoggedIn to the if-clause
         */
        if ($this->getQuote()->getCheckoutMethod()==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER && !$returningNotLoggedIn) {
            $customer->save();
            $customerBillingId = $customerBilling->getId();
            if (!$this->getQuote()->isVirtual()) {
                $customerShippingId = isset($customerShipping) ? $customerShipping->getId() : $customerBillingId;
                $customer->setDefaultShipping($customerShippingId);
            }
            $customer->setDefaultBilling($customerBillingId);
            $customer->save();

            $this->getQuote()->setCustomerId($customer->getId());


//            $this->LogIfGuest("H",$order);

            $order->setCustomerId($customer->getId());
            Mage::helper('core')->copyFieldset('customer_account', 'to_order', $customer, $order);

            $billing->setCustomerId($customer->getId())->setCustomerAddressId($customerBillingId);
            if (!$this->getQuote()->isVirtual()) {
                $shipping->setCustomerId($customer->getId())->setCustomerAddressId($customerShippingId);
            }

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail('confirmation');
            }
            else {
                $customer->sendNewAccountEmail();
            }
        }

//        $this->LogIfGuest("I",$order);

        /**
         *  QuickCheckout
         *  We need to connect the order with the customerId
         */
        $this->getQuote()->setCustomerId($customer->getId());
        $order->setCustomerId($customer->getId());

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        if(!$redirectUrl){
            $order->setEmailSent(true);
        }

//        $this->LogIfGuest("J",$order);

        $order->save();

        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         * need to have somelogic to set order as new status to make sure order is not finished yet
         * quote will be still active when we send the customer to paypal
         */

//        $this->LogIfGuest("K",$order);

        $orderId = $order->getIncrementId();
        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
        $this->getCheckout()->setLastOrderId($order->getId());
        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());
        $this->getCheckout()->setRedirectUrl($redirectUrl);

        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl){
            $order->sendNewOrderEmail();
        }

//        $this->LogIfGuest("L",$order);

        /**
         *  QuickCheckout
         *  Added the && !$returningNotLoggedIn to the if-clause
         */
        if ($this->getQuote()->getCheckoutMethod(true)==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER
            && !Mage::getSingleton('customer/session')->isLoggedIn() && !$returningNotLoggedIn) {
            /**
             * we need to save quote here to have it saved with Customer Id.
             * so when loginById() executes checkout/session method loadCustomerQuote
             * it would not create new quotes and merge it with old one.
             */
            $this->getQuote()->save();
            if ($customer->isConfirmationRequired()) {
                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.',
                    Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
                ));
            }
            else {
                Mage::getSingleton('customer/session')->loginById($customer->getId());
            }
        }

//        $this->LogIfGuest("M",$order);

        //Setting this one more time like control flag that we haves saved order
        //Must be checkout on success page to show it or not.
        $this->getCheckout()->setLastSuccessQuoteId($this->getQuote()->getId());

        $this->getQuote()->setIsActive(false);
        $this->getQuote()->save();

//        $this->LogIfGuest("N",$order);
//        $this->LogIfGuestEnd("_saveOrderQuick13",$order);

        return $this;
    }

    protected function _saveOrderQuickAndCredit14()
	{
        $this->validate();

//        $this->LogIfGuestStart("_saveOrderQuickAndCredit14");
//        $this->LogIfGuest("A",NULL);

		/* Icommerce QuickCheckout addon */
		$qcQuote = $this->getQuote();
        if(!is_null($qcQuote) && is_null($qcQuote->getCustomerId())){
				$qcCustEmail = $qcQuote->getBillingAddress()->getEmail();
				if ($this->_customerEmailExists($qcCustEmail, Mage::app()->getWebsite()->getId())) {
						$this->getQuote()->setCheckoutMethod(self::METHOD_CUSTOMER);
				}
				else {
						$this->getQuote()->setCheckoutMethod(self::METHOD_REGISTER);
				}
        }

//        $this->LogIfGuest("B",NULL);

        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();

				/**
				 * Creditcheck addon, checks if customer already has registered with another email-address.
				*/
				$ccCustomer = $this->getQuote()->getCustomer();
				$this->AddCustomizedFields($ccCustomer,$this->getQuote()->getBillingAddress(),true);

				if ($this->CustomerIDExists($ccCustomer->getCustomer_idnumber(),$ccCustomer->getCustomer_idtype(),-1, Mage::app()->getWebsite()->getId())) {
					if ($ccCustomer->getCustomer_idtype()=="person") {
						Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
					} else {
						Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
					}
				}

                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();

				/**
				 * Creditcheck addon, checks if customer already has registered with another email-address.
				*/
				$ccCustomer = $this->getQuote()->getCustomer();
				$this->AddCustomizedFields($ccCustomer,$this->getQuote()->getBillingAddress(),false);
				if ($this->CustomerIDExists($ccCustomer->getCustomer_idnumber(),$ccCustomer->getCustomer_idtype(),$ccCustomer->getEntity_id(), Mage::app()->getWebsite()->getId())) {
					if ($ccCustomer->getCustomer_idtype()=="person") {
						Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
					} else {
						Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
					}
				}

                break;
        }

//        $this->LogIfGuest("C",NULL);

        /**
		 * Creditcheck addon checks...
		 *  1. if customer is over credit limit
		 *  2. if address has changed
		*/
		$creditcheck = Mage::getModel('creditcheck/creditcheck');
        if ($this->getQuote()->getPayment()->getMethodInstance()->getCode() == $creditcheck->getConfigData('paymentmode')) { // checkmo (invoice payment)
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::CustomerOverCreditLimit($this->getQuote())) {
                Mage::throwException(Mage::helper('checkout')->__('Total amount higher than your credit limit, please use another payment method'));
            }
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::OKButChangedAddress($this->getQuote()->getBillingAddress(), $this->getQuote()->getShippingAddress())){
                Mage::throwException(Mage::helper('checkout')->__('You will not be able to use Invoice payment method because you changed the Identified name and address.'));
            }
        }

        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $service->submitAll();

//        $this->LogIfGuest("D",NULL);

        if ($isNewCustomer) {
            try {
/*
	Icommerce DIBS addon
	Magento Core BUG "fix"... that only comes into effect when a new customer shops using DIBS and Mag version 1.4+
	Magento standard function _involveNewCustomer deep down below inside loadCustomerQuote where it deletes the quote and places
	a new one specifically for this customer (don't know why, but it does). When it tries to read the quotation record it can't
	find it since is_active is set to false.
*/
				$quote = $this->getQuote();
				$quote->setIsActive(true);
				$quote->save();

//                $this->LogIfGuest("E",NULL);

                $this->_involveNewCustomer();

/*
	Icommerce QuickCheckout addon
	Need to set it back to false, otherwise it reappears as items in your shopping basket.
*/
				$quote = $this->getQuote();
				$quote->setIsActive(false);
				$quote->save();

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

//        $this->LogIfGuest("F",NULL);

        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData()
        ;

        $order = $service->getOrder();

//        $this->LogIfGuest("G",$order);

        if ($order) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

//            $this->LogIfGuest("H",$order);

            /**
             * a flag to set that there will be redirect to third party after confirmation
             * eg: paypal standard ipn
             */
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if(!$redirectUrl && ($order->getCanSendNewEmailFlag() || $order->getCanSendNewEmailFlag()===NULL)) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

//            $this->LogIfGuest("I",$order);

            // add order information to the session
            $this->_checkoutSession->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
            }
        }

//        $this->LogIfGuest("J",$order);

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_checkoutSession->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

//        $this->LogIfGuest("K",$order);

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
        );

//        $this->LogIfGuest("L",$order);
//        $this->LogIfGuestEnd("_saveOrderQuickAndCredit14",$order);

        return $this;
    }

    protected function _saveOrderCredit14()
	{
        $this->validate();

		$isNewCustomer = false;

		switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();

				/**
				 * Creditcheck addon, checks if customer already has registered with another email-address.
				*/
				$ccCustomer = $this->getQuote()->getCustomer();
				$this->AddCustomizedFields($ccCustomer,$this->getQuote()->getBillingAddress(),true);

				if ($this->CustomerIDExists($ccCustomer->getCustomer_idnumber(),$ccCustomer->getCustomer_idtype(),-1, Mage::app()->getWebsite()->getId())) {
					if ($ccCustomer->getCustomer_idtype()=="person") {
						Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
					} else {
						Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
					}
				}

                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();

				/**
				 * Creditcheck addon, checks if customer already has registered with another email-address.
				*/
				$ccCustomer = $this->getQuote()->getCustomer();
				$this->AddCustomizedFields($ccCustomer,$this->getQuote()->getBillingAddress(),false);
				if ($this->CustomerIDExists($ccCustomer->getCustomer_idnumber(),$ccCustomer->getCustomer_idtype(),$ccCustomer->getEntity_id(), Mage::app()->getWebsite()->getId())) {
					if ($ccCustomer->getCustomer_idtype()=="person") {
						Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
					} else {
						Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
					}
				}

                break;
        }

        /**
		 * Creditcheck addon checks...
		 *  1. if customer is over credit limit
		 *  2. if customer has changed his/her address
		*/
		$creditcheck = Mage::getModel('creditcheck/creditcheck');
        if ($this->getQuote()->getPayment()->getMethodInstance()->getCode() == $creditcheck->getConfigData('paymentmode')) { // checkmo (invoice payment)
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::CustomerOverCreditLimit($this->getQuote())) {
                Mage::throwException(Mage::helper('checkout')->__('Total amount higher than your credit limit, please use another payment method'));
            }
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::OKButChangedAddress($this->getQuote()->getBillingAddress(), $this->getQuote()->getShippingAddress())){
                Mage::throwException(Mage::helper('checkout')->__('You will not be able to use Invoice payment method because you changed the Identified name and address.'));
            }
        }

        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $service->submitAll();

        if ($isNewCustomer) {
            try {
/*
	Icommerce DIBS addon
	Magento Core BUG "fix"... that only comes into effect when a new customer shops using DIBS and Mag version 1.4+
	Magento standard function _involveNewCustomer deep down below inside loadCustomerQuote where it deletes the quote and places
	a new one specifically for this customer (don't know why, but it does). When it tries to read the quotation record it can't
	find it since is_active is set to false.
*/
				$quote = $this->getQuote();
				$quote->setIsActive(true);
				$quote->save();

                $this->_involveNewCustomer();

/*
	Icommerce QuickCheckout addon
	Need to set it back to false, otherwise it reappears as items in your shopping basket.
*/
				$quote = $this->getQuote();
				$quote->setIsActive(false);
				$quote->save();

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData()
        ;

        $order = $service->getOrder();
        if ($order) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

            /**
             * a flag to set that there will be redirect to third party after confirmation
             * eg: paypal standard ipn
             */
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if(!$redirectUrl && ($order->getCanSendNewEmailFlag() || $order->getCanSendNewEmailFlag()===NULL)) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            // add order information to the session
            $this->_checkoutSession->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
            }
        }

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_checkoutSession->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
        );

        return $this;
    }

   /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _saveOrderQuick14()
	{
        $this->validate();

//        $this->LogIfGuestStart("_saveOrderQuick14");
//        $this->LogIfGuest("A",NULL);

		/* Icommerce QuickCheckout addon */
		$qcQuote = $this->getQuote();

        Mage::dispatchEvent('checkout_onepage_saveorder_before', array('qcQuote' => $qcQuote));

        if(!is_null($qcQuote) && is_null($qcQuote->getCustomerId())){
				$qcCustEmail = $qcQuote->getBillingAddress()->getEmail();
				if ($this->_customerEmailExists($qcCustEmail, Mage::app()->getWebsite()->getId())) {
						$this->getQuote()->setCheckoutMethod(self::METHOD_CUSTOMER);
				}
				else {
						$this->getQuote()->setCheckoutMethod(self::METHOD_REGISTER);
				}
        }

//        $this->LogIfGuest("B",NULL);

        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

        if ($this->getQuote()->getPayment()->getMethod() == Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS) {
            $this->getCheckout()->setRedirectUrl($this->getQuote()->getPayment()->getCheckoutRedirectUrl());

            return;
        }

//        $this->LogIfGuest("C",NULL);

        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $service->submitAll();

//        $this->LogIfGuest("D",NULL);

        $isDibsRedirectActive = false;

        if (Icommerce_Default::isModuleActive('Icommerce_Dibs')) {
            $dibs = Mage::getModel('dibs/dibs');
            if ($dibs->getConfigData('redirect_to_cart_on_cancel', $dibs->getStoreId())) {
                $isDibsRedirectActive = true;
            }
        }

        if ($isNewCustomer || $isDibsRedirectActive) {
            try {
/*
	Icommerce DIBS addon
	Magento Core BUG "fix"... that only comes into effect when a new customer shops using DIBS and Mag version 1.4+
	Magento standard function _involveNewCustomer deep down below inside loadCustomerQuote where it deletes the quote and places
	a new one specifically for this customer (don't know why, but it does). When it tries to read the quotation record it can't
	find it since is_active is set to false.
*/
				$quote = $this->getQuote();
				$quote->setIsActive(true);
				$quote->save();

//                $this->LogIfGuest("E",NULL);
                if ($isNewCustomer) {
                    $this->_involveNewCustomer();
                }else{
                    $customer = $this->getQuote()->getCustomer();
                    if ($customer->getId()) {
                        $this->getCustomerSession()->loginById($customer->getId());
                    }
                }

/*
	Icommerce DIBS addon
	Need to set it back to false, otherwise it reappears as items in your shopping basket.
*/
				$quote = $this->getQuote();
				$quote->setIsActive(false);
				$quote->save();

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

//        $this->LogIfGuest("F",NULL);

        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData()
        ;

        $order = $service->getOrder();

//        $this->LogIfGuest("G",$order);

        if ($order) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

//            $this->LogIfGuest("H",$order);
            /**
             * a flag to set that there will be redirect to third party after confirmation
             * eg: paypal standard ipn
             */
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if(!$redirectUrl && ($order->getCanSendNewEmailFlag() || $order->getCanSendNewEmailFlag()===NULL)) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

//            $this->LogIfGuest("I",$order);

            // add order information to the session
            $this->_checkoutSession->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
            }
        }

//        $this->LogIfGuest("J",$order);

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_checkoutSession->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

//        $this->LogIfGuest("K",$order);
        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
        );

//        $this->LogIfGuest("L",$order);
//        $this->LogIfGuestEnd("_saveOrderQuick14",$order);

        return $this;
    }

   /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _saveOrder14()
    {
        $this->validate();
        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $service->submitAll();

        if ($isNewCustomer) {
            try {
/*
	Icommerce DIBS addon
	Magento Core BUG "fix"... that only comes into effect when a new customer shops using DIBS and Mag version 1.4+
	Magento standard function _involveNewCustomer deep down below inside loadCustomerQuote where it deletes the quote and places
	a new one specifically for this customer (don't know why, but it does). When it tries to read the quotation record it can't
	find it since is_active is set to false.
*/
				$quote = $this->getQuote();
				$quote->setIsActive(true);
				$quote->save();

                $this->_involveNewCustomer();

/*
	Icommerce DIBS addon
	Need to set it back to false, otherwise it reappears as items in your shopping basket.
*/
				$quote = $this->getQuote();
				$quote->setIsActive(false);
				$quote->save();

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData()
        ;

        $order = $service->getOrder();
        if ($order) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

            /**
             * a flag to set that there will be redirect to third party after confirmation
             * eg: paypal standard ipn
             */
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if(!$redirectUrl && ($order->getCanSendNewEmailFlag() || $order->getCanSendNewEmailFlag()===NULL)) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            // add order information to the session
            $this->_checkoutSession->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
            }
        }

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_checkoutSession->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

        return $this;
    }

    /**
     * Icommerce addon, shared overridden function between CreditCheck and QuickCheckout
     *
     * @return array
     */
	public function saveOrder()
	{
		$a = Icommerce_Default::getMagentoVersion();
		if( Icommerce_Default::getMagentoVersion()<1400){
		    if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout') && Icommerce_Default_Helper_Data::isModuleActive('Icommerce_Creditcheck')) {
				$this->_saveOrderQuickAndCredit13();
			} else if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_Creditcheck')) {
				$this->_saveOrderCredit13();
			} else if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout')) {
				$this->_saveOrderQuick13();
			} else {
				return parent::saveOrder();
			}
		} elseif( Icommerce_Default::getMagentoVersion()>=1800 and Icommerce_Default::getMagentoVersion()<1899){ // Don't know what versions needs this... I know 1.8.0.0 does...
		    if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout') && Icommerce_Default_Helper_Data::isModuleActive('Icommerce_Creditcheck')) {
				$this->_saveOrderQuickAndCredit18();
			} else if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_Creditcheck')) {
				$this->_saveOrderCredit18();
			} else if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout')) {
				$this->_saveOrderQuick18();
			} else {
				return parent::saveOrder();
			}
		} else {
			if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout') && Icommerce_Default_Helper_Data::isModuleActive('Icommerce_Creditcheck')) {
				$this->_saveOrderQuickAndCredit14();
			} else if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_Creditcheck')) {
				$this->_saveOrderCredit14();
            } else if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckout')) {
				$this->_saveOrderQuick14();
			} else {
				$this->_saveOrder14(); // Shouldn't this be : return parent::saveOrder(); ?????
			}
		}

        if( Mage::getStoreConfig("paymentshared/payments/save_pno_with_billing") ){
            $cust = Mage::getSingleton("customer/session")->getCustomer();
            if( !$cust ){
                // If customer was logged out by QC
                $cust = Mage::getModel("customer/customer")->setData("website_id",Icommerce_Default::getWebsiteId());
                $email = $this->getQuote()->getData("customer_email");
                $cust->loadByEmail($email);
            }

            if( $cust->getId() ){
                $email = $cust->getData("email");
                $personid = Icommerce_Db::getValue("SELECT personid FROM ic_checkout_email_save WHERE email LIKE '$email'");
                if( $personid ){
                    $cust->setData("taxvat",$personid)->save();
                }
            }
        }

	}


 protected function _saveOrderQuickAndCredit18()
	{
        $this->validate();

		/* Icommerce QuickCheckout addon */
		$qcQuote = $this->getQuote();
        if(!is_null($qcQuote) && is_null($qcQuote->getCustomerId())){
				$qcCustEmail = $qcQuote->getBillingAddress()->getEmail();
				if ($this->_customerEmailExists($qcCustEmail, Mage::app()->getWebsite()->getId())) {
						$this->getQuote()->setCheckoutMethod(self::METHOD_CUSTOMER);
				}
				else {
						$this->getQuote()->setCheckoutMethod(self::METHOD_REGISTER);
				}
        }

        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();

				/**
				 * Creditcheck addon, checks if customer already has registered with another email-address.
				*/
				$ccCustomer = $this->getQuote()->getCustomer();
				$this->AddCustomizedFields($ccCustomer,$this->getQuote()->getBillingAddress(),true);

				if ($this->CustomerIDExists($ccCustomer->getCustomer_idnumber(),$ccCustomer->getCustomer_idtype(),-1, Mage::app()->getWebsite()->getId())) {
					if ($ccCustomer->getCustomer_idtype()=="person") {
						Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
					} else {
						Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
					}
				}

                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();

				/**
				 * Creditcheck addon, checks if customer already has registered with another email-address.
				*/
				$ccCustomer = $this->getQuote()->getCustomer();
				$this->AddCustomizedFields($ccCustomer,$this->getQuote()->getBillingAddress(),false);
				if ($this->CustomerIDExists($ccCustomer->getCustomer_idnumber(),$ccCustomer->getCustomer_idtype(),$ccCustomer->getEntity_id(), Mage::app()->getWebsite()->getId())) {
					if ($ccCustomer->getCustomer_idtype()=="person") {
						Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
					} else {
						Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
					}
				}

                break;
        }

        /**
		 * Creditcheck addon checks...
		 *  1. if customer is over credit limit
		 *  2. if address has changed
		*/
		$creditcheck = Mage::getModel('creditcheck/creditcheck');
        if ($this->getQuote()->getPayment()->getMethodInstance()->getCode() == $creditcheck->getConfigData('paymentmode')) { // checkmo (invoice payment)
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::CustomerOverCreditLimit($this->getQuote())) {
                Mage::throwException(Mage::helper('checkout')->__('Total amount higher than your credit limit, please use another payment method'));
            }
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::OKButChangedAddress($this->getQuote()->getBillingAddress(), $this->getQuote()->getShippingAddress())){
                Mage::throwException(Mage::helper('checkout')->__('You will not be able to use Invoice payment method because you changed the Identified name and address.'));
            }
        }

           $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $order = $service->submit();

                if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl){
            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setRedirectUrl($redirectUrl)
            ->setLastSuccessQuoteId($this->getQuote()->getId());
        return $this;

    }

    protected function _saveOrderCredit18()
	{
        $this->validate();

		$isNewCustomer = false;

		switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();

				/**
				 * Creditcheck addon, checks if customer already has registered with another email-address.
				*/
				$ccCustomer = $this->getQuote()->getCustomer();
				$this->AddCustomizedFields($ccCustomer,$this->getQuote()->getBillingAddress(),true);

				if ($this->CustomerIDExists($ccCustomer->getCustomer_idnumber(),$ccCustomer->getCustomer_idtype(),-1, Mage::app()->getWebsite()->getId())) {
					if ($ccCustomer->getCustomer_idtype()=="person") {
						Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
					} else {
						Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
					}
				}

                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();

				/**
				 * Creditcheck addon, checks if customer already has registered with another email-address.
				*/
				$ccCustomer = $this->getQuote()->getCustomer();
				$this->AddCustomizedFields($ccCustomer,$this->getQuote()->getBillingAddress(),false);
				if ($this->CustomerIDExists($ccCustomer->getCustomer_idnumber(),$ccCustomer->getCustomer_idtype(),$ccCustomer->getEntity_id(), Mage::app()->getWebsite()->getId())) {
					if ($ccCustomer->getCustomer_idtype()=="person") {
						Mage::throwException(Mage::helper('checkout')->__('This person has already registered with another e-mail address'));
					} else {
						Mage::throwException(Mage::helper('checkout')->__('This company has already registered with another e-mail address'));
					}
				}

                break;
        }

        /**
		 * Creditcheck addon checks...
		 *  1. if customer is over credit limit
		 *  2. if customer has changed his/her address
		*/
		$creditcheck = Mage::getModel('creditcheck/creditcheck');
        if ($this->getQuote()->getPayment()->getMethodInstance()->getCode() == $creditcheck->getConfigData('paymentmode')) { // checkmo (invoice payment)
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::CustomerOverCreditLimit($this->getQuote())) {
                Mage::throwException(Mage::helper('checkout')->__('Total amount higher than your credit limit, please use another payment method'));
            }
            if (Icommerce_Creditcheck_Block_Checkout_Onepage_Payment_Methods::OKButChangedAddress($this->getQuote()->getBillingAddress(), $this->getQuote()->getShippingAddress())){
                Mage::throwException(Mage::helper('checkout')->__('You will not be able to use Invoice payment method because you changed the Identified name and address.'));
            }
        }

           $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $order = $service->submit();

                if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl){
            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setRedirectUrl($redirectUrl)
            ->setLastSuccessQuoteId($this->getQuote()->getId());
        return $this;

    }

   /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _saveOrderQuick18()
	{
        $this->validate();

		/* Icommerce QuickCheckout addon */
		$qcQuote = $this->getQuote();
        if(!is_null($qcQuote) && is_null($qcQuote->getCustomerId())){
				$qcCustEmail = $qcQuote->getBillingAddress()->getEmail();
				if ($this->_customerEmailExists($qcCustEmail, Mage::app()->getWebsite()->getId())) {
						$this->getQuote()->setCheckoutMethod(self::METHOD_CUSTOMER);
				}
				else {
						$this->getQuote()->setCheckoutMethod(self::METHOD_REGISTER);
				}
        }

        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

          $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $order = $service->submit();

                if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl){
            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setRedirectUrl($redirectUrl)
            ->setLastSuccessQuoteId($this->getQuote()->getId());
        return $this;

    }

   /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _saveOrder18()
    {
        $this->validate();
        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

           $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $order = $service->submit();

                if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         * a flag to set that there will be redirect to third party after confirmation
         * eg: paypal standard ipn
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        /**
         * we only want to send to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl){
            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setRedirectUrl($redirectUrl)
            ->setLastSuccessQuoteId($this->getQuote()->getId());
        return $this;

    }
/*
    public function savePayment($data)
    {
    	if (Icommerce_Default::getMagentoVersion()>=1400) {
	        if (empty($data)) {
    	        return array('error' => -1, 'message' => $this->_helper->__('Invalid data'));
        	}
	        if ($this->getQuote()->isVirtual()) {
    	        $this->getQuote()->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        	} else {
            	$this->getQuote()->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
	        }

    	    $payment = $this->getQuote()->getPayment();
        	$payment->importData($data);

	        $this->getQuote()->collectTotals()->save();
//  	      $this->getQuote()->save();

        	$this->getCheckout()
            	->setStepData('payment', 'complete', true)
	            ->setStepData('review', 'allow', true);

    	    return array();
       	} else {
       		return parent::savePayment($data);
       	}
    }
*/
    public function checkCreateDir( $log_dir ){
        $sl = strlen($log_dir);
        if( $sl && $log_dir[$sl-1]=='/' ){
            $log_dir = substr($log_dir,0,$sl-1);
        }
        if( !is_dir($log_dir) ){
            if( is_file($log_dir) ){
                @unlink($log_dir);
            }
            if( !@mkdir( $log_dir ) ){
                return null;
            }
        }
        return true;
    }

    public function log($msg, $log_path)
    {
        try {
            if( file_exists($log_path) ){
                $fp = fopen( $log_path, "a" );
            } else {
                // Directory exists?
                $p = strrpos( $log_path, "/" );
                if( $p!==FALSE ){
                    $log_dir = substr( $log_path, 0, $p );
                    if( !$this->checkCreateDir($log_dir) ){
                        return null;
                    }
                }
                $fp = fopen( $log_path, "w" );
            }
        } catch( Exception $e ) {
            return null;
        }
        if( !$fp ) return null;

        // Convert object / array to string
        if (is_array($msg) || is_object($msg)) {
            $msg = print_r($msg, true);
        }

        $sl = strlen($msg);
        if( !$sl || $msg[$sl-1]!=="\n" ){
            $msg .= "\n";
        }
        fwrite( $fp, $msg );
        fclose( $fp );
        return true;
    }

    public function LogIfGuestStart($function)
    {
        $this->log('----------START ' . $function . ' ' . now() . '----------','var/order/onepage.log');
    }

    public function LogIfGuest($pos, $order)
    {
        $str = $pos . ' Quote ID: ' . $this->getQuote()->getId() . ' | Method: ' . $this->getQuote()->getCheckoutMethod() . ' | active: ' . $this->getQuote()->getIsActive();
        if ($order) {
            $customer = $order->getCustomer();
            if ($customer) {
                $str .= ' | Customer ID (customer): ' . $customer->getId() . ' | E-mail: ' . $customer->getEmail();
            } else {
                $str .= ' | Customer ID (customer): null | E-mail: ' . $order->getCustomerEmail();
            }
            $str .= ' | Order ID: ' . $order->getId() . ' | Customer ID (order): ' . $order->getCustomerId();
        } else {
            $customer = $this->getQuote()->getCustomer();
            if ($customer) {
                $str .= ' | Customer ID (quote): ' . $customer->getId() . ' | E-mail: ' . $customer->getEmail();
            } else {
                $str .= ' | Customer ID (quote): null | E-mail: ' . $order->getCustomerEmail();
            }
            $str .= ' | Order pending';
        }
        $this->log($str,'var/order/onepage.log');
    }

    public function LogIfGuestEnd($function,$order)
    {
        if ($order) {
            $this->log('Order ID: ' . $order->getId(),'var/order/onepage.log');
            if ($order->getCustomerIsGuest() || $order->getCustomerId()<=0) {
                Icommerce_Default::logAppendBT('Order is done by guest! Here is the backtrace:','var/order/onepage.log');
            }
        }
        $this->log('----------END ' . $function . ' ' . now() . '----------','var/order/onepage.log');
        $this->log('','var/order/onepage.log');
    }

    // This function is depricated, but since we are using depricated functions anyhow in this code, we are calling this no matter what. This is a chance to customize it...
    protected function _processValidateCustomer(Mage_Sales_Model_Quote_Address $address)
    {
        $status = new Varien_Object(array('returnvalue' => false));
        Mage::dispatchEvent('checkoutcommon_process_validate_customer', array('quote'=>$this->getQuote(), 'address'=>$address, 'status' => $status));
        if ($status->getReturnvalue()) {
            return $status->getReturnvalue();
        } else {
            return parent::_processValidateCustomer($address);
        }
    }


}
