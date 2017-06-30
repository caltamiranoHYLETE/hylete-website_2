<?php

class Ebizmarts_BakerlooRestful_Model_Api_Loyalty extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model   = "bakerloo_restful/integrationDispatcher";

    public function get() {
        Mage::throwException('Not implemented.');
    }

    public function post() {
        //ToDo: Give points to the customer: Ebizmarts_BakerlooLoyalty_Model_Abstract::rewardCustomer
        Mage::throwException('Not implemented.');
    }

    /**
     * Return qty of points to earn.
     *
     * PUT
     */
    public function put() {

        if(!$this->getStoreId())
            Mage::throwException('Please provide a Store ID.');

        Varien_Profiler::start('POS::' . __METHOD__);

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        $loyalty = Mage::getModel('bakerloo_restful/integrationDispatcher',
            array('integration_type' => 'loyalty') );

        $quote = Mage::helper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data, false);

        $points = $loyalty->getYouWillEarnPoints($quote);

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $points;

    }

    /**
     * GET
     *
     * @see http://docs.ebizmartspos.apiary.io/#loyalty
     */
    public function productRedeemOptions() {
        $customerId = (int)$this->_getQueryParameter('customer_id');
        $productIdentifier  = (int)$this->_getQueryParameter('product');

        $customer = Mage::getModel('customer/customer')->load($customerId);
        if(!$customer->getId()) {
            return array();
        }

        $productModel = Mage::getModel('catalog/product');
        $product = $productModel->load($productIdentifier);
        if(!$product->getId()) {

            $productId = $productModel->getIdBySku($productIdentifier);

            $productModel->unsetData();
            $productModel->unsetOldData();
            $product = $productModel->load($productId);
            if(!$product->getId()) {
                return array();
            }
        }

        $loyalty = Mage::getModel('bakerloo_restful/integrationDispatcher',
            array('integration_type' => 'loyalty', 'customer' => $customer) );

        $options = $loyalty->productRedeemOptions($customer, $product);

        return array('reward_points_balance' => (float)$loyalty->getPointsBalance(), 'rules' => $options);
    }

    /**
     * Get the redemption rules applicable to a cart.
     */
    public function cartRedeemOptions() {

        Varien_Profiler::start('POS::' . __METHOD__);

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data     = $this->getJsonPayload();

        $customer = Mage::getModel('customer/customer')->load($data->customer->customer_id);

        $loyalty  = Mage::getModel('bakerloo_restful/integrationDispatcher',
            array(
                'integration_type'  => 'loyalty',
                'website_id'        => Mage::app()->getStore()->getWebsiteId(),
                'customer'          => $customer
            )
        );

        $quote    = Mage::helper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data, false);
        $options = $loyalty->cartRedeemOptions($quote);

        Varien_Profiler::stop('POS::' . __METHOD__);

        return array('reward_points_balance' => (float)$loyalty->getPointsBalance(), 'rules' => $options);

    }

    public function redeem() {

        Varien_Profiler::start('POS::' . __METHOD__);

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();
        $h = Mage::helper('bakerloo_restful/sales');
        $quote = $h->buildQuote($this->getStoreId(), $data, false);

        $cartData = $h->getCartData($quote);
        $quote->delete();

        Varien_Profiler::start('POS::' . __METHOD__);

        return $cartData;
    }

}