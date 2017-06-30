<?php

class Ebizmarts_BakerlooRestful_Helper_Sales extends Mage_Core_Helper_Abstract {

    private $_quote = null;
    private $_loyalty = null;

    /**
     * Build quote for order.
     *
     * @param $storeId
     * @param $data
     * @param bool $onlyQuote
     * @return mixed
     */
    public function buildQuote($storeId, $data, $onlyQuote = false) {

        Varien_Profiler::start('POS::' . __METHOD__);

        Mage::app()->getStore()->setCurrentCurrencyCode($data->currency_code);
        $store = Mage::app()->getStore();
        Mage::helper('bakerloo_restful/pages')->disableFlatCatalogAndCategory($storeId);

        $quote = Mage::getModel('sales/quote')
            ->setStoreId($storeId)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->save();

        $this->setQuote($quote);

        //Adding products to Quote
        if(!is_array($data->products) or empty($data->products))
            Mage::throwException( Mage::helper('bakerloo_restful')->__('ALERT: No products provided on order.') );

        $this->_addProductsToQuote($data->products);
        //Adding products to Quote

        //Get customer
        $customerId = (int)$data->customer->customer_id;

        $customerExistsByEmail = $this->customerExists($data->customer->email, $store->getWebsiteId());
        if(false !== $customerExistsByEmail) {
            $customerId = $customerExistsByEmail->getId();
        }
        $customer = Mage::getModel("customer/customer")->load($customerId);
        //Get customer

        /* Save data to session for extensions compatibility */
        if($customer->getId()) {
            $session = Mage::getModel('checkout/session');

            $session->setCustomer($customer)
                ->setCustomerId($customerId);

            Mage::getSingleton('customer/session')
                ->setCustomer($customer);

            $payments = (array)$data->payment->addedPayments;
            if($data->payment->method == 'bakerloo_magestorecredit') {
                $session->setBaseCustomerCreditAmount($data->payment->amount);
                Mage::register('pos_credit_amount', $data->payment->amount);
            }
            elseif(!empty($payments)) {

                foreach($payments as $_payment) {
                    if($_payment->method == 'bakerloo_magestorecredit') {
                        $session->setBaseCustomerCreditAmount($_payment->amount);
                        Mage::register('pos_credit_amount', $_payment->amount);
                    }
                }

            }
        }

        if(!$this->getQuote()->isVirtual()) {
            $shippingAddress = $this->_getAddress($data->customer->shipping_address, $data->customer->email);

            $this->getQuote()->getShippingAddress()
                ->addData($shippingAddress)
                ->save();

            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true)->save();
        }

        if($onlyQuote) {

            if($customerId)
                $this->getQuote()->setCustomerId($customerId);
            elseif($customerExistsByEmail) {
                $this->getQuote()->setCustomerId($customerId);
                $customerId = $customerExistsByEmail->getId();
            }

            if ($this->getQuote()->isVirtual()) {
                $this->getQuote()->getBillingAddress()->getTotals();
            }
            else {
                $this->getQuote()->getShippingAddress()->collectTotals();
            }

            /* prevent totals from collecting twice if using Magestore Extensions */
            if(Mage::helper('bakerloo_gifting')->getIntegrationFromConfig() == 'Magestore_Giftvoucher' and !empty($giftCards)
                || $data->payment->method == 'bakerloo_magestorecredit')
                $quote->setTotalsCollectedFlag(true);

            return $this->getQuote();
        }

        $canUseLoyalty = Mage::helper('bakerloo_loyalty')->canUse();
        if($canUseLoyalty && $customerId) {
            $this->_loyalty = Mage::getModel('bakerloo_restful/integrationDispatcher', array('integration_type' => 'loyalty', 'customer_id' => $customerId, 'website_id' => Mage::app()->getStore()->getWebsiteId()));

            /*Fix for TBT_Rewards, points not saved to customer otherwise.*/
            if (Mage::helper('bakerloo_loyalty')->isSweetTooth($this->_loyalty)) {
                $this->getQuote()->save();
            }
            /*Fix for TBT_Rewards, points not saved to customer otherwise.*/
        }


        if( Mage_Checkout_Model_Type_Onepage::METHOD_GUEST == $data->customer->mode && (false === $customerExistsByEmail) ) {

            $ownerEmail = (string)Mage::app()->getStore()->getConfig('trans_email/ident_general/email');

            if( (((string)$data->customer->email) != $ownerEmail) and (1 === (int)Mage::helper('bakerloo_restful')->config('checkout/create_customer')) ) {
                //Involve new customer if the one provided does not exist
                $this->_involveNewCustomer($data);
            }
            else {

                $this->getQuote()->setCheckoutMethod($data->customer->mode);

                $this->getQuote()
                    ->setCustomerEmail($data->customer->email)
                    ->setCustomerId(null)
                    ->setCustomerIsGuest(true)
                    ->setCustomerFirstname($data->customer->firstname)
                    ->setCustomerLastname($data->customer->lastname);

                $this->getQuote()->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
            }

        }
        else {

            $this->getQuote()
                ->setCustomer($customer)
                ->setPasswordHash($customer->encryptPassword($customer->getPassword()));

            /*Fix for TBT_Rewards, points not saved to customer otherwise.*/
            if($canUseLoyalty && Mage::helper('bakerloo_loyalty')->isSweetTooth($this->_loyalty)) {
                Mage::getSingleton ('customer/session')->loginById($customerId);
                $this->getQuote()->save();
            }
            /*Fix for TBT_Rewards, points not saved to customer otherwise.*/

        }

        if($data->total_amount == 0 && !$this->getQuote()->isVirtual()) {

            $this->getQuote()->getShippingAddress()
                ->setShippingMethod($data->shipping);
        }
        else {

            if($this->getQuote()->isVirtual()) {
                $this->getQuote()->getBillingAddress()
                    ->setPaymentMethod($data->payment->method);
            }
            else {
                $this->getQuote()->getShippingAddress()
                    ->setPaymentMethod($data->payment->method)
                    ->setShippingMethod($data->shipping);
            }

        }

        $billingAddress  = $this->_getAddress($data->customer->billing_address, $data->customer->email);
        $this->getQuote()->getBillingAddress()
            ->addData($billingAddress);

        //Apply coupon if present
        $checkCouponOK = false;
        if(isset($data->coupon_code) && !empty($data->coupon_code)) {
            $couponCode = $data->coupon_code;

            $this->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '');

            $checkCouponOK = true;
        }

        //Apply gift cards if present
        $giftCards = isset($data->gift_card) ? $data->gift_card : null;
        if(!empty($giftCards) and is_array($giftCards)) {

            $session = Mage::getSingleton('checkout/session');
            $session->unsetData('gift_codes')
                ->unsetData('codes_discount');

            foreach($giftCards as $_giftCardCode) {
                Mage::getModel('bakerloo_restful/integrationDispatcher',
                    array('integration_type' => 'gifting', 'store_id' => $storeId, 'code' => $_giftCardCode))
                ->addToCart($this->getQuote());
            }

        }

        if ($this->getQuote()->isVirtual()) {
            $this->getQuote()->getBillingAddress()->getTotals();
        }
        else {
            $this->getQuote()->getShippingAddress()->collectTotals();
        }

        /* prevent totals from collecting twice if using Magestore Extensions */
        if((Mage::helper('bakerloo_gifting')->getIntegrationFromConfig() == 'Magestore_Giftvoucher' and !empty($giftCards))
            || $data->payment->method == 'bakerloo_magestorecredit'
        )
            $quote->setTotalsCollectedFlag(true);


        //Apply loyalty rules if present
        $loyalty = isset($data->loyalty) ? $data->loyalty : null;
        if($loyalty) {

            $applied = Mage::getModel('rewards/salesrule_list_applied')->initQuote($this->getQuote());

            foreach($loyalty as $rule) {
                if($rule->points_amount > 0)
                    Mage::getSingleton('rewards/session')->setPointsSpending($rule->points_amount);

                $applied->add($rule->rule_id)->saveToQuote($this->getQuote());
            }
        }

        if($data->total_amount != 0) {

            //Use Reward Points
            if(isset($data->payment->use_reward_points) and ((int)$data->payment->use_reward_points === 1)) {
                $this->getQuote()->setUseRewardPoints(true);
            }

            //Use Customer Balance
            if(isset($data->payment->use_customer_balance) and ((int)$data->payment->use_customer_balance === 1)) {
                $this->getQuote()->setUseCustomerBalance(true);
            }

            $this->getQuote()->getPayment()->importData((array)$data->payment);
        }
        else {

            /* workaround in case full payment is made with giftcard */
            if(!empty($giftCards))
                $this->getQuote()->getPayment()->importData(array('method' => 'free'));
            else
                $this->getQuote()->getPayment()->importData(array('method' => 'bakerloo_free'));

        }

        //Commented on January, 6 2015 to fix issue with coupons applied twice on bundle products.
        //$this->getQuote()->collectTotals()->save();
        $this->getQuote()->save();

        //If coupon was provided and does not validate, throw error.
        if($checkCouponOK) {
            if (!$this->getQuote()->getCouponCode()) {
                Mage::throwException( Mage::helper('bakerloo_restful')->__('Discount coupon could not be applied, please try again.') );
            }
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $this->getQuote();
    }


    public function _addProductsToQuote($products) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $useSimplePrice = (int)Mage::helper('bakerloo_restful')->config('general/simple_configurable_prices', Mage::app()->getStore()->getId());

        foreach($products as $_product) {

            $product = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('weight')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('price_type')
                ->addAttributeToSelect('tax_class_id')
                ->addAttributeToFilter('entity_id', array('eq' => $_product->product_id))
                ->getFirstItem();

            $stockItem = Mage::getResourceModel('cataloginventory/stock_item_collection')
                ->addProductsFilter(array($product))
                ->getFirstItem();
            $product->setStockItem($stockItem);

            if(!$product->getId()) {
                Mage::throwException('Product ID: ' . $_product->product_id . " does not exist.");
            }

            $buyInfo = $this->getBuyInfo($_product, $product);

            try {

                if (((int)Mage::helper('bakerloo_restful')->config('catalog/allow_backorders'))) {
                    if (!Mage::registry(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES))
                        Mage::register(Ebizmarts_BakerlooRestful_Model_Rewrite_CatalogInventory_Stock_Item::BACKORDERS_YES, true);
                }

                //Skip stock checking
                if(Mage::helper('bakerloo_restful')->dontCheckStock()) {
                    $product->getStockItem()->setData('manage_stock', 0);
                    $product->getStockItem()->setData('use_config_manage_stock', 0);
                }

                //if simple_configurable_product enabled, use child's price
                if(isset($_product->child_id) and !is_null($_product->child_id) and $useSimplePrice === 1) {
                    $product->setPrice($_product->price);
                    $product->setSpecialPrice('');
                }

                if (isset($_product->no_tax) and $_product->no_tax) {
                    $_taxHelper         = Mage::helper('tax');
                    $_finalPriceExclTax = $_taxHelper->getPrice($product, $product->getFinalPrice(), false);
                    $product->setTaxClassId('0');
                    $product->setPrice($_finalPriceExclTax);
                    $product->setSpecialPrice('');
                }

                $quoteItem = $this->getQuote()->addProduct($product, new Varien_Object($buyInfo));

                if(isset($_product->loyalty) and !empty($_product->loyalty)) {
                    foreach($_product->loyalty as $rule) {

                        Mage::getSingleton('rewards/catalogrule_saver')->writePointsToQuote(
                                $product, (int)$rule->rule_id, $rule->rule_uses, 1, $quoteItem
                        );
                    }
                }


            }catch (Exception $qex) {
                Mage::throwException("An error occurred, Product SKU: {$product->getSku()}. Error Message: {$qex->getMessage()}");
            }

            if(is_string($quoteItem)) {
                Mage::throwException($quoteItem . ' Product ID: ' . $_product->product_id);
            }

            //@TODO: Discount amount per line, see discount.
            if(isset($_product->is_custom_price) and (int)$_product->is_custom_price === 1)
                    $this->_applyCustomPrice($quoteItem, $_product->price);
            elseif(isset($_product->price))
                $this->_applyCustomPrice($quoteItem, $_product->price);


            //Discount reasons
            if(isset($_product->discount_reason)) {
                if($quoteItem->getParentItem()) {
                    $quoteItem->getParentItem()->setPosDiscountReason($_product->discount_reason);
                }
                else {
                    $quoteItem->setPosDiscountReason($_product->discount_reason);
                }
            }

            unset($product);

        }//foreach

        foreach($this->getQuote()->getAllItems() as $item)
            $item->save();

        Varien_Profiler::stop('POS::' . __METHOD__);
    }

    public function getBuyInfo($_product, $product = null) {
        $buyInfo = array();
        $buyInfo['qty'] = isset($_product->qty) ? ($_product->qty * 1) : 1;
        $productType = isset($_product->type) ? (string)$_product->type : "";

        //Configurable attributes
        if(isset($_product->super_attribute)) {

            $superAttribute = $_product->super_attribute;
            if(is_array($superAttribute) && !empty($superAttribute)) {

                $superRequest = array();

                foreach($superAttribute as $_at) {

                    $attribute = Mage::getModel('catalog/resource_eav_attribute')
                        ->loadByCode(Mage_Catalog_Model_Product::ENTITY, (string)$_at->attribute_code);

                    $superRequest[$attribute->getId()] = (string)$_at->value_index;

                }

                $buyInfo['super_attribute'] = $superRequest;
            }

        }

        //Grouped product
        if(isset($_product->super_group)) {
            $superGroup = array();

            foreach($_product->super_group as $_sg) {

                $_sgQty = isset($_sg->qty) ? (int)$_sg->qty : 0;

                if($_sgQty > 0)
                    $superGroup[$_sg->product_id] = $_sgQty;
                
            }
            $buyInfo['super_group'] = $superGroup;
        }

        //Bundle product
        if(isset($_product->bundle_option) and is_array($_product->bundle_option))
            $buyInfo += $this->buyInfoAddBundleOptions($_product);

        //@TODO: Support FILES.
        //Product custom options
        if(isset($_product->options) and is_array($_product->options)) {

            $options = $_product->options;

            $optionsRequest = array();

            foreach($options as $_opt) {
                $selected = (int)$_opt->option_type_id;

                if($selected) {
                    $optionsRequest[$_opt->option_id] = $selected;
                }
                else {
                    if(isset($_opt->text)) {
                        if ($_opt->type =='date' || $_opt->type =='date_time' || $_opt->type =='time')
                            $optionsRequest[$_opt->option_id] = $this->getOptionForDateTime($_opt->text);
                        else
                            $optionsRequest[$_opt->option_id] = (string)$_opt->text;
                    }
                    else {
                        if($_opt->type == 'multiple' or $_opt->type == 'checkbox')
                            $optionsRequest[$_opt->option_id] = $_opt->option_type_ids;
                    }
                }
            }

            $buyInfo['options'] = $optionsRequest;
        }

        $giftcard = Mage::helper('bakerloo_gifting')->getGiftcard($productType);
        if(!is_null($giftcard)){

            if(Mage::registry('haitv_product_' . $product->getId()))
                Mage::unregister('haitv_product_' . $product->getId());

            $giftcardOptions = $giftcard->getBuyInfoOptions($_product);
            $buyInfo = array_merge($buyInfo, $giftcardOptions);
        }

        $buyInfo = new Varien_Object($buyInfo);
        Mage::dispatchEvent('pos_add_product_to_cart',
            array('info_buy_request' => $buyInfo, 'product' => $product)
        );


        return $buyInfo->getData();
    }

    //Product options date, date-time and time
    public function getOptionForDateTime($elements){
        return date_parse($elements);
    }

    public function buyInfoAddBundleOptions(stdClass $_product) {
        $buyInfo = array();

        $buyInfo['product']         = $_product->product_id;
        $buyInfo['related_product'] = '';

        $buyInfo['bundle_option']     = array();
        $buyInfo['bundle_option_qty'] = array();

        foreach($_product->bundle_option as $bundle) {

            $optionType = $bundle->type;
            $optionId   = (int)$bundle->id;
            $selections = $bundle->selections;

            if(is_array($selections) and !empty($selections)) {
                foreach($selections as $_sel) {

                    if(isset($_sel->selected)) {
                        if(1 === ((int)$_sel->selected)) {

                            $selectedId = (int)$_sel->id;

                            if($this->isBundleItemOptionMultiSelect($optionType)) {
                                $buyInfo['bundle_option'][$optionId][] = $selectedId;
                            }
                            else {
                                if($this->isBundleItemOptionSingleSelect($optionType)) {
                                    $buyInfo['bundle_option'][$optionId]     = $selectedId;
                                    $buyInfo['bundle_option_qty'][$optionId] = ($_sel->qty * 1);
                                }
                            }

                        }
                    }
                }
            }
        }

        return $buyInfo;
    }

    public function isBundleItemOptionMultiSelect($optionType) {
        return ($optionType == 'multi' or $optionType == 'checkbox');
    }

    public function isBundleItemOptionSingleSelect($optionType) {
        return ($optionType == 'radio' or $optionType == 'select');
    }

    private function _applyCustomPrice($quoteItem, $price) {

        //Cannot apply custom price on dynamic bundle, Magento does not allow it.

        if($quoteItem->getParentItem()) {
            $quoteItem->getParentItem()->setCustomPrice($price);
            $quoteItem->getParentItem()->setOriginalCustomPrice($price);
            $quoteItem->getParentItem()->setBaseRowTotal($price);
        }
        else {
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->setBaseRowTotal($price);
        }
    }

    private function _getCustomerAddress($addressId) {
        $address = Mage::getModel('customer/address')->load((int)$addressId);
        if (is_null($address->getId())) {
            return null;
        }

        $address->explodeStreetAddress();
        if ($address->getRegionId()) {
            $address->setRegion($address->getRegionId());
        }
        return $address;
    }

    private function _getAddress($data, $email = "") {

        $id = (int)$data->customer_address_id;

        $street = array($data->street, (string)$data->street1);
        $street = implode("\n", $street);

        $address = array(
            //'customer_address_id' => $id,
            'firstname'  => $data->firstname,
            'lastname'   => $data->lastname,
            'email'      => $email,
            'is_active'  => 1,
            'street'     => $street,
            'street1'    => (string)$data->street,
            'street2'    => (string)$data->street2,
            'city'       => $data->city,
            'region_id'  => $data->region_id,
            'region'     => $data->region,
            'company'    => $data->company,
            'postcode'   => $data->postcode,
            'country_id' => $data->country_id,
            'telephone'  => $data->telephone,
        );

        if($id) {
            $_address = $this->_getCustomerAddress($id);
            if($_address) {
                $address = $_address->getData();
            }
        }

        return $address;
    }

    /**
     * Create new customer if the one provided does not exist.
     *
     * @param  string $data JSON data
     * @return void
     */
    private function _involveNewCustomer($data) {

        $email = (string)$data->customer->email;

        $this->getQuote()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);

        /* @see Mage_Checkout_Model_Type_Onepage::_validateCustomerData */
        /* @var $customerForm Mage_Customer_Model_Form */

        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $customer  = $this->customerExists($email, $websiteId);

        if(false === $customer) {

            $password     = substr(uniqid(), 0, 8);
            $customer     = Mage::helper('bakerloo_restful')->createCustomer($websiteId, $data, $password);
            $passwordHash = $customer->hashPassword($password);

            $addAddress = true;

            //Billing Address
            $address  = $this->_getAddress($data->customer->billing_address, $data->customer->email);

            //Check that the address provided is not the store's, if thats the case, ignore it.
            $storeAddress = Mage::helper('bakerloo_restful')->getStoreAddress(Mage::app()->getStore()->getId());
            if(is_array($storeAddress) and !empty($storeAddress)) {
                $eqPostcode  = ($storeAddress['postal_code'] == $address['postcode']);
                $eqCountry   = ($storeAddress['country'] == $address['country_id']);
                $eqTelephone = ($storeAddress['telephone'] == $address['telephone']);
                $eqRegion    = ($storeAddress['region_id'] == $address['region_id']);

                $addAddress = !($eqPostcode and $eqCountry and $eqTelephone and $eqRegion);
            }

            if($addAddress) {
                $newAddress = Mage::getModel('customer/address');
                $newAddress->addData($address);
                $newAddress->setId(null)
                    ->setIsDefaultBilling(true)
                    ->setIsDefaultShipping(true);
                $customer->addAddress($newAddress);

                $addressErrors = $newAddress->validate();
                if (is_array($addressErrors)) {
                    Mage::throwException(implode("\n", $addressErrors));
                }
            }

            //@TODO: Check this, Magento should save customer when checkout method is REGISTER
            //its not doing it so we call save() manually
            $customer->save();

            $this->getQuote()->setPasswordHash($passwordHash);
            $this->getQuote()->setCustomerGroupId($customer->getGroupId());
            $this->getQuote()->setCustomerIsGuest(false);

            // copy customer data to quote
            Mage::helper('core')->copyFieldset('customer_account', 'to_quote', $customer, $this->getQuote());

        }

    }

    /**
     * Check if customer email exists
     *
     * @param string $email
     * @param int $websiteId
     * @return false|Mage_Customer_Model_Customer
     */
    public function customerExists($email, $websiteId = null) {
        $customer = Mage::getModel('customer/customer');
        if ($websiteId != null) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    public function getQuote() {
        return $this->_quote;
    }

    public function setQuote($aQuote) {
        $this->_quote = $aQuote;
    }

    /**
     * Put notification on admin panel.
     *
     * @param  array  $notification
     * @return void
     */
    public function notifyAdmin(array $notification) {
        if (!empty($notification)) {
            Mage::getModel('adminnotification/inbox')->parse(array($notification));
        }
    }

    public function getCartData($quote) {

        $cartData = array(
            'quote_currency_code'         => $quote->getQuoteCurrencyCode(),
            'grand_total'                 => $quote->getGrandTotal(),
            'base_grand_total'            => $quote->getBaseGrandTotal(),
            'sub_total'                   => $quote->getSubtotal(),
            'base_subtotal'               => $quote->getBaseSubtotal(),
            'subtotal_with_discount'      => $quote->getSubtotalWithDiscount(),
            'base_subtotal_with_discount' => $quote->getBaseSubtotalWithDiscount(),
            'items'                       => array(),
        );

        $childrenAux = array();

        foreach($quote->getItemsCollection(false) as $quoteItem) {

            if ($quoteItem->getParentItem()) {
                $parentId = $quoteItem->getParentItemId();
                if(array_key_exists($parentId, $childrenAux))
                    $childrenAux[$parentId]['discount_amount']+= $quoteItem->getDiscountAmount();
                else
                    $childrenAux[$parentId] = array('discount_amount'=>$quoteItem->getDiscountAmount());

            }

            $item = array(
                'item_id'                 => (int)$quoteItem->getId(),
                'parent_item_id'          => (int)$quoteItem->getParentItemId(),
                'sku'                     => $quoteItem->getSku(),
                'product_id'              => (int)$quoteItem->getProductId(),
                'qty'                     => ($quoteItem->getQty() * 1),
                'price'                   => $quoteItem->getPrice(),
                'price_incl_tax'          => $quoteItem->getPriceInclTax(),
                'base_price_incl_tax'     => (float)$quoteItem->getBasePriceInclTax(),
                'row_total'               => $quoteItem->getRowTotal(),
                'row_total_with_discount' => $quoteItem->getRowTotalAfterRedemptions() ? (float)$quoteItem->getRowTotalAfterRedemptions() : (float)$quoteItem->getRowTotalWithDiscount(),
                'row_total_incl_tax'      => $quoteItem->getRowTotalAfterRedemptionsInclTax() ? (float)$quoteItem->getRowTotalAfterRedemptionsInclTax() : (float)$quoteItem->getRowTotalInclTax(),
                'base_row_total'          => $quoteItem->getBaseRowTotal(),
                'custom_price'            => (float)$quoteItem->getCustomPrice(),
                'discount_amount'         => $quoteItem->getRewardsCatalogDiscount() ? (float)$quoteItem->getRewardsCatalogDiscount() : $quoteItem->getDiscountAmount(),
                'tax_amount'              => (float)$quoteItem->getTaxAmount(),
            );

            $cartData['items'][$quoteItem->getId()] = $item;
        }

        if(!empty($childrenAux)) {
            foreach($childrenAux as $itemId => $iData) {
                if(array_key_exists($itemId, $cartData['items'])) {
                    foreach($iData as $key => $value) {
                        if($value)
                            $cartData['items'][$itemId][$key] = $value;
                    }
                }

            }
        }

        $cartData['items'] = array_values($cartData['items']);

        return $cartData;
    }

    /**
     * Check if the customer associated to an order is guest.
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function customerInOrderIsGuest(Mage_Sales_Model_Order $order) {
        return ((int)$order->getCustomerIsGuest() === 1);
    }


    /**
     * Check if the customer associated to an order is guest
     * or is the default customer from POS device.
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function customerInOrderIsGuestOrDefault(Mage_Sales_Model_Order $order){
        $isGuest = $this->customerInOrderIsGuest($order);
        $isDefault = 0;

        $posOrder = Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');
        if($posOrder->getId())
            $isDefault = (int)$posOrder->getUsesDefaultCustomer();

        return ($isGuest or $isDefault);
    }

}