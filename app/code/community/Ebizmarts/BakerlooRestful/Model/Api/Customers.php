<?php

class Ebizmarts_BakerlooRestful_Model_Api_Customers extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = "customer/customer";

    protected $_orJoinType = 'left';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_customer';

    public function getCollection(){
        return $this->_collection;
    }
    public function setCollection(){
        $this->_collection = $this->_getCollection();
    }


    public function checkPostPermissions() {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/customers/create'));
    }

    public function checkPutPermissions() {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/customers/update'));
    }

    /**
     * @param $store
     * @return string
     */
    public function getSearchByOnlineConfig($store) {
        return Mage::helper('bakerloo_restful')->config('customer/search_by_online', $store);
    }

    /**
     * Applying array of filters to collection
     *
     * @param array $filters
     * @param bool $useOR
     */
    public function _applyFilters($filters, $useOR = false) {

        //Search only performed on DEFAULT shipping and billing addresses.
        if(!empty($filters)) {

            $totalFilters = count($filters);
            for($i=0; $i < $totalFilters; $i++) {
                list($attributeCode, $condition, $value) = explode($this->_querySep, $filters[$i]);

                if(false !== strstr($attributeCode, 'address_')) {

                    $_attributeCode = str_replace('address_', '', $attributeCode);
                    $billingFilterAttributeCode  = 'billing_' . $_attributeCode;
                    $shippingFilterAttributeCode = 'shipping_' . $_attributeCode;
                    $this->_collection
                        ->joinAttribute($billingFilterAttributeCode, 'customer_address/' . $_attributeCode, 'default_billing', null, 'left')
                        ->joinAttribute($shippingFilterAttributeCode, 'customer_address/' . $_attributeCode, 'default_shipping', null, 'left');

                    unset($filters[$i]);
                    array_push($filters, "{$billingFilterAttributeCode},{$condition},{$value}");
                    array_push($filters, "{$shippingFilterAttributeCode},{$condition},{$value}");
                }
            }

        }

        parent::_applyFilters($filters, true);
    }

    protected function _getCollection() {

        Mage::app()->setCurrentStore($this->getStoreId());

        $collection = Mage::getResourceModel('customer/customer_collection')
                        ->addNameToSelect()
                        ->addAttributeToSelect('bakerloo_payment_methods');

        $shouldFilter = (int)Mage::helper("bakerloo_restful")->config("general/filter_customers", $this->getStoreId());

        if($shouldFilter === 1) {
            $collection->addAttributeToFilter(
                            array(
                                array('attribute'=> 'website_id','eq' => (int)Mage::app()->getStore()->getWebsiteId()),
                                array('attribute'=> 'website_id','eq' => 0), //Admin
                            )
                        );
        }

        $h = Mage::helper('bakerloo_restful');

        //Additional attributes on collection
        $additionalAttributes = (string) $h->config('customer/additional_attributes', $this->getStoreId());
        if(!empty($additionalAttributes))
            $additionalAttributes = explode(',', $additionalAttributes);
        else
            $additionalAttributes = array();

        $editableAttributes = $this->_editableAttributesConfig();

        $attributes = array_unique( array_merge($additionalAttributes, $editableAttributes) );

        if(is_array($attributes) && !empty($attributes)){
            foreach($attributes as $attributecode) {
                $collection->addAttributeToSelect($attributecode);
            }
        }

        return $collection;
    }

    public function _createDataObject($id = null, $data = null) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $result = array();

        if(is_null($data))
            $customer = Mage::getModel($this->_model)->load($id);
        else
            $customer = $data;

        if($customer->getId()) {

            $h = Mage::helper('bakerloo_restful');

            $websiteId = Mage::app()->getStore($this->getStoreId())->getWebsiteId();

            $websiteBaseCurrencyCode = Mage::app()->getWebsite($websiteId)->getBaseCurrencyCode();

            $result['customer_id']              = (int) $customer->getId();
            $result['prefix']                   = $customer->getPrefix();
            $result['firstname']                = $customer->getFirstname();
            $result['lastname']                 = $customer->getLastname();
            $result['email']                    = $customer->getEmail();
            $result['website_id']               = (int) $customer->getWebsiteId();
            $result['group_id']                 = (int) $customer->getGroupId();
            $result['bakerloo_payment_methods'] = (string)$customer->getBakerlooPaymentMethods();
            $result['subscribed_to_newsletter'] = (Mage::getModel('newsletter/subscriber')->loadByCustomer($customer)->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);

            if("0000-00-00 00:00:00" == $customer->getUpdatedAt()) {
                $result['updated_at'] = Mage::getModel('core/date')->gmtDate();
            }
            else {
                $result['updated_at'] = $customer->getUpdatedAt();
            }

            //Lifetime sales value in base currency
            $result ['lifetime_sales'] = $this->getLifetimeSales($customer);

            //Additional attributes
            $additionalAttributesConfig      = (string) Mage::helper('bakerloo_restful')->config('customer/additional_attributes', $this->getStoreId());
            $result['additional_attributes'] = $h->customerAttributesValues($customer, $additionalAttributesConfig);

            //Editable attributes
            $editableAttributesConfig      = (string) Mage::helper('bakerloo_restful')->config('customer/editable_attributes', $this->getStoreId());
            $result['editable_attributes'] = $h->customerAttributesValues($customer, $editableAttributesConfig);

            //Loyalty
            $loyaltyOk = Mage::helper('bakerloo_loyalty')->canUse();
            if($loyaltyOk !== FALSE) {

                $loyalty = Mage::getModel('bakerloo_restful/integrationDispatcher',
                    array('integration_type' => 'loyalty', 'customer' => $customer, 'website_id' => $websiteId, 'store_id' => $this->getStoreId()));

                if($loyalty->isEnabled()) {
                    $result['reward_points_balance']                = (float)$loyalty->getPointsBalance();
                    $result['reward_points_minumum_to_redeem']      = (float)$loyalty->getMinumumToRedeem();
                    $result['reward_points_amount']                 = (float)$loyalty->getCurrencyAmount();
                    $result['reward_points_website_currency_code']  = (string)$loyalty->getWebsiteBaseCurrencyCode();
                }

            }
            //Loyalty

            //Customer Store Credit
            $storeCreditExtension = Mage::helper('bakerloo_restful')->getStoreCreditExtension();

            if($storeCreditExtension == 'Enterprise_CustomerBalance') {

                $storeCreditPaymentMethod        = Mage::helper('payment')->getMethodInstance('bakerloo_storecredit');
                $storeCreditPaymentMethodEnabled = false;

                if($storeCreditPaymentMethod)
                    $storeCreditPaymentMethodEnabled = (int)$storeCreditPaymentMethod->getConfigData('active', $this->getStoreId());

                if($storeCreditPaymentMethodEnabled) {
                    $credit = Mage::getModel('enterprise_customerbalance/balance')
                        ->setCustomerId($result['customer_id'])
                        ->setWebsiteId($websiteId)
                        ->loadByCustomer();

                    $result['store_credit_amount']              = $credit->getAmount();
                    $result['store_credit_base_currency_code']  = $websiteBaseCurrencyCode;
                }

            }


            elseif($storeCreditExtension == 'Magestore_Customercredit') {

                $magestoreCreditPaymentMethod        = Mage::helper('payment')->getMethodInstance('bakerloo_magestorecredit');
                $magestoreCreditPaymentMethodEnabled = false;

                if($magestoreCreditPaymentMethod)
                    $magestoreCreditPaymentMethodEnabled = (int)$magestoreCreditPaymentMethod->getConfigData('active', $this->getStoreId());

                if($magestoreCreditPaymentMethodEnabled) {
                    $result['store_credit_amount']              = (float)$customer->getCreditValue();
                    $result['store_credit_base_currency_code']  = $websiteBaseCurrencyCode;
                }

            }
            //Customer Store Credit

            //Addresses
            $result['address'] = array();

            $addresses = $customer->getAddressesCollection();
            if($addresses->getSize()) {

                $defaultBillingId  = (int)$customer->getDefaultBilling();
                $defaultShippingId = (int)$customer->getDefaultShipping();

                foreach($addresses as $_address) {

                    $id = (int)$_address->getId();

                    $addr = array(
                        "customer_address_id" => $id,
                        "prefix"              => $_address->getPrefix(),
                        "firstname"           => $_address->getFirstname(),
                        "lastname"            => $_address->getLastname(),
                        "country_id"          => $_address->getCountryId(),
                        "city"                => $_address->getCity(),
                        "street"              => $_address->getStreet1(),
                        "street2"             => $_address->getStreet2(),
                        "region_id"           => $_address->getRegionId(),
                        "region"              => $_address->getRegion(),
                        "postcode"            => $_address->getPostcode(),
                        "telephone"           => $_address->getTelephone(),
                        "fax"                 => $_address->getFax(),
                        "company"             => $_address->getCompany(),
                        "is_shipping_address" => ($defaultShippingId == $id) ? 1 : 0,
                        "is_billing_address"  => ($defaultBillingId == $id) ? 1 : 0
                      );

                    $result['address'] []= $addr;
                }

            }

            $result ['wishlist'] = $this->_getMyWishlist($customer->getId());

        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $this->returnDataObject($result);

    }


    protected function _getMyWishlist($customerId) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $wishlistItems = array();

        $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, false);

        if ( !Mage::helper('wishlist')->isAllow() or !$wishlist->getId() or ((int)$wishlist->getCustomerId() != (int)$customerId) ) {
            return $wishlistItems;
        }

        if($wishlist->getItemsCount()) {
            Mage::helper('bakerloo_restful/pages')->disableFlatCatalogAndCategory($this->getStoreId());

            $collection = $wishlist->getItemCollection();
            $collection->setInStockFilter(true)->setOrder('added_at', 'ASC');



            foreach($collection as $_item) {

                $_itemWishlist = array(
                    'added_at'    => $_item->getAddedAt(),
                    'description' => is_null($_item->getDescription()) ? "" : $_item->getDescription(),
                    'product_id'  => (int)$_item->getProductId(),
                    'qty'         => ($_item->getQty() * 1),
                    'store_id'    => (int)$_item->getStoreId(),
                );

                array_push($wishlistItems, $_itemWishlist);

            }

        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $wishlistItems;

    }


    /**
     * Returns formatted lifetime sales value, eg: "£2,417.34"
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return array
     */
    public function getLifetimeSales(Mage_Customer_Model_Customer $customer) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $sales = Mage::getResourceModel('sales/sale_collection')
            ->setCustomerFilter($customer)
            ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
            ->load();

        $baseCurrencyCode = Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        $baseCurrency = Mage::getModel('directory/currency')
                        ->load($baseCurrencyCode);

        $baseAmount = $sales->getTotals()->getBaseLifetime();

        Varien_Profiler::stop('POS::' . __METHOD__);

        return array(
                        'currency'         => $baseCurrencyCode,
                        'amount'           => $baseAmount,
                        'formatted_amount' => $baseCurrency->format($baseAmount, array(), false),
        );
    }

    /**
     * Create customer with addresses in Magento.
     *
     * @return $this|array
     */
    public function post() {

        parent::post();

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        $_customer = $data->customer;
        $email     = (string)$_customer->email;

        $websiteId       = Mage::app()->getStore()->getWebsiteId();
        $customerExists  = Mage::helper('bakerloo_restful/sales')->customerExists($email, $websiteId);

        if($customerExists === false) {

            $password     = substr(uniqid(), 0, 8);
            $customer     = $this->helper('bakerloo_restful')->createCustomer($websiteId, $data, $password);

        }
        else {
            Mage::throwException(Mage::helper('bakerloo_restful')->__("Customer already exists."));
        }

        $newCustomer       = $this->_createDataObject((int)$customer->getId());
        $newCustomer['id'] = (int)$customer->getId();

        return $newCustomer;
    }

    /**
     * Process customer update.
     *
     * @return $this|array
     */
    public function put() {

        parent::put();

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        $_customer = $data->customer;
        $email     = (string)$_customer->email;

        $websiteId = isset($_customer->website_id) ? (string)$_customer->website_id : Mage::app()->getStore()->getWebsiteId();
        $customer  = Mage::helper('bakerloo_restful/sales')->customerExists($email, $websiteId);

        if($customer !== false) {

            //Edit customer address
            if(isset($_customer->address) && is_array($_customer->address) && !empty($_customer->address)) {

                foreach($_customer->address as $address) {
                    //check if address is new
                    $addressId = isset($address->customer_address_id) ? (int)$address->customer_address_id : 0;
                    $dbAddress = Mage::getModel('customer/address')->load($addressId);

                    if(!$dbAddress->getId())
                        //if new address, add
                        $this->_importAddressToCustomer($address, $customer);
                    else
                        //else update existing
                        $this->_updateExistingAddress($address, $dbAddress);
                }
            }

            //Edit Firstname and Lastname
            if(isset($_customer->firstname) and !empty($_customer->firstname))
                $customer->setFirstname($_customer->firstname);

            if(isset($_customer->lastname) and !empty($_customer->lastname))
                $customer->setLastname($_customer->lastname);

            if(isset($_customer->group_id))
                $customer->setGroupId($_customer->group_id);

            //Update attributes.
            if(isset($_customer->editable_attributes) and is_array($_customer->editable_attributes) and !empty($_customer->editable_attributes)) {
                $this->_updateAttributes($customer, $_customer->editable_attributes);
            }

            $customer->save();

        }
        else {
            Mage::throwException(Mage::helper('bakerloo_restful')->__("Customer does not exist."));
        }

        $updateCustomerEmail = isset($_customer->new_email);

        if($updateCustomerEmail && !empty($_customer->new_email)) {

            $newEmail = (string)$_customer->new_email;

            $customer->setEmail( $newEmail )
                ->save();
        }

        return $this->_createDataObject($customer->getId());

    }

    private function _updateAttributes($customer, $attributes){
        $config = $this->_getEditableAttributeConfig();
        foreach($attributes as $attr){
            if(in_array($attr->name, $config))
                $customer->setData($attr->name, $attr->value);
        }
        $customer->save();
    }

    private function _getEditableAttributeConfig(){
        $config = $this->editableAttributesConfig();

        $configKeys = array();
        foreach($config as $configOption){
            $configKeys[] = $configOption['name'];
        }

        return $configKeys;
    }

    public function addStorecredit() {

        $h = Mage::helper('bakerloo_restful');

        if(!Mage::helper('core')->isModuleEnabled('Enterprise_CustomerBalance') or !Mage::helper('core')->isModuleOutputEnabled('Enterprise_CustomerBalance'))
            Mage::throwException($h->__('Cannot add credit to customer.'));

        $data = $this->getJsonPayload();
        $customerId = $data->customer_id;

        $customer = Mage::getModel($this->_model)->load($customerId);
        if(!$customer->getId())
            Mage::throwException($h->__("Customer does not exist."));

        $websiteId = Mage::app()->getStore($this->getStoreId())->getWebsiteId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($data->order_id);
        $creditmemo = Mage::getModel('sales/order_creditmemo')->load($data->creditmemo_id);

        $refundAmt = isset($data->amount) ? $data->amount : $creditmemo->getGrandTotal();
        if($refundAmt > $creditmemo->getGrandTotal())
            $refundAmt = $creditmemo->getGrandTotal();

        
        Mage::getModel('enterprise_customerbalance/balance')
            ->setCustomer($customer)
            ->setWebsiteId($websiteId)
            ->setAmountDelta($refundAmt)
            ->setOrder($order)
            ->setCreditMemo($creditmemo)
            ->setHistoryAction(Enterprise_CustomerBalance_Model_Balance_History::ACTION_REFUNDED)
            ->save();

        return $this->_createDataObject($customerId, null);
    }

    /**
     * Retrieve DELETED customers.
     *
     * @return Collection data.
     */
    public function trashed() {
        $this->checkGetPermissions();

        $trash = Mage::getModel('bakerloo_restful/customertrash')
            ->getCollection();

        $since = $this->_getQueryParameter('since');
        if(!is_null($since)) {
            $trash->addFieldToFilter("updated_at", array("gt" => $since));
        }

        $items = $trash->getData();

        return $this->_getCollectionPageObject($items, 1, null, null, count($items));
    }

    /**
     * Return Customer editable attributes configuration.
     */
    public function editableAttributesConfig() {
        $editableAttributes = $this->_editableAttributesConfig();

        $config = array();

        if(!empty($editableAttributes)) {

            $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');

            $ca = Mage::getModel('customer/attribute');

            foreach($editableAttributes as $attr) {

                $attribute = $ca->loadByCode($entityType, $attr);

                $config []= array(
                    'name'  => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                    'type'  => $attribute->getFrontendInput(),
                );


                $ca->unsetOldData();
                $ca->unsetData();
            }

        }

        return $config;
    }

    protected function _editableAttributesConfig() {
        $editableAttributes = (string) Mage::helper('bakerloo_restful')->config('customer/editable_attributes', $this->getStoreId());
        if(!empty($editableAttributes))
            return explode(',', $editableAttributes);
        else
            return array();
    }

    protected function _importAddressToCustomer($address, $customer) {

        $_address = array (
            'prefix'     => isset($address->prefix) ? $address->prefix : '',
            'firstname'  => $address->firstname,
            'lastname'   => $address->lastname,
            'email'      => $customer->getEmail(),
            'is_active'  => 1,
            'city'       => $address->city,
            'region_id'  => $address->region_id,
            'region'     => $address->region,
            'postcode'   => $address->postcode,
            'country_id' => $address->country_id,
            'telephone'  => $address->telephone,
        );


        if(isset($address->company))
            $_address['company'] = $address->company;


        $newAddress = Mage::getModel('customer/address');
        $newAddress->addData($_address);
        $newAddress->setStreet(array($address->street, $address->street1));
        $newAddress->setId(null)
            ->setIsDefaultBilling( ((int)$address->is_billing_address) )
            ->setIsDefaultShipping( ((int)$address->is_shipping_address) );

        $addressValidation = $newAddress->validate();
        if(true === $addressValidation)
            $newAddress->setCustomer($customer)->save();
        else
            Mage::throwException(Mage::helper('sales')->__('Please check address information. %s', implode(' ', $addressValidation)));
    }

    /**
     * @param stdClass $addressData
     * @param Mage_Customer_Model_Address $addressObject
     * @throws Exception
     */
    protected function _updateExistingAddress(stdClass $addressData, Mage_Customer_Model_Address $addressObject){
        $addressObject->addData((array)$addressData);
        $addressObject->setStreet(array($addressData->street, $addressData->street2));

        $addressObject->save();
    }

    /**
     * Auth customer with provided email/password combination.
     *
     * PUT
     */
    public function authenticate() {

        if(!$this->getRequest()->isPut()) {
            return null;
        }

        $data = $this->getJsonPayload();

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        if ($customer->authenticate($data->username, $data->password)) {
            $customer = $customer->loadByEmail($data->username);
            return $this->_createDataObject(null, $customer);
        }
        else {
            Mage::throwException('Invalid login.');
        }

    }

    /**
     * Get the active quote for a customer in a store
     *
     * @return Mage_Sales_Model_Quote
     * @throws Mage_Core_Exception
     *
     */
    public function getActiveQuote(){
        if(!$this->getStoreId())
            Mage::throwException('Please provide a Store ID.');
        Mage::app()->setCurrentStore($this->getStoreId());

        //get requested customer identification
        $customerId = Mage::app()->getRequest()->getParam('id');
        $customerEmail = Mage::app()->getRequest()->getParam('email');

        //get active quote for customer
        if(isset($customerId))
            $activeQuote = $this->_getActiveQuoteById($customerId);
        elseif(isset($customerEmail))
            $activeQuote = $this->_getActiveQuoteByEmail($customerEmail);
        else
            Mage::throwException('No customer provided. ');

        if(is_null($activeQuote))
            return 'There are no active quotes for this customer. ';

        return $activeQuote->getData();
    }

    protected function _getActiveQuoteById($customerId){
        $activeQuotes = $this->_getActiveQuotesById((int)$customerId);

        if(empty($activeQuotes))
            return null;

        $keys = array_keys($activeQuotes);
        return $activeQuotes[$keys[0]];
    }

    protected function _getActiveQuoteByEmail($customerEmail){
        $activeQuotes = $this->_getActiveQuotesByEmail($customerEmail);

        if(empty($activeQuotes))
            return null;

        $keys = array_keys($activeQuotes);
        return $activeQuotes[$keys[0]];
    }

    protected function _getActiveQuotesById($customerId){
        return Mage::getModel('sales/quote')
            ->getCollection()
            ->addFieldToFilter('store_id', array('store_id' => $this->getStoreId()))
            ->addFieldToFilter('is_active', array('is_active' => 1))
            ->addFieldToFilter('customer_id', array('customer_id' => $customerId))
            ->setOrder('created_at', 'desc')
            ->getItems();
    }

    protected function _getActiveQuotesByEmail($customerEmail){
        return Mage::getModel('sales/quote')
            ->getCollection()
            ->addFieldToFilter('store_id', array('store_id' => $this->getStoreId()))
            ->addFieldToFilter('is_active', array('is_active' => 1))
            ->addFieldToFilter('customer_email', array('customer_email' => $customerEmail))
            ->setOrder('created_at', 'desc')
            ->getItems();
    }

    public function deactivateQuotes($quoteItems){
        foreach($quoteItems as $item){
            $item->setIsActive(0)
                ->save();
        }
    }

    public function addToCart(){
        if(!$this->getStoreId())
            Mage::throwException('Please provide a store ID.');
        Mage::app()->setCurrentStore($this->getStoreId());

        $customerId = $this->_getQueryParameter('id');
        $customer = Mage::getModel($this->_model)->load($customerId);

        if(!$customer->getId())
            Mage::throwException(Mage::helper('bakerloo_restful')->__('Cannot add product to cart. Please specify a customer ID.'));

        //get the customer's active quote
        $activeQuote = Mage::getModel('sales/quote')
            ->getCollection()
            ->addFieldToFilter('store_id', array('eq' => $this->getStoreId()))
            ->addFieldToFilter('is_active', array('eq' => 1))
            ->addFieldToFilter('customer_id', array('eq' => $customerId))
            ->setOrder('created_at', 'desc')
            ->getFirstItem();

        if(!$activeQuote->getId())
            $activeQuote->setCustomer($customer)
                ->setIsActive(true)
                ->setStoreId($this->getStoreId())
                ->save();

        //add products to quote
        $data = $this->getJsonPayload();
        $products = $data->products;

        $h = Mage::helper('bakerloo_restful/sales');
        $h->setQuote($activeQuote);
        $h->_addProductsToQuote($products);

        $activeQuote->collectTotals()->save();

        $result = array(
            "quote_id" => (int)$activeQuote->getId(),
            "store_id" => (int)$activeQuote->getStoreId(),
            "created_at" => $activeQuote->getCreatedAt(),
            "updated_at" => $activeQuote->getUpdatedAt(),
            "reserved_order_id" => $activeQuote->getReservedOrderId(),
            "customer_id" => $activeQuote->getCustomerId(),
            "customer_email" => $activeQuote->getCustomerEmail(),
            "base_currency_code" => $activeQuote->getBaseCurrencyCode(),
            "base_grand_total" => $activeQuote->getBaseGrandTotal(),
            "base_subtotal" => $activeQuote->getBaseSubtotal(),
            "items" => array()
        );

        $items = $activeQuote->getAllItems() ? $activeQuote->getAllItems() : array();
        foreach($items as $_item){
            $result["items"][] = $_item->getData();
        }

        return $result;
    }


}