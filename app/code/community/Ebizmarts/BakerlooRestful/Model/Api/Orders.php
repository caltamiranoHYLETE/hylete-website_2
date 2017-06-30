<?php

class Ebizmarts_BakerlooRestful_Model_Api_Orders extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_order';

    public $defaultDir = "DESC";

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'order';

    protected $_model = "sales/order";
    protected $_filterUseOR = true;

    public function checkDeletePermissions() {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/orders/delete'));
    }

    public function checkPostPermissions() {
        //Validate permissions
        $this->checkPermission(array('bakerloo_api/login', 'bakerloo_api/orders/create'));
    }

    /**
     * Return order options.
     * @param $item
     * @return array
     */
    public function orderOptions($item) {
        $result = array();
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }

        $selections = array();
        foreach ($result as $option) {

            $_sel = array('label' => $option['label'], 'value' => '');

            if(!is_array($option['value']))
                $_sel['value'] = $option['value'];
            /*else
                //TODO*/


            array_push($selections, $_sel);
        }

        return $selections;
    }

    public function _createDataObject($id = null, $data = null) {
        $result = array();

        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel($this->_model)->load($id);

        if($order->getId()) {

            $orderItems = array();

            $childrenAux = array();

            foreach($order->getItemsCollection() as $item) {

                if($item->getParentItem()) {
                    $parentId = $item->getParentItemId();
                    if(array_key_exists($parentId, $childrenAux))
                        $childrenAux[$parentId]['discount'] += $item->getDiscountAmount();
                    else
                        $childrenAux[$parentId] = array('discount' => $item->getDiscountAmount());

                    continue;
                }

                $orderItems[$item->getId()] = array(
                    'name'           => $item->getName(),
                    'sku'            => $item->getSku(),
                    'product_id'     => (int)$item->getProductId(),
                    'item_id'        => (int)$item->getItemId(),
                    'product_type'   => $item->getProductType(),
                    'qty'            => ($item->getQtyOrdered() * 1),
                    'qty_invoiced'   => ($item->getQtyInvoiced() * 1),
                    'qty_shipped'    => ($item->getQtyShipped() * 1),
                    'qty_refunded'   => ($item->getQtyRefunded() * 1),
                    'qty_canceled'   => ($item->getQtyCanceled() * 1),
                    'price'          => (float)$item->getPrice(),
                    'tax_amount'     => (float)$item->getTaxAmount(),
                    'price_incl_tax' => (float)$item->getPriceInclTax(),
                    'tax_percent'    => (float)$item->getTaxPercent(),
                    'discount'       => (float)$item->getDiscountAmount(),
                    'options'        => $this->orderOptions($item)
                );

                $giftTypes = Mage::helper('bakerloo_gifting')->getSupportedTypes();
                if(array_key_exists($item->getProductType(), $giftTypes)) {

                    $orderItems[$item->getId()]['gift_card_options'] = Mage::getModel('bakerloo_restful/api_giftcards')->getOrderItemData($item);
                }
            }

            if(!empty($childrenAux)) {
                foreach ($childrenAux as $itemId => $iData) {
                    if (array_key_exists($itemId, $orderItems)) {
                        foreach ($iData as $key => $value) {
                            if ($value)
                                $orderItems[$itemId][$key] = $value;
                        }
                    }
                }
            }


            $shippingAddress = is_object($order->getShippingAddress()) ? $order->getShippingAddress() : new Varien_Object;
            $billingAddress  = is_object($order->getBillingAddress()) ? $order->getBillingAddress() : new Varien_Object;

            $posOrder = Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');

            $result += array(
                "pos_entity_id"        => (int)$posOrder->getId(),
                "entity_id"            => (int)$order->getId(),
                "status"               => $order->getStatusLabel(),
                "state"                => $order->getState(),
                "created_at"           => $order->getCreatedAt(),
                "updated_at"           => $order->getUpdatedAt(),
                "store_id"             => (int)$order->getStoreId(),
                "store_name"           => $order->getStoreName(),
                "customer_id"          => (int)$order->getCustomerId(),
                "base_subtotal"        => (float)$order->getBaseSubtotal(),
                "subtotal"             => (float)$order->getSubtotal(),
                "base_grand_total"     => (float)$order->getBaseGrandTotal(),
                "base_total_paid"      => (float)$order->getBaseTotalPaid(),
                "grand_total"          => (float)$order->getGrandTotal(),
                "total_paid"           => (float)$order->getTotalPaid(),
                "tax_amount"           => (float)$order->getTaxAmount(),
                "discount_amount"      => (float)$order->getDiscountAmount(),
                "coupon_code"          => (string)$order->getCouponCode(),
                "shipping_description" => (string)$order->getShippingDescription(),
                "shipping_amount"      => (float)$order->getShippingInclTax(),
                "shipping_amount_refunded" => (float)$order->getShippingRefunded() + (float)$order->getShippingTaxRefunded(),
                "increment_id"         => $order->getIncrementId(),
                "currency_rate"        => (float)$order->getBaseToOrderRate(),
                "base_currency_code"   => $order->getBaseCurrencyCode(),
                "order_currency_code"  => $order->getOrderCurrencyCode(),
                "customer_email"       => (string)$order->getCustomerEmail(),
                "customer_firstname"   => (string)$order->getCustomerFirstname(),
                "customer_lastname"    => (string)$order->getCustomerLastname(),
                "shipping_name"        => (string)$shippingAddress->getName(),
                "billing_name"         => (string)$billingAddress->getName(),
                "products"             => array_values($orderItems),
                "invoices"             => $this->_getAssociatedData($order->getId(), 'invoices'),
                "creditnotes"          => $this->_getAssociatedData($order->getId(), 'creditnotes'),
                "shipping_address"     => $this->_getOrderAddress($order, 'shipping'),
                "billing_address"      => $this->_getOrderAddress($order, 'billing'),
                "payment"              => $this->_getOrderPayments($order, $posOrder),
                "pos_order"            => $this->_getJsonPayload($posOrder)
            );
        }

        return $this->returnDataObject($result);
    }

    /**
     * Create order in Magento.
     *
     */
    public function post() {
        parent::post();
        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }
        Mage::app()->setCurrentStore($this->getStoreId());
        //Mage::log("Store: " . $this->getStoreId(), null, 'store.log', true);

        $data = $this->getJsonPayload();

        $order = new Varien_Object;
        //Save order data to local storage
        $posOrder = $this->_saveOrder(null, $order, $data, $this->getRequest()->getRawBody());

        $returnData = array(
            'order_id'     => null,
            'order_number' => null,
            'order_state'  => "",
            'order_status' => ""
        );
        try {
            $quote = Mage::helper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data);
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            $order = $service->getOrder();

            if($order->getId())
               $order = Mage::getModel('sales/order')->load($order->getId());

            if($order) {
                Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                    array('order' => $order, 'quote' => $quote));
            }
            Mage::dispatchEvent(
                    'checkout_submit_all_after',
                array('order' => $order, 'quote' => $quote)
            );

            if(isset($data->returns) && !empty($data->returns)){
                Mage::dispatchEvent('pos_order_has_returns', array('order_id' => $order->getIncrementId(), 'returned_items' => $this->getReturnDetails($data->returns)));
            }

            /* Check payment method is Layaway */
            if($data->payment->method == Ebizmarts_BakerlooPayment_Model_Layaway::CODE)
                Mage::dispatchEvent('pos_order_has_layaway_payment', array('order' => $order, 'payload' => $data, 'posorder' => $posOrder));

            if($order->getId() && isset($data->comments)) {
                $order->addStatusHistoryComment(nl2br($data->comments), false)
                    ->setIsVisibleOnFront(false)
                    ->setIsCustomerNotified(false)
                    ->save();
            }
            //Cancel order if its posted as canceled from device
            if(isset($data->order_state) && ((int)$data->order_state === 4)) {
                $order->cancel()
                    ->save();
            }
            //Report price override if custom price has been entered
            if(isset($data->discount) and $data->discount > 0)
                $this->reportPriceOverride($order, $data);

            //Invoice and ship
            if($order->getId()) {

                $invoiceConfig = (int)$order->getPayment()->getMethodInstance()->getConfigData("invoice");
                $shipmentConfig = (int)$order->getPayment()->getMethodInstance()->getConfigData("ship");

                if($order->getPayment()->getMethod() == 'free') {
                    $invoiceConfig = (int)Mage::getStoreConfig('payment/bakerloo_free/invoice', $this->getStore());
                    $shipmentConfig = (int)Mage::getStoreConfig('payment/bakerloo_free/ship', $this->getStore());
                }

                $transactionSave = Mage::getModel('core/resource_transaction');

                if($order->canInvoice() and $invoiceConfig) {

                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                    $invoice->setTransactionId(time());
                    $invoice->register();

                    //Do no send invoice email
                    $invoice->setEmailSent(false);
                    $invoice->getOrder()->setCustomerNoteNotify(false);

                    $transactionSave->addObject($invoice)
                        ->addObject($invoice->getOrder());

                }
                elseif($order->hasInvoices()) {
                    $invoice = $order->getInvoiceCollection()->getFirstItem();
                }

                if(!$order->getIsVirtual() and isset($invoice)) {

                    //If not Virtual, create shipment if indicated.
                    $createShipment = false;
                    $shippingMethod = explode('_', $order->getShippingMethod(), 3);

                    if (!isset($shippingMethod[2]))
                        $shipmentShip = $shipmentConfig;
                    else
                        $shipmentShip = (int)Mage::getStoreConfig('carriers/' . $shippingMethod[2] . '/ship');
                    if ((1 === $shipmentConfig) || ((2 === $shipmentConfig) && (1 === $shipmentShip)))
                        $createShipment = true;
                    if ($createShipment) {
                        $shipment = Mage::getModel('sales/service_order', $invoice->getOrder())
                            ->prepareShipment(array());
                        $shipment->register();
                        if ($shipment) {
                            $shipment->setEmailSent($invoice->getEmailSent());
                            $transactionSave->addObject($shipment)
                                ->addObject($shipment->getOrder());
                        }
                    }
                }

                //if(isset($invoice) or isset($shipment))
                $transactionSave->save();
            }

            $this->_saveOrder($posOrder->getId(), $order, $data);

            $returnData['order_id']     = (int)$order->getId();
            $returnData['order_number'] = $order->getIncrementId();
            $returnData['order_state']  = $order->getState();
            $returnData['order_status'] = $order->getStatusLabel();
            $returnData['order_data']   = $this->_createDataObject($order->getId());
            $posOrder
                ->setFailMessage('')
                ->save();
            //Inactivate quote.
            $service->getQuote()->save();
            //TODO: set quote as not active if the order fails. (->setIsActive(false))
        }
        catch(Exception $e) {
            Mage::logException($e);
            $posOrderId = (int)$posOrder->getId();
            $message = $e->getMessage();

            $returnData['order_id']      = $posOrderId;
            $returnData['order_number']  = $posOrderId;
            $returnData['order_state']   = "notsaved";
            $returnData['order_status']  = "notsaved";
            $returnData['error_message'] = $message;
            $posOrder->setFailMessage($message)
                ->save();

            Mage::helper('bakerloo_restful/sales')->notifyAdmin(array(
                'severity'      => Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL,
                'date_added'    => Mage::getModel('core/date')->date(),
                'title'         => Mage::helper('bakerloo_restful')->__("POS order number #%s failed.", $posOrderId),
                'description'   => Mage::helper('bakerloo_restful')->__($message),
                'url'           => null /*Mage::helper('adminhtml')->getUrl('adminhtml/bakerlooorders/', array('id' => $posOrderId))*/,
            ));
        }

        $this->_saveOrder($posOrder->getId(), $order, $data);

        //call observers after posOrder save
        if(isset($returnData['order_data']))
            $returnData['order_data'] = $this->returnDataObject($returnData['order_data']);


        return $returnData;
    }

    /**
     *
     */
    public function getReturnDetails($returnedProducts){
        $returnedProductDetails = array();

        foreach($returnedProducts as $prod){

            if(isset($prod->bundle_option) && !empty($prod->bundle_option)){
                $bundleQty = $prod->qty;

                $bundledProducts = $prod->bundle_option;
                foreach($bundledProducts as $bundledProd){
                    foreach($bundledProd->selections as $selectedProd){
                        if($selectedProd->selected == true) {
                            $productDetails = array(
                                'product_id' => $selectedProd->product_id,
                                'product_qty' => $selectedProd->qty * $bundleQty
                            );
                            $returnedProductDetails[] = $productDetails;
                        }
                    }
                }
            }
            elseif(isset($prod->type) && $prod->type == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) { // 'configurable'){
                $productDetails = array(
                    'product_id' => $prod->child_id,
                    'product_qty' => $prod->qty
                );
                $returnedProductDetails[] = $productDetails;
            }
            else{
                $productDetails = array(
                    'product_id' => $prod->product_id,
                    'product_qty' => $prod->qty
                );
                $returnedProductDetails[] = $productDetails;
            }
        }

        return $returnedProductDetails;
    }

    public function reportPriceOverride($order, $orderData){
        $totalBefore = $orderData->total_amount;
        $discount = $orderData->discount;
        $totalAfter = $totalBefore - $discount;

        //save discount to custom price table
        Mage::getModel('bakerloo_restful/customPrice')
            ->setId(null)
            ->setOrderId($order->getId())
            ->setOrderIncrementId($order->getIncrementId())
            ->setAdminUser($orderData->user)
            ->setStoreId($order->getStoreId())
            ->setTotalDiscount($discount)
            ->setGrandTotalBeforeDiscount($totalBefore)
            ->setGrandTotalAfterDiscount($totalAfter)
            ->save();

        //check email config and send
        $notifyFlag = Mage::helper('bakerloo_restful')->config('custom_discount_email/enabled', $this->getStoreId());

        if($notifyFlag) {
            $notifyPercent = (int)Mage::helper('bakerloo_restful')->config('custom_discount_email/minimum_percent', $this->getStoreId());

            $orderPercent = ($totalBefore != 0) ? $discount/$totalBefore * 100 : 0;
            if($orderPercent >= $notifyPercent)
                $this->sendPriceOverrideEmail($order, $discount);
        }

        return $this;
    }

    public function sendPriceOverrideEmail($order, $discount_amount){

        $template_id = 'bakerloorestful_pos_customprice_template';

        $emailTemplate = Mage::getModel('core/email_template');
        $emailTemplate->loadDefault($template_id);

        $admin_name = Mage::getStoreConfig('trans_email/ident_general/name');
        $admin_email = Mage::getStoreConfig('trans_email/ident_general/email');

        $discount = sprintf('%s %d', $order->getBaseCurrencyCode(), $discount_amount);
        $emailTemplate_vars = array(
            'order_id' => $order->getIncrementId(),
            'discount' => $discount
        );


        $emailTemplate->setSenderName($admin_name);
        $emailTemplate->setSenderEmail($admin_email);
        $emailTemplate->send($admin_email, null, $emailTemplate_vars);
    }

    /**
     * Cancel order
     */
    public function delete() {
        parent::delete();

        $orderId = $this->_getIdentifier();

        $order = Mage::getModel('sales/order')->load($orderId);

        if($order->getId()) {

            if ($order->canCancel()) {
                $order->cancel()
                    ->save();
            }
            else {
                Mage::throwException("Order can not be canceled.");
            }
        }
        else {
            Mage::throwException("Order does not exist.");
        }

        return array(
            'order_id'     => (int)$order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_state'  => $order->getState(),
            'order_status' => $order->getStatusLabel()
        );
    }

    protected function _getCollection() {
        return Mage::getResourceModel('sales/order_collection');
    }

    /*public function _beforePaginateCollection($collection, $page, $since) {

        parent::_beforePaginateCollection($collection, $page, $since);

        $filters = $this->_getQueryParameter('filters');
        if(!$since or empty($filters) or ($collection->getSize() > 0) )
            return $this;

//        $posOrder = Mage::getModel('bakerloo_restful/order')->getCollection()->addFieldToFilter('device_order_id', $filterValue)->getFirstItem();
//        if($posOrder->getId()) {
//            $this->_collection->getSelect()->joinLeft(
//                array('pos' => Mage::getSingleton('core/resource')->getTableName('bakerloo_restful/order')),
//                'main_table.entity_id = pos.order_id',
//                array()
//            )
//                ->where('pos.device_order_id = ?', $filterValue);
//        }

        return $this;
    }*/

    /**
     * Applying array of filters to collection
     *
     * @param array $filters
     * @param boolean $useOR
     */
    public function _applyFilters($filters, $useOR = false) {

        if(count($filters) == 1) {
            $filter = list($attributeCode, $condition, $value) = explode($this->_querySep, $filters[0]);

            //Value to filter by.
            $filterValue = $filter[2];

            if("increment_id" == $filter[0] && $filter[1] == 'eq') {

                $orderByIncrementId = Mage::getModel($this->_model)->loadByIncrementId($filterValue);
                if($orderByIncrementId->getId()) {
                    $this->_collection->getSelect()->where('main_table.increment_id = ?', $filterValue);
                }
                else
                    $this->_filterByDeviceOrderId($filterValue);

            }
            else {

                if("order_guid" == $filter[0] && $filter[1] == 'eq') {
                    $this->_collection->getSelect()->joinLeft(
                        array('pos' => $this->_posTableName()),
                        'main_table.entity_id = pos.order_id',
                        array()
                    )->where('pos.order_guid = ?', $filterValue);
                }
                else
                    parent::_applyFilters($filters, true);

            }
        }
        else {

            $magicSearch = false;

            foreach($filters as $_filterString) {
                $fl = explode($this->_querySep, $_filterString);

                //Try search by device_order_id
                if($fl[0] == 'increment_id') {
                    $collection = Mage::getModel('bakerloo_restful/order')->getCollection();
                    $collection->addFieldToFilter('device_order_id', array($fl[1] => $fl[2]));

                    if( ((int)$collection->getSize() > 0) )  {
                        $magicSearch = true;
                        $this->_filterByDeviceOrderId($fl[2]);
                    }
                }
            }

            if(!$magicSearch)
                parent::_applyFilters($filters, $this->_filterUseOR);

        }

    }

    protected function _posTableName() {
        return Mage::getSingleton('core/resource')->getTableName('bakerloo_restful/order');
    }

    private function _filterByDeviceOrderId($orderId) {
        $this->_collection->getSelect()->joinLeft(
            array('pos' => $this->_posTableName()),
            'main_table.entity_id = pos.order_id',
            array()
        )->where('pos.device_order_id LIKE ?', $orderId);
    }

    /**
     * Save order in local table POS > Orders.
     *
     * @param  int   $id      [description]
     * @param  Mage_Sales_Model_Order   $order   [description]
     * @param  stdClass $data    [description]
     * @param  string   $rawData [description]
     * @return Ebizmarts_BakerlooRestful_Model_Order            [description]
     */
    protected function _saveOrder($id = null, $order, stdClass $data, $rawData = null) {
//        Mage::log('Saving bakerloo order.');

        $_bakerlooOrder = Mage::getModel('bakerloo_restful/order');
        $headerId = (int)$this->_getRequestHeader('B-Order-Id');
        if($headerId) {
            $id = $headerId;
        }
        if(!is_null($id)) {
            $_bakerlooOrder->load($id);
        }
        else {
            //Check that order is not duplicate by order guid.
            if(isset($data->order_guid)) {
                $duplicate = Mage::getModel('bakerloo_restful/order')->load($data->order_guid, 'order_guid');
                if($duplicate->getId()) {
                    Mage::throwException("Duplicate POST for `{$data->order_guid}`.");
                }
            }
            //Store request headers in local table first time
            //so if it fails we can retry with all original data
            $requestHeaders = array();
            foreach(Mage::helper('bakerloo_restful')->allPossibleHeaders() as $_rqh) {
                $value = (string)$this->_getRequestHeader($_rqh);
                if(!empty($value)) {
                    $requestHeaders[$_rqh] = $value;
                }
            }
            $_bakerlooOrder->setJsonRequestHeaders(json_encode($requestHeaders));
        }
        //Save order in custom table
        $_bakerlooOrder
            ->setOrderIncrementId($order->getIncrementId())
            ->setOrderId($order->getId())
            ->setAdminUser($data->user)
            ->setLoginUser($this->getUsername())
            ->setLoginUserAuth($this->getUsernameAuth())
            ->setSalesperson((isset($data->salesperson) ? $data->salesperson : null))
            ->setRemoteIp(Mage::helper('core/http')->getRemoteAddr())
            ->setDeviceId($this->getDeviceId())
            ->setUserAgent($this->getUserAgent())
            ->setRequestUrl(Mage::helper('core/url')->getCurrentUrl()); //@TODO: Check this.

        if(!is_null($rawData)) {
//            Mage::log('Order has raw data. ');

            $_rawData = json_decode($rawData);
            if(isset($_rawData->payment->customer_signature)) {
                $_bakerlooOrder->setCustomerSignature($_rawData->payment->customer_signature);
                unset($_rawData->payment->customer_signature);
            }
            $_bakerlooOrder->setJsonPayload(json_encode($_rawData));
//            Mage::log('Json payload saved. ');
        }
        //Device Order ID
        if(isset($data->internal_id)) {
            $_bakerlooOrder->setDeviceOrderId($data->internal_id);
        }
        if(isset($data->order_guid)) {
            $_bakerlooOrder->setOrderGuid($data->order_guid);
        }
        if(isset($data->auth_user)) {
            $_bakerlooOrder->setAdminUserAuth($data->auth_user);
        }
        if(isset($data->customer->is_default_customer)){
            $usesDefault = !is_null($data->customer->is_default_customer) ? $data->customer->is_default_customer : 0;
            $_bakerlooOrder->setUsesDefaultCustomer($usesDefault);
        }
        if($this->getLatitude()) {
            $_bakerlooOrder->setLatitude($this->getLatitude());
        }
        if($this->getLongitude()) {
            $_bakerlooOrder->setLongitude($this->getLongitude());
        }
        //Store additional data.
        $additional = array(
            'store_id',
            'grand_total',
            'subtotal',
            'base_subtotal',
            'base_grand_total',
            'base_shipping_amount',
            'base_tax_amount',
            'base_to_global_rate',
            'base_to_order_rate',
            'base_currency_code',
            'tax_amount',
            'store_to_base_rate',
            'store_to_order_rate',
            'global_currency_code',
            'order_currency_code',
            'store_currency_code',
        );
        foreach($additional as $_attribute) {
            $_bakerlooOrder->setData($_attribute, $order->getData($_attribute));
        }
        $_bakerlooOrder->save();

//        Mage::log('Order saved. ');
        return $_bakerlooOrder;
    }

    /**
     * Given an order ID, send order email.
     *
     * @return array Email sending result
     */
    public function sendEmail() {

        //get data
        $orderId = (int)$this->_getQueryParameter('orderId');
        $customEmail = (string)$this->_getQueryParameter('email');
        $storeEmail = (string)Mage::app()->getStore()->getConfig('trans_email/ident_general/email');

        //Load order and check if exists.
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);

        if(!$order->getId()) {
            Mage::throwException("Order does not exist.");
        }

        Mage::app()->setCurrentStore($order->getStoreId());

        //send email if custom email is valid and different from store email
        $email = filter_var($customEmail, FILTER_VALIDATE_EMAIL) ? $customEmail : $order->getCustomerEmail();

        if($storeEmail != $email)
            $emailSent = $this->insertEmail($order, $email);
        else
            $emailSent = false;

        //return a jSon object with order data and email status
        $result = array(
            'order_id'     => (int)$order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_state'  => $order->getState(),
            'order_status' => $order->getStatusLabel(),
            'email_sent'   => $emailSent
        );
        return $result;

    }

    /**
     * @param $order
     */
    public function insertEmail(Mage_Sales_Model_Order $order, $customEmail = null){

        $inserted = false;

        if($this->getRequest()->isPost())
            $data = $this->getJsonPayload();
        else
            $data = new stdClass();

        //Add customer from email if email is valid and customer is new
        $customer = Mage::helper('bakerloo_restful/sales')->customerExists($customEmail, Mage::app()->getStore()->getWebsiteId());
        $createConfig = (int)Mage::helper('bakerloo_restful')->config('checkout/create_customer');
        $customerInOrderIsGuestOrDefault = Mage::helper('bakerloo_restful/sales')->customerInOrderIsGuestOrDefault($order);

        if ($customer === false and $createConfig) {
            $this->addCustomer($customEmail, $order, false);
        }
        elseif($customerInOrderIsGuestOrDefault && $customer->getId()) {
            $this->setCustomerToOrder($customer, $order);
            $bakerlooOrder = Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id');
            $bakerlooOrder->setUsesDefaultCustomer(0)->save();
        }

        //Register flag for workaround in Magento version 1.8 or lower.
        if(!Mage::registry('pos_send_email_to'))
            Mage::register('pos_send_email_to', $order->getCustomerEmail());

        $emailType = (string)Mage::helper('bakerloo_restful')->config('pos_receipt/receipts', $this->getStoreId());
        $subscribeToNewsletter = (bool)$this->_getQueryParameter('subscribe_to_newsletter');

        //Add incidence to bakerloo_email/unsent_emails table
        $unsentQueue = $this->insertUnsentEmail($order, $emailType, $customEmail);
        if($unsentQueue->getId()) {
            $inserted = true;

            //Add incidence to bakerloo_email/log table
            $queue = $this->logEmail($order, $emailType, $subscribeToNewsletter, null, $customEmail);

            //Save attachment if set
            if(isset($data->attachments) and is_array($data->attachments) and !empty($data->attachments)) {
                $receiptData = current($data->attachments);

                //Store image name in database.
                $unsentQueue->setAttachment($receiptData->name)->save();
                $queue->setAttachment($receiptData->name)->save();

                //Store receipt on disk.
                $receiptsStorage = Mage::helper('bakerloo_restful/cli')->getPathToDb($order->getStoreId(), 'receipts', false);
                $saved = file_put_contents($receiptsStorage . DS . $receiptData->name, base64_decode($receiptData->content));

                if($saved === false){
                    $error = sprintf("Receipt for order %d not saved. ", $order->getId());
                    $this->logEmail($order, $emailType, null, $error, $customEmail);
                }
            }
        }

        //Subscribe email to newsletter if indicated
        if($subscribeToNewsletter) {
            $this->subscribeToNewsletter($order->getCustomerEmail());
        }

        $order->save();

        return $inserted;
    }

    public function logEmail($order, $emailType, $newsletterSubscription = null, $error = null, $emailTo = null){
        $emailTo = is_null($emailTo) ? $order->getCustomerEmail() : $emailTo;

        $row = Mage::getModel('bakerloo_email/queue')
            ->setId(null)
            ->setOrderId($order->getId())
            ->setCustomerId($order->getCustomerId())
            ->setToEmail($emailTo)
            ->setEmailType($emailType)
            ->setSubscribeToNewsletter((int)$newsletterSubscription)
            ->save();

        if(isset($error)){
            $row->setEmailResult(false)
                ->setErrorMessage($error)
                ->save();
        }

        return $row;
    }

    public function insertUnsentEmail($order, $emailType, $customEmail = null){

        $emailTo = is_null($customEmail) ? $order->getCustomerEmail() : $customEmail;

        $rows = Mage::getModel('bakerloo_email/unsent')
            ->getCollection()
            ->addFieldToFilter('order_id', array('eq', $order->getId()))
            ->addFieldToFilter('to_email', array('eq', $emailTo));

        if($rows->count() == 0){
            $row = Mage::getModel('bakerloo_email/unsent')
                ->setId(null)
                ->setOrderId($order->getId())
                ->setCustomerId($order->getCustomerId())
                ->setToEmail($emailTo)
                ->setEmailType($emailType)
                ->save();
        }
        else{
            $row = $rows->getFirstItem();
            $row->setCustomerId($order->getCustomerId())
                ->setEmailType($emailType)
                ->save();
        }

        return $row;
    }

    public function subscribeToNewsletter($email){
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);

        $subscriberCollection = Mage::getModel('newsletter/subscriber')->getCollection()
            ->addFieldToFilter('subscriber_email', array('eq' => $email));
        $duplicateSubscriber = current($subscriberCollection->getItems());

        if($duplicateSubscriber !== false && !$duplicateSubscriber->getId()) {
            if ($customer->getId()) {
                $customer->setIsSubscribed(1);
                $customer->save();

                Mage::getModel('newsletter/subscriber')->subscribe($email);
                $subscribedCustomer = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                $subscribedCustomer->setCustomerId($customer->getId());
                $subscribedCustomer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $subscribedCustomer->save();
            }
        }
        else {
            Mage::getModel('newsletter/subscriber')->subscribe($email);
        }
    }

    /**
     * @param $email
     * @return mixed
     */
    public function customerExists($email){
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        return Mage::helper('bakerloo_restful/sales')->customerExists($email, $websiteId);
    }

    /**
     * @param $order
     * @param $newEmail
     * @return mixed
     */
    public function swapOrderEmail($order, $newEmail){
        $validCustomEmail = filter_var($newEmail, FILTER_VALIDATE_EMAIL);
        if($validCustomEmail) {
            $order->setCustomerEmail($newEmail)->save();
        }
    }

    /**
     * @param $email
     * @param $order
     * @param $changedCustomer
     *
     * @return bool
     *
     * Adds a customer to Magento customers from supplied email
     */
    public function addCustomer($email, Mage_Sales_Model_Order $order, $changedCustomer = false){
        $name = substr($email, 0, strpos($email, '@'));

        $customerData = new stdClass;
        $customerData->customer = new stdClass;
        $customerData->customer->group_id  = Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, Mage::app()->getStore()->getId());
        $customerData->customer->email     = $email;
        $customerData->customer->firstname = $name;
        $customerData->customer->lastname  = $name;

        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $newCustomer = Mage::helper('bakerloo_restful')->createCustomer($websiteId, $customerData);
        //@TODO: Add addresses if not equal to store.

        $customerInOrderIsGuestOrDefault = Mage::helper('bakerloo_restful/sales')->customerInOrderIsGuestOrDefault($order);

        //Associate customer to order.
        if($newCustomer->getId() and $customerInOrderIsGuestOrDefault) {
            $this->setCustomerToOrder($newCustomer, $order);
            Mage::getModel('bakerloo_restful/order')->load($order->getId(), 'order_id')->setUsesDefaultCustomer(0)->save();
            $changedCustomer = true;
            unset($currentEmail);

            //Register flag for workaround in Magento version 1.8 or lower.
            Mage::register('pos_send_email_to', $newCustomer);

        }

        return $changedCustomer;
    }

    /**
     * Search orders by POS order number.
     *
     * @return array|Varien_Object
     */
    public function searchByPosOrderId() {

        $id = (int)$this->_getQueryParameter('id');

        $collection = Mage::getModel('bakerloo_restful/order')->getCollection();
        $collection->addFieldToFilter('id', $id);

        $order = new Varien_Object;
        if($collection->getSize()) {
            $_order = $this->_createDataObject($collection->getFirstItem()->getOrderId());

            if(is_array($_order) and isset($_order['entity_id'])) {
                $order = $_order;
            }
        }

        return $order;

    }


    /**
     *
     */
    public function processUnsentEmails(){
        //check email sending enabled
        $enabled = Mage::getStoreConfig('bakerloorestful/order_emails/enabled', Mage::app()->getStore());

        if($enabled){
            $unsentQueue = Mage::getModel('bakerloo_email/unsent')->getCollection();

            foreach($unsentQueue as $unsentEmail) {
                $emailType = $unsentEmail->getEmailType();

                $orderId = (int)$unsentEmail->getOrderId();

                /* @var $order Mage_Sales_Model_Order */
                $order = Mage::getModel('sales/order')->load($orderId);
                if(!$order->getId())
                    continue;

                //swap order email if different from unsent email address
                $orderEmailAddress = $order->getCustomerEmail();
                $unsentEmailAddress = $unsentEmail->getToEmail();
                if (strcmp($unsentEmailAddress, $orderEmailAddress) != 0)
                    $this->swapOrderEmail($order, $unsentEmailAddress);

                $attachment = new stdClass();
                $attachment->name = $unsentEmail->getAttachment();

                $receiptsStorage = Mage::helper('bakerloo_restful/cli')->getPathToDb($order->getStoreId(), 'receipts', false);
                $fullPath = $receiptsStorage . DS . $attachment->name;
                $attachment->content = base64_encode(file_get_contents($fullPath));
                $attachment->type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullPath);

                $emailSent = false;
                try {
                    if ($emailType == 'magento') {
                        $order->sendNewOrderEmail();
                        $emailSent = (bool)$order->getEmailSent();
                    } elseif ($emailType == 'receipt') {
                        $receipt = Mage::helper('bakerloo_restful/email')->sendReceipt($order, $attachment);
                        $emailSent = (bool)$receipt->getEmailSent();
                    } else {
                        $order->sendNewOrderEmail();
                        $receipt = Mage::helper('bakerloo_restful/email')->sendReceipt($order, $attachment);
                        $emailSent = (bool)($order->getEmailSent() or $receipt->getEmailSent());
                    }

                    if ($emailSent) {
                        $this->updateEmailStatus($order);
                        $unsentEmail->delete();
                    }
                }
                catch (Exception $e) {
                    Mage::logException($e);

                    //Add row to email log reflecting failed attempt
                    $this->logEmail($order, $emailType, null, $e->getMessage());
                }

                //reset old order email
                $this->swapOrderEmail($order, $orderEmailAddress);
            }
        }
    }

    public function updateEmailStatus($order){
        //Add comment to order.
        $order->addStatusHistoryComment(Mage::helper('bakerloo_restful')->__("Order email sent to email address: \"%s\"", $order->getCustomerEmail()), false)
            ->setIsVisibleOnFront(false)
            ->setIsCustomerNotified(false)
            ->save();

        //Set send in corresponding queue record
        $queuedEmails = Mage::getModel('bakerloo_email/queue')->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $order->getId()));

        foreach($queuedEmails as $queuedEmail)
            $queuedEmail->setEmailResult(true)->save();
    }

    /**
     * PUT - Update original order JSON.
     */
    public function updateOrigOrder() {

        $id = (int)$this->_getQueryParameter('id');

        $posOrder = Mage::getModel('bakerloo_restful/order')->load($id);

        if(!$posOrder->getId())
            Mage::throwException('Order does not exist.');

        $jsonObj = $this->getJsonPayload();

        $posOrder->setJsonPayload(json_encode($jsonObj));

        $posOrder->save();

        return $this->_createDataObject($posOrder->getOrderId());
    }

    /**
     * Return ready to pickup orders.
     *
     * @return array
     */
    public function readyToPickup() {
        //get page
        $page = $this->_getQueryParameter('page');
        if(!$page)
            $page = 1;

        //Retrieve orders not completed and placed with our shipping method.
        $myFilters = array(
            'shipping_method,eq,bakerloo_store_pickup_bakerloo_store_pickup',
            'state,neq,complete',
            'state,neq,closed',
            'total_paid,notnull,',
        );

        $filters = $this->_getQueryParameter('filters');

        if(is_null($filters)) {
            $filters = $myFilters;
        }
        else {
            $filters = array_merge($filters, $myFilters);
        }

        $this->_filterUseOR = false;

        return $this->_getAllItems($page, $filters);
    }

    private function _getOrderAddress($order, $type) {
        if($type == 'billing')
            $address = $order->getBillingAddress();
        else
            $address = $order->getShippingAddress();

        if(!is_object($address))
            return null;

        $return = array(
            "id"                  => $address->getId(),
            "firstname"           => $address->getFirstname(),
            "lastname"            => $address->getLastname(),
            "country_id"          => $address->getCountry(),
            "city"                => $address->getCity(),
            "street"              => $address->getStreet(1),
            "street1"             => $address->getStreet(2),
            "region"              => $address->getRegion(),
            "region_id"           => $address->getRegionId(),
            "postcode"            => $address->getPostcode(),
            "telephone"           => $address->getTelephone(),
            "fax"                 => $address->getFax(),
            "company"             => $address->getCompany(),
            "is_shipping_address" => (int)($type == 'shipping'),
            "is_billing_address"  => (int)($type == 'billing'),
        );

        return $return;
    }

    private function _getAssociatedData($orderId, $resource) {

        $api = Mage::getModel('bakerloo_restful/api_' . $resource);
        $api->parameters = array(
            'not_by_id'=>'not_by_id',
            'filters' => array('order_id,eq,' . $orderId)
        );

        $invoices = $api->get();

        if(is_array($invoices) and array_key_exists('page_data', $invoices)) {
            return $invoices['page_data'];
        }
        else {
            return array();
        }

    }

    private function _getJsonPayload(Ebizmarts_BakerlooRestful_Model_Order $order){
        $payload = json_decode($order->getJsonPayload());

        if($payload) {
            $payload->payment->customer_signature = null;
            $payload->payment->customer_signature_type = null;
            $payload->payment->customer_signature_file = null;

            $addedPayments = isset($payload->payment->addedPayments) ? $payload->payment->addedPayments : array();
            foreach ($addedPayments as $_addedPayment) {
                $_addedPayment->customer_signature = null;
                $_addedPayment->customer_signature_type = null;
                $_addedPayment->customer_signature_file = null;
            }

            $payload->currency_rate = (float)$order->getBaseToOrderRate();
        }

        return $payload;
    }

    public function _getOrderPayments(Mage_Sales_Model_Order $order, Ebizmarts_BakerlooRestful_Model_Order $posOrder){

        if(!$posOrder->getId())
            return;

        $json = json_decode($posOrder->getJsonPayload());
        $payment = $order->getPayment();
        $result = null;

        if (!is_null($json) and $payment->getId()) {
            $result = isset($json->payment) ? $json->payment : new stdClass();

            $result->payment_id = (int)$payment->getId();
            $json->payment->payment_id = (int)$payment->getId();

            if(isset($result->customer_signature))
                $result->customer_signature = null;
            if(isset($result->customer_signature_type))
                $result->customer_signature_type = null;
            if(isset($result->customer_signature_file))
                $result->customer_signature_file = null;

            if($payment->getMethod() == Ebizmarts_BakerlooPayment_Model_Layaway::CODE) {

                if (isset($result->addedPayments) and is_array($result->addedPayments)) {

                    $installments = Mage::getModel('bakerloo_payment/installment')
                        ->getCollection()
                        ->addFieldToFilter('parent_id', array('eq' => $payment->getId()))
                        ->getItems();

                    $installments = array_values($installments);

                    $addedPayments = $result->addedPayments;
                    $addedPaymentKeys = array_keys($addedPayments);

                    foreach ($addedPaymentKeys as $_key) {
                        if(isset($installments[$_key])) {
                            $addedPayments[$_key]->payment_id = (int)$installments[$_key]->getPaymentId();
                            $json->payment->addedPayments[$_key]->payment_id = (int)$installments[$_key]->getPaymentId();

                            if(isset($addedPayments[$_key]->customer_signature))
                                $addedPayments[$_key]->customer_signature = null;
                            if(isset($addedPayments[$_key]->customer_signature_type))
                                $addedPayments[$_key]->customer_signature_type = null;
                            if(isset($addedPayments[$_key]->customer_signature_file))
                                $addedPayments[$_key]->customer_signature_file = null;

                            foreach($addedPayments[$_key]->refunds as $_ref)
                                $_ref->refund_id = (int)$installments[$_key]->getId();
                        }
                        else {
                            Mage::log(Mage::helper('bakerloo_restful')->__("Installment failed for order {$order->getId()}: {$order->getOrderCurrencyCode()} {$addedPayments[$_key]->amount} ({$addedPayments[$_key]->method})"));
                        }
                    }
                }
            }
        }

        $posOrder->setJsonPayload(json_encode($json))
            ->save();

        return $result;
    }

    public function setCustomerToOrder($customer, $order) {
        $order->setData('customer_id', $customer->getId());
        $order->setData('customer_is_guest', 0);
        $order->setData('customer_email', $customer->getEmail());
        $order->setData('customer_firstname', $customer->getFirstname());
        $order->setData('customer_lastname', $customer->getLastname());
        $order->setData('customer_group_id', $customer->getGroupId());
    }

}