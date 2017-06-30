<?php
    class WindsorCircle_Export_Model_Order extends Mage_Core_Model_Abstract
    {
        // Order Data
        protected $orderData = array();

        // Order Items Data
        protected $orderDetailsData = array();

        // Abandoned Shopping Cart Quote Data
        protected $quoteAscData = array();

        // Abandoned Shopping Cart Quote Items Data
        protected $quoteAscDetailsData = array();

        // Order ID Array
        protected $orderIdArray = array();

        // Quote ID Array
        protected $quoteIdArray = array();

        protected function _construct(){
            $this->_init('windsorcircle_export/order');
        }

        /**
         * Get Orders and OrderDetails between dates
         * @param string $startDate
         * @param string $endDate
         * @return array Returns Array of Order (Array[0]) and Order Details (Array[1])
         */
        public function getOrders($startDate, $endDate){
            $this->getOrder($startDate, $endDate)
                ->getOrderItems()
                ->getCanceled($startDate, $endDate)
                ->getOrderItems();

            return array($this->orderData, $this->orderDetailsData);
        }

        /**
         * Get Abandoned Shopping Cart Order Data
         *
         * @param   $startDate  Start Date of Orders
         * @param   $endDate    End Date of Orders
         * @return  array
         */
        public function getAscOrders($startDate, $endDate) {
            $this->getAscOrder($startDate, $endDate)
                 ->getAscOrderItems();

            return array($this->quoteAscData, $this->quoteAscDetailsData);
        }

        /**
         * Get Order Data between dates
         *
         * @param   $startDate  Start Date of Orders
         * @param   $endDate    End Data of Orders
         * @return  $this
         */
        protected function getOrder($startDate, $endDate){
            $orders = Mage::getModel('sales/order')->getCollection();

            $version = explode('.', Mage::getVersion());
            if ( $version[0] == 1 && $version[1] <= 3 )
            {
                $orders->addFieldToFilter('updated_at',array('datetime' => true, 'from' => $startDate, 'to' => $endDate))
                       ->addAttributeToSort('increment_id', 'ASC');
                $orders->getSelect()->joinLeft(array('customers' => 'customer_entity'), 'e.customer_id = customers.entity_id', array('customer_email' => 'customers.email'));
                $orders->getSelect()->joinLeft(array('newsletter' => Mage::getSingleton('core/resource')->getTableName('newsletter/subscriber')), 'customers.email = newsletter.subscriber_email', array('newsletter.subscriber_status'));
                $orders->getSelect()->joinLeft(array('customer_group' => Mage::getSingleton('core/resource')->getTableName('customer/customer_group')), 'customers.group_id = customer_group.customer_group_id', array('customer_group.customer_group_code'));
            }
            else
            {
                $orders->addFieldToFilter('main_table.updated_at',array('datetime' => true, 'from' => $startDate, 'to' => $endDate))
                       ->addAttributeToSort('main_table.increment_id', 'ASC');
                $orders->getSelect()->joinLeft(array('newsletter' => Mage::getSingleton('core/resource')->getTableName('newsletter/subscriber')), 'main_table.customer_email = newsletter.subscriber_email',array('newsletter.subscriber_status'));
                $orders->getSelect()->joinLeft(array('customers' => Mage::getSingleton('core/resource')->getTableName('customer/entity')), 'main_table.customer_id = customers.entity_id', array('customer_email' => 'customers.email'));
                $orders->getSelect()->joinLeft(array('customer_group' => Mage::getSingleton('core/resource')->getTableName('customer/customer_group')), 'customers.group_id = customer_group.customer_group_id', array('customer_group.customer_group_code'));
            }

            $status = Mage::getModel('windsorcircle_export/status');

            $helper = Mage::helper('windsorcircle_export');

            $this->addHeaders();

            foreach($orders->getData() as $orderData){
                $customerGroupCode = $orderData['customer_group_code'];

                $subscriber_status = $orderData['subscriber_status'];
                $order = Mage::getSingleton('sales/order')->load($orderData['entity_id']);
                $orderData = $order->getData();
                $couponCode = isset($order['coupon_code']) ? $order['coupon_code'] : $order->getCouponCode();

                if(empty($orderData['shipping_address_id']) && !empty($orderData['billing_address_id'])) {
                    $shippingAddress = Mage::getSingleton('sales/order_address')->load($orderData['billing_address_id'])->getData();
                    $billingAddress = Mage::getSingleton('sales/order_address')->load($orderData['billing_address_id'])->getData();
                } elseif(!empty($orderData['shipping_address_id']) && !empty($orderData['billing_address_id'])) {
                    $shippingAddress = Mage::getSingleton('sales/order_address')->load($orderData['shipping_address_id'])->getData();
                    $billingAddress = Mage::getSingleton('sales/order_address')->load($orderData['billing_address_id'])->getData();
                }

                if(empty($orderData['shipping_address_id']) && empty($orderData['billing_address_id'])) {
                    $custBName = '';
                    $custName = '';
                    $custFName = '';
                    $custLName = '';
                    $custAddr1 = '';
                    $custCity = '';
                    $custState = '';
                    $custZip = '';
                    $custCountry = '';
                    $custPhone = '';

                    $shipName = '';
                    $shipFName = '';
                    $shipLName = '';
                    $shipAddr1 = '';
                    $shipCity = '';
                    $shipState = '';
                    $shipZip = '';
                    $shipCountry = '';
                } else {
                    $custBName = $billingAddress['company'];
                    $custName = $billingAddress['firstname'] . ' ' . $billingAddress['lastname'];
                    $custFName = $billingAddress['firstname'];
                    $custLName = $billingAddress['lastname'];
                    $custAddr1 = $helper->formatString($billingAddress['street']);
                    $custCity = $helper->formatString($billingAddress['city']);
                    $custState = $helper->formatString($billingAddress['region']);
                    $custZip = $helper->formatString($billingAddress['postcode']);
                    $custCountry = $helper->formatString($billingAddress['country_id']);
                    $custPhone = $billingAddress['telephone'];

                    $shipName = $shippingAddress['firstname'] . ' ' . $shippingAddress['lastname'];
                    $shipFName = $shippingAddress['firstname'];
                    $shipLName = $shippingAddress['lastname'];
                    $shipAddr1 = $helper->formatString($shippingAddress['street']);
                    $shipCity = $helper->formatString($shippingAddress['city']);
                    $shipState = $helper->formatString($shippingAddress['region']);
                    $shipZip = $helper->formatString($shippingAddress['postcode']);
                    $shipCountry = $helper->formatString($shippingAddress['country_id']);
                }

                $this->orderIdArray['entity_id'][] = $orderData['entity_id'];
                $this->orderIdArray['order_id'][$orderData['entity_id']]  = $orderData['increment_id'];

                $custEmailOpt = $this->getNewsletterStatus($subscriber_status);

                // Format for time fields
                $time = '';
                $time = new DateTime($orderData['created_at']);
                $orderDate = $time->format('Ymd');
                $orderTime = $time->format('H:i:s');


                // Array of Order Data
                $this->orderData[$orderData['increment_id']] = array('orderId' 		=>	$orderData['increment_id'],
                                                                    'orderDate' 	=>	$orderDate,
                                                                    'orderTime'		=>	$orderTime,
                                                                    'storeId'		=> 	$orderData['store_id'],
                                                                    'custId'		=>	$orderData['customer_id'],
                                                                    'custGroupId'   =>  $orderData['customer_group_id'],
                                                                    'custGroupName' =>  $customerGroupCode,
                                                                    'custBName'		=>	$custBName,
                                                                    'custName'		=>	$custName,
                                                                    'custFName'		=>	$custFName,
                                                                    'custLName'		=>	$custLName,
                                                                    'custEmail'		=>	$orderData['customer_email'],
                                                                    'custEmailOpt'	=>	$custEmailOpt,
                                                                    'custAddr1'		=>	$custAddr1,
                                                                    'custAddr2'		=>	'',
                                                                    'custCity'		=>	$custCity,
                                                                    'custState'		=>	$custState,
                                                                    'custZip'		=>	$custZip,
                                                                    'custCountry'	=>	$custCountry,
                                                                    'custPhone'		=>	$custPhone,
                                                                    'shipName'		=>	$shipName,
                                                                    'shipFName'		=>	$shipFName,
                                                                    'shipLName'		=>	$shipLName,
                                                                    'shipAddr1'		=>	$shipAddr1,
                                                                    'shipAddr2'		=>	'',
                                                                    'shipCity'		=>	$shipCity,
                                                                    'shipState'		=>	$shipState,
                                                                    'shipZip'		=>	$shipZip,
                                                                    'shipCountry'	=>	$shipCountry,
                                                                    'shipMethod'	=>	$orderData['shipping_description'],
                                                                    'shipCost'		=>	$orderData['base_shipping_amount'],
                                                                    'couponCodes'	=>	$couponCode,
                                                                    'couponDiscount'=>	$orderData['discount_amount'],
                                                                    'discountType'	=>	'',
                                                                    'discount'		=>	'',
                                                                    'prodTotal'		=>	($orderData['base_subtotal'] + $orderData['discount_amount']),
                                                                    'cancelled'		=>	$status->canceled($orderData['status']),
                                                                    );
            }
            return $this;
        }

        /**
         * Get Orders and OrderDetails between dates and status is closed or canceled
         * @param string $startDate
         * @param string $endDate
         */
        protected function getCanceled($startDate, $endDate){
            $orders = Mage::getModel('sales/order')->getCollection()
                        ->addFieldToFilter('status', array(array('eq' => 'closed'), array('eq' => 'canceled')));

            $version = explode('.', Mage::getVersion());
            if ( $version[0] == 1 && $version[1] <= 3 )
            {
                $orders->addFieldToFilter('updated_at',array('datetime' => true, 'from' => $startDate, 'to' => $endDate))
                       ->addAttributeToSort('increment_id', 'ASC');
                $orders->getSelect()->joinLeft(array('customers' => 'customer_entity'), 'e.customer_id = customers.entity_id', array('customer_email' => 'customers.email'));
                $orders->getSelect()->joinLeft(array('newsletter' => Mage::getSingleton('core/resource')->getTableName('newsletter/subscriber')), 'customers.email = newsletter.subscriber_email', array('newsletter.subscriber_status'));
                $orders->getSelect()->joinLeft(array('customer_group' => Mage::getSingleton('core/resource')->getTableName('customer/customer_group')), 'customers.group_id = customer_group.customer_group_id', array('customer_group.customer_group_code'));
            }
            else
            {
                $orders->addFieldToFilter('main_table.updated_at',array('datetime' => true, 'from' => $startDate, 'to' => $endDate))
                       ->addAttributeToSort('main_table.increment_id', 'ASC');
                $orders->getSelect()->joinLeft(array('newsletter' => Mage::getSingleton('core/resource')->getTableName('newsletter/subscriber')), 'main_table.customer_email = newsletter.subscriber_email',array('newsletter.subscriber_status'));
                $orders->getSelect()->joinLeft(array('customers' => Mage::getSingleton('core/resource')->getTableName('customer/entity')), 'main_table.customer_id = customers.entity_id', array('customer_email' => 'customers.email'));
                $orders->getSelect()->joinLeft(array('customer_group' => Mage::getSingleton('core/resource')->getTableName('customer/customer_group')), 'customers.group_id = customer_group.customer_group_id', array('customer_group.customer_group_code'));
            }

            $helper = Mage::helper('windsorcircle_export');
            $this->orderIdArray = array();

            foreach($orders->getData() as $orderData){
                $customerGroupCode = $orderData['customer_group_code'];

                $subscriber_status = $orderData['subscriber_status'];
                $order = Mage::getSingleton('sales/order')->load($orderData['entity_id']);
                $orderData = $order->getData();                
                $couponCode = isset($order['coupon_code']) ? $order['coupon_code'] : $order->getCouponCode();

                if(!empty($this->orderData[$orderData['increment_id']]) && is_array($this->oderData[$orderData['increment_id']])){
                    $this->orderData[$orderData['increment_id']]['cancelled'] = 'Y';
                } else {

                    if(empty($orderData['shipping_address_id']) && !empty($orderData['billing_address_id'])) {
                        $shippingAddress = Mage::getSingleton('sales/order_address')->load($orderData['billing_address_id'])->getData();
                        $billingAddress = Mage::getSingleton('sales/order_address')->load($orderData['billing_address_id'])->getData();
                    } elseif(!empty($orderData['shipping_address_id']) && !empty($orderData['billing_address_id'])) {
                        $shippingAddress = Mage::getSingleton('sales/order_address')->load($orderData['shipping_address_id'])->getData();
                        $billingAddress = Mage::getSingleton('sales/order_address')->load($orderData['billing_address_id'])->getData();
                    }

                    if(empty($orderData['shipping_address_id']) && empty($orderData['billing_address_id'])) {
                        $custBName = '';
                        $custName = '';
                        $custFName = '';
                        $custLName = '';
                        $custAddr1 = '';
                        $custCity = '';
                        $custState = '';
                        $custZip = '';
                        $custCountry = '';
                        $custPhone = '';

                        $shipName = '';
                        $shipFName = '';
                        $shipLName = '';
                        $shipAddr1 = '';
                        $shipCity = '';
                        $shipState = '';
                        $shipZip = '';
                        $shipCountry = '';
                    } else {
                        $custBName = $billingAddress['company'];
                        $custName = $billingAddress['firstname'] . ' ' . $billingAddress['lastname'];
                        $custFName = $billingAddress['firstname'];
                        $custLName = $billingAddress['lastname'];
                        $custAddr1 = $helper->formatString($billingAddress['street']);
                        $custCity = $helper->formatString($billingAddress['city']);
                        $custState = $helper->formatString($billingAddress['region']);
                        $custZip = $helper->formatString($billingAddress['postcode']);
                        $custCountry = $helper->formatString($billingAddress['country_id']);
                        $custPhone = $billingAddress['telephone'];

                        $shipName = $shippingAddress['firstname'] . ' ' . $shippingAddress['lastname'];
                        $shipFName = $shippingAddress['firstname'];
                        $shipLName = $shippingAddress['lastname'];
                        $shipAddr1 = $helper->formatString($shippingAddress['street']);
                        $shipCity = $helper->formatString($shippingAddress['city']);
                        $shipState = $helper->formatString($shippingAddress['region']);
                        $shipZip = $helper->formatString($shippingAddress['postcode']);
                        $shipCountry = $helper->formatString($shippingAddress['country_id']);
                    }

                    $this->orderIdArray['entity_id'][] = $orderData['entity_id'];
                    $this->orderIdArray['order_id'][$orderData['entity_id']]  = $orderData['increment_id'];

                    $custEmailOpt = $this->getNewsletterStatus($subscriber_status);

                    // Format for time fields
                    // Could be put in a custom class
                    $time = '';
                    $time = new DateTime($orderData['created_at']);
                    $orderDate = $time->format('Ymd');
                    $orderTime = $time->format('H:i:s');

                    $this->orderData[$orderData['increment_id']] = array('orderId' 		=>	$orderData['increment_id'],
                                                                        'orderDate' 	=>	$orderDate,
                                                                        'orderTime'		=>	$orderTime,
                                                                        'storeId'		=> 	$orderData['store_id'],
                                                                        'custId'		=>	$orderData['customer_id'],
                                                                        'custGroupId'   =>  $orderData['customer_group_id'],
                                                                        'custGroupName' =>  $customerGroupCode,
                                                                        'custBName'		=>	$custBName,
                                                                        'custName'		=>	$custName,
                                                                        'custFName'		=>	$custFName,
                                                                        'custLName'		=>	$custLName,
                                                                        'custEmail'		=>	$orderData['customer_email'],
                                                                        'custEmailOpt'	=>	$custEmailOpt,
                                                                        'custAddr1'		=>	$custAddr1,
                                                                        'custAddr2'		=>	'',
                                                                        'custCity'		=>	$custCity,
                                                                        'custState'		=>	$custState,
                                                                        'custZip'		=>	$custZip,
                                                                        'custCountry'	=>	$custCountry,
                                                                        'custPhone'		=>	$custPhone,
                                                                        'shipName'		=>	$shipName,
                                                                        'shipFName'		=>	$shipFName,
                                                                        'shipLName'		=>	$shipLName,
                                                                        'shipAddr1'		=>	$shipAddr1,
                                                                        'shipAddr2'		=>	'',
                                                                        'shipCity'		=>	$shipCity,
                                                                        'shipState'		=>	$shipState,
                                                                        'shipZip'		=>	$shipZip,
                                                                        'shipCountry'	=>	$shipCountry,
                                                                        'shipMethod'	=>	$orderData['shipping_description'],
                                                                        'shipCost'		=>	$orderData['base_shipping_amount'],
                                                                        'couponCodes'	=>	$couponCode,
                                                                        'couponDiscount'=>	$orderData['discount_amount'],
                                                                        'discountType'	=>	'',
                                                                        'discount'		=>	'',
                                                                        'prodTotal'		=>	($orderData['base_subtotal'] + $orderData['discount_amount']),
                                                                        'cancelled'		=>	'Y'
                                                                        );
                }
            }
            return $this;
        }

        protected function getAscOrder($startDate, $endDate) {
            $quotes = Mage::getModel('sales/quote')->getCollection();

            $version = explode('.', Mage::getVersion());
            if ( $version[0] == 1 && $version[1] <= 3 )
            {
                $quotes->addFieldToFilter('updated_at',array('datetime' => true, 'from' => $startDate, 'to' => $endDate))
                    ->addFieldToFilter('items_qty', array('gt' => 0))
                    ->setOrder('increment_id', 'ASC');
            }
            else
            {
                $quotes->addFieldToFilter('main_table.updated_at',array('datetime' => true, 'from' => $startDate, 'to' => $endDate))
                    ->addFieldToFilter('main_table.items_qty', array('gt' => 0))
                    ->setOrder('main_table.entity_id', 'ASC');
                $quotes->getSelect()->joinLeft(array('address_billing' => Mage::getSingleton('core/resource')->getTableName('sales/quote_address')), 'main_table.entity_id = address_billing.quote_id and address_billing.address_type = "billing"', array('billing_id' => 'address_billing.address_id'));
                $quotes->getSelect()->joinLeft(array('address_shipping' => Mage::getSingleton('core/resource')->getTableName('sales/quote_address')), 'main_table.entity_id = address_shipping.quote_id and address_shipping.address_type = "shipping"', array('shipping_id' => 'address_shipping.address_id'));
            }

            $this->addAscHeaders();
            $helper = Mage::helper('windsorcircle_export');

            foreach($quotes->getData() as $quoteData){

                if ($quoteData['customer_id']) {
                    $customer = Mage::getModel('customer/customer')->load($quoteData['customer_id']);
                    $billingAddress = Mage::getSingleton('customer/address')
                                        ->setData(array())
                                        ->load($customer->getDefaultBilling());
                    $shippingAddress = Mage::getSingleton('customer/address')
                                        ->setData(array())
                                        ->load($customer->getDefaultShipping());
                } else {
                    $billingAddress = Mage::getSingleton('sales/quote_address')->setData(array())->load($quoteData['billing_id']);
                    $shippingAddress = Mage::getSingleton('sales/quote_address')->setData(array())->load($quoteData['shipping_id']);
                }

                if (!$billingAddress->isEmpty()) {
                    $custBName = $billingAddress->getCompany();
                    $custName = implode(' ', array($billingAddress->getFirstname(), $billingAddress->getLastname()));
                    $custFName = $billingAddress->getFirstname();
                    $custLName = $billingAddress->getLastname();
                    $custAddr1 = $helper->formatString(implode('\n', $billingAddress->getStreet()));
                    $custCity = $helper->formatString($billingAddress->getCity());
                    $custState = $helper->formatString($billingAddress->getRegion());
                    $custZip = $helper->formatString($billingAddress->getPostcode());
                    $custCountry = $helper->formatString($billingAddress->getCountryId());
                    $custPhone = $billingAddress->getTelephone();
                } else {
                    $custBName  = '';
                    $custName   = '';
                    $custFName  = '';
                    $custLName  = '';
                    $custAddr1  = '';
                    $custCity   = '';
                    $custState  = '';
                    $custZip    = '';
                    $custCountry= '';
                    $custPhone  = '';
                }

                if (!$shippingAddress->isEmpty()) {
                    $shipName = implode(' ', array($shippingAddress->getFirstname(), $shippingAddress->getLastname()));
                    $shipFName = $shippingAddress->getFirstname();
                    $shipLName = $shippingAddress->getLastname();
                    $shipAddr1 = $helper->formatString(implode('\n', $shippingAddress->getStreet()));
                    $shipCity = $helper->formatString($shippingAddress->getCity());
                    $shipState = $helper->formatString($shippingAddress->getRegion());
                    $shipZip = $helper->formatString($shippingAddress->getPostcode());
                    $shipCountry = $helper->formatString($shippingAddress->getCountryId());
                } else {
                    $shipName   = '';
                    $shipFName  = '';
                    $shipLName  = '';
                    $shipAddr1  = '';
                    $shipCity   = '';
                    $shipState  = '';
                    $shipZip    = '';
                    $shipCountry= '';
                }

                $this->quoteIdArray['entity_id'][] = $quoteData['entity_id'];

                // Format for time fields
                $createdTime = new DateTime($quoteData['created_at']);
                $createdTime = $createdTime->format('Ymd H:i:s');
                $updatedTime = new DateTime($quoteData['updated_at']);
                $updatedTime = $updatedTime->format('Ymd H:i:s');
                $convertedAt = '';

                // Array of Order Data
                $this->quoteAscData[$quoteData['entity_id']] = array('orderId'              =>  $quoteData['entity_id'],
                                                                     'reserved_order_id'    =>  $quoteData['reserved_order_id'],
                                                                     'createdtimestamp'     =>  $createdTime,
                                                                     'updatedtimestamp'     =>  $updatedTime,
                                                                     'convertedtimestamp'   =>  $convertedAt,
                                                                     'is_active'            =>  $quoteData['is_active'],
                                                                     'storeId'              =>  $quoteData['store_id'],
                                                                     'cartUrl'              =>  Mage::helper('checkout/url')->getCheckoutUrl(),
                                                                     'custId'               =>  $quoteData['customer_id'],
                                                                     'custBName'            =>  $custBName,
                                                                     'custName'             =>  $custName,
                                                                     'custFName'            =>  $custFName,
                                                                     'custLName'            =>  $custLName,
                                                                     'custEmail'            =>  $quoteData['customer_email'],
                                                                     'custAddr1'            =>  $custAddr1,
                                                                     'custAddr2'            =>  '',
                                                                     'custCity'             =>  $custCity,
                                                                     'custState'            =>  $custState,
                                                                     'custZip'              =>  $custZip,
                                                                     'custCountry'          =>  $custCountry,
                                                                     'custPhone'            =>  $custPhone,
                                                                     'shipName'             =>  $shipName,
                                                                     'shipFName'            =>  $shipFName,
                                                                     'shipLName'            =>  $shipLName,
                                                                     'shipAddr1'            =>  $shipAddr1,
                                                                     'shipAddr2'            =>  '',
                                                                     'shipCity'             =>  $shipCity,
                                                                     'shipState'            =>  $shipState,
                                                                     'shipZip'              =>  $shipZip,
                                                                     'shipCountry'          =>  $shipCountry,
                                                                     'shipMethod'           =>  isset($quoteData['shipping_description']) ? $quoteData['shipping_description']:'',
                                                                     'shipCost'             =>  isset($quoteData['base_shipping_amount']) ? $quoteData['base_shipping_amount']:'',
                                                                     'couponCodes'          =>  $quoteData['coupon_code'],
                                                                     'couponDiscount'       =>  $quoteData['base_subtotal'] - $quoteData['base_subtotal_with_discount'],
                                                                     'discountType'         =>  '',
                                                                     'discount'             =>  '',
                                                                     'prodTotal'            =>  $quoteData['base_subtotal_with_discount'],
                                                                    );
            }
            return $this;
        }

        /**
         * Adds headers to array
         */
        protected function addHeaders(){
            $this->orderData[0] = array('orderId'		=>	'OrderId',
                                        'orderDate' 	=>	'OrderDate',
                                        'orderTime'		=>	'OrderTime',
                                        'storeId'		=> 	'StoreId',
                                        'custId'		=>	'CustId',
                                        'custGroupId'   =>  'CustGroupId',
                                        'custGroupName' =>  'CustGroupName',
                                        'custBName'		=>	'CustBName',
                                        'custName'		=>	'CustName',
                                        'custFName'		=>	'CustFName',
                                        'custLName'		=>	'CustLName',
                                        'custEmail'		=>	'CustEmail',
                                        'custEmailOpt'	=>	'CustEmailOpt',
                                        'custAddr1'		=>	'CustAddr1',
                                        'custAddr2'		=>	'CustAddr2',
                                        'custCity'		=>	'CustCity',
                                        'custState'		=>	'CustState',
                                        'custZip'		=>	'CustZip',
                                        'custCountry'	=>	'CustCountry',
                                        'custPhone'		=>	'CustPhone',
                                        'shipName'		=>	'ShipName',
                                        'shipFName'		=>	'ShipFName',
                                        'shipLName'		=>	'ShipLName',
                                        'shipAddr1'		=>	'ShipAddr1',
                                        'shipAddr2'		=>	'ShipAddr2',
                                        'shipCity'		=>	'ShipCity',
                                        'shipState'		=>	'ShipState',
                                        'shipZip'		=>	'ShipZip',
                                        'shipCountry'	=>	'ShipCountry',
                                        'shipMethod'	=>	'ShipMethod',
                                        'shipCost'		=>	'ShipCost',
                                        'couponCodes'	=>	'CouponCodes',
                                        'couponDiscount'=>	'CouponDiscount',
                                        'discountType'	=>	'DiscountType',
                                        'discount'		=>	'Discount',
                                        'prodTotal'		=>	'ProdTotal',
                                        'cancelled'		=>	'Cancelled',
                                        );

            $this->orderDetailsData[0][0] = array('orderId'		=>	'OrderId',
                                                  'storeId'		=>	'StoreId',
                                                  'prodId'		=>	'ProdId',
                                                  'qtyOrdered'	=>	'QtyOrdered',
                                                  'qtyReturned'	=>	'QtyReturned',
                                                  'masterId'	=>	'PSKU',
                                                  'simpleId'	=>	'VSKU',
                                                  'price'		=>	'Price'
                                                );
        }

        protected function addAscHeaders(){
            $this->quoteAscData[0] = array('orderId'		    =>	'CartID',
                                           'reserved_order_id'  =>  'ReservedOrderID',
                                           'createdtimestamp'   =>  'CreatedTimestamp',
                                           'updatedtimestamp'   =>  'UpdatedTimestamp',
                                           'convertedtimestamp' =>  'ConvertedTimestamp',
                                           'is_active'          =>  'IsActive',
                                           'storeId'            =>  'StoreID',
                                           'cartUrl'            =>  'CartURL',
                                           'custId'             =>  'CustID',
                                           'custBName'          =>  'CustBName',
                                           'custName'           =>  'CustName',
                                           'custFName'          =>  'CustFName',
                                           'custLName'          =>  'CustLName',
                                           'custEmail'          =>  'CustEmail',
                                           'custAddr1'          =>  'CustAddr1',
                                           'custAddr2'          =>  'CustAddr2',
                                           'custCity'           =>  'CustCity',
                                           'custState'          =>  'CustState',
                                           'custZip'            =>  'CustZip',
                                           'custCountry'        =>  'CustCountry',
                                           'custPhone'          =>  'CustPhone',
                                           'shipName'           =>  'ShipName',
                                           'shipFName'          =>  'ShipFName',
                                           'shipLName'          =>  'ShipLName',
                                           'shipAddr1'          =>  'ShipAddr1',
                                           'shipAddr2'          =>  'ShipAddr2',
                                           'shipCity'           =>  'ShipCity',
                                           'shipState'          =>  'ShipState',
                                           'shipZip'            =>  'ShipZip',
                                           'shipCountry'        =>  'ShipCountry',
                                           'shipMethod'         =>  'ShipMethod',
                                           'shipCost'           =>  'ShipCost',
                                           'couponCodes'        =>  'CouponCodes',
                                           'couponDiscount'     =>  'CouponDiscount',
                                           'discountType'       =>  'DiscountType',
                                           'discount'           =>  'Discount',
                                           'prodTotal'          =>  'ProdTotal',
                                          );

            $this->quoteAscDetailsData[0][0] = array('cartId'      =>   'CartID',
                                                     'storeId'     =>   'StoreID',
                                                     'simpleId'    =>   'VSKU',
                                                     'masterId'    =>   'PSKU',
                                                     'prodId'      =>   'ProdID',
                                                     'price'       =>   'Price',
                                                     'quantity'    =>   'Quantity',
                                                    );
        }

        protected function getNewsletterStatus($status){
            if(empty($status) ||
                $status == 3){
                    return '3';
            } elseif(!empty($status) &&
                $status == 1){
                    return '1';
            } else {
                    return '';
            }
        }


        protected function getOrderItems(){
            if(!empty($this->orderIdArray['entity_id'])){
                $version = explode('.', Mage::getVersion());
                // If Magento version 1.3 then you must use addOrder instead of addAttributeToSort otherwise it breaks
                if ( $version[0] == 1 && $version[1] <= 3 )
                {
                    $orderItems = Mage::getModel('sales/order_item')->getCollection()
                                ->join('catalog/product',
                                            'main_table.product_id=`catalog/product`.entity_id',
                                            array('sku' => 'IF(ISNULL(`catalog/product`.sku), main_table.sku, `catalog/product`.sku)'))
                                ->addFieldToFilter('order_id', array('in' => $this->orderIdArray['entity_id']))
                                ->addOrder('product_type', 'ASC');
                }
                else
                {
                    $orderItems = Mage::getModel('sales/order_item')->getCollection()
                                ->join('catalog/product',
                                            'main_table.product_id=`catalog/product`.entity_id',
                                            array('sku' => 'IF(ISNULL(`catalog/product`.sku), main_table.sku, `catalog/product`.sku)'))
                                ->addFieldToFilter('order_id', array('in' => $this->orderIdArray['entity_id']))
                                ->addAttributeToSort('product_type', 'ASC');
                }

                $itemCheck = array();
                foreach($orderItems->getData() as $item) {
                    $itemCheck[$item['item_id']] = array('type' => $item['product_type'], 'sku' => $item['sku']);
                }

                foreach($orderItems->getData() as $item){
                    $realOrderId = $this->orderIdArray['order_id'][$item['order_id']];

                    if ( $version[0] == 1 && $version[1] <= 3 ) {
                        $order = Mage::getSingleton('sales/order')->load($item['order_id']);
                        $storeId = $order->getStoreId();
                    } else {
                        $storeId = $item['store_id'];
                    }

                    if($item['product_type'] == 'configurable' || $item['product_type'] == '') {
                        $this->orderDetailsData[$realOrderId][$item['item_id']] = array('orderId'		=>	$realOrderId,
                                                                                        'storeId'		=>	$storeId,
                                                                                        'prodId'		=>	$item['product_id'],
                                                                                        'qtyOrdered'	=>	$item['qty_ordered'],
                                                                                        'qtyReturned'	=>	$item['qty_refunded'],
                                                                                        'masterId'		=>	'',
                                                                                        'simpleId'		=>	$item['sku'],
                                                                                        'price'			=>	number_format($item['base_price'], 2,'.',''));
                    } elseif(($item['product_type'] == 'simple' && $item['parent_item_id'] != null) && $itemCheck[$item['parent_item_id']]['type'] == 'bundle') {
                        $this->orderDetailsData[$realOrderId][$item['item_id']]['orderId']		= $realOrderId;
                        $this->orderDetailsData[$realOrderId][$item['item_id']]['storeId']		= $storeId;
                        $this->orderDetailsData[$realOrderId][$item['item_id']]['prodId']		= $item['product_id'];
                        $this->orderDetailsData[$realOrderId][$item['item_id']]['qtyOrdered']	= $item['qty_ordered'];
                        $this->orderDetailsData[$realOrderId][$item['item_id']]['qtyReturned']	= $item['qty_refunded'];
                        $this->orderDetailsData[$realOrderId][$item['item_id']]['masterId']		= $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['simpleId'];
                        $this->orderDetailsData[$realOrderId][$item['item_id']]['simpleId']		= $item['sku'];
                        $this->orderDetailsData[$realOrderId][$item['item_id']]['price']		= '0.00';
                    } elseif(($item['product_type'] == 'simple' && $item['parent_item_id'] != null) && $itemCheck[$item['parent_item_id']]['type'] != 'simple') {
                        $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['orderId']		= $realOrderId;
                        $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['storeId']		= $storeId;
                        $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['prodId']		= $item['product_id'];
                        $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['qtyOrdered']	= $item['qty_ordered'];
                        $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['qtyReturned']	= $item['qty_refunded'];
                        $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['masterId']		= $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['simpleId'];
                        $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['simpleId']		= $item['sku'];

                        // Force base_price to be a float because a string '0.0000' is not empty from php
                        $item['base_price'] = (float) $item['base_price'];
                        if(!empty($item['base_price'])) {
                            $this->orderDetailsData[$realOrderId][$item['parent_item_id']]['price']			= number_format($item['base_price'], 2,'.','');
                        }
                    } else {
                        $this->orderDetailsData[$realOrderId][$item['item_id']] = array('orderId'		=>	$realOrderId,
                                                                                        'storeId'		=>	$storeId,
                                                                                        'prodId'		=>	$item['product_id'],
                                                                                        'qtyOrdered'	=>	$item['qty_ordered'],
                                                                                        'qtyReturned'	=>	$item['qty_refunded'],
                                                                                        'masterId'		=>	'',
                                                                                        'simpleId'		=>	$item['sku'],
                                                                                        'price'			=>	number_format($item['base_price'], 2,'.',''));
                    }
                }
            }
            return $this;
        }

        protected function getAscOrderItems() {
            if(!empty($this->quoteIdArray['entity_id'])){
                $version = explode('.', Mage::getVersion());
                
                $quoteItems = Mage::getModel('sales/quote_item')->getCollection()
                    ->join('catalog/product',
                        'main_table.product_id=`catalog/product`.entity_id',
                        array('sku' => 'IF(ISNULL(`catalog/product`.sku), main_table.sku, `catalog/product`.sku)'))
                    ->addFieldToFilter('quote_id', array('in' => $this->quoteIdArray['entity_id']))
                    ->addOrder('product_type', 'ASC');

                $itemCheck = array();
                foreach($quoteItems->getData() as $item) {
                    $itemCheck[$item['item_id']] = array('type' => $item['product_type'], 'sku' => $item['sku']);
                }

                foreach($quoteItems->getData() as $item){
                    if ( $version[0] == 1 && $version[1] <= 3 ) {
                        $quote = Mage::getSingleton('sales/quote')->load($item['quote_id']);
                        $storeId = $quote->getStoreId();
                    } else {
                        $storeId = $item['store_id'];
                    }

                    if($item['product_type'] == 'configurable' || $item['product_type'] == '') {
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']] = array('cartId'=>  $item['quote_id'],
                                                                                           'storeId'    =>  $storeId,
                                                                                           'simpleId'   =>  $item['sku'],
                                                                                           'masterId'   =>  '',
                                                                                           'prodId'     =>  $item['product_id'],
                                                                                           'price'      =>  number_format($item['base_price'], 2,'.',''),
                                                                                           'quantity'   =>  $item['qty']);
                    } elseif(($item['product_type'] == 'simple' && $item['parent_item_id'] != null) && $itemCheck[$item['parent_item_id']]['type'] == 'bundle') {
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']]['cartId']        = $item['quote_id'];
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']]['storeId']       = $storeId;
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']]['masterId']      = $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['simpleId'];
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']]['simpleId']      = $item['sku'];
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']]['prodId']        = $item['product_id'];
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']]['price']         = '0.00';
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']]['quantity']      = $item['qty'];
                    } elseif(($item['product_type'] == 'simple' && $item['parent_item_id'] != null) && $itemCheck[$item['parent_item_id']]['type'] != 'simple') {
                        $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['cartId']     = $item['quote_id'];
                        $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['storeId']    = $storeId;
                        $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['masterId']   = $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['simpleId'];
                        $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['simpleId']   = $item['sku'];
                        $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['prodId']     = $item['product_id'];

                        // Force base_price to be a float because a string '0.0000' is not empty from php
                        $item['base_price'] = (float) $item['base_price'];
                        if(!empty($item['base_price'])) {
                            $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['price'] = number_format($item['base_price'], 2,'.','');
                        }

                        $this->quoteAscDetailsData[$item['quote_id']][$item['parent_item_id']]['quantity'] = $item['qty'];
                    } else {
                        $this->quoteAscDetailsData[$item['quote_id']][$item['item_id']] = array('cartId'=>  $item['quote_id'],
                                                                                           'storeId'    =>  $storeId,
                                                                                           'simpleId'   =>  $item['sku'],
                                                                                           'masterId'   =>  '',
                                                                                           'prodId'     =>  $item['product_id'],
                                                                                           'price'      =>  number_format($item['base_price'], 2,'.',''),
                                                                                           'quantity'   =>  $item['qty']);
                    }
                }
            }
        }
    }
