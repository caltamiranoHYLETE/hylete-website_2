<?php
/**
 * HiConversion
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * [http://opensource.org/licenses/MIT]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category Hic
 * @package Hic_Integration
 * @Copyright © 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

/**
 * Integration data model
 *
 * @category Hic
 * @package Integration
 * @author HiConversion <support@hiconversion.com>
 */
class Hic_Integration_Model_Data extends Varien_Object
{
    const CATALOG_URL = 'catalog/product/';
    
    /**
     * Class constructor
     */
    protected function _construct()
    {
    }
    
    
    /**
     * Returns category names for each product
     * passed into function
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array $categoryNames
     */
    protected function _getCategoryNames($product)
    {
        $catIds =  $product->getCategoryIds();
        if (empty($catIds)) {
            return null;
        }
        $catCollection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToFilter('entity_id', $catIds)
            ->addAttributeToSelect('name')
            ->addIsActiveFilter();
        $categoryNames = array();
        foreach ($catCollection as $category) {
            $categoryNames[] = $category->getName();
        }
        return array_unique($categoryNames);
    }

    /**
     * Returns currency information
     *
     * @return array $currencyInfo
     */
    protected function _getCurrencyInfo()
    {
        $currencyInfo = array();

        if (Mage::app()->getStore()->getCurrentCurrencyCode()) {
            $currencyInfo['cu'] = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        if (Mage::app()->getStore()->getBaseCurrencyCode()) {
            $currencyInfo['bcu'] = Mage::app()->getStore()->getBaseCurrencyCode();
        }
        if (Mage::app()->getStore()->getCurrentCurrencyRate()) {
            $currencyInfo['cr'] = Mage::app()->getStore()->getCurrentCurrencyRate();
        }
    
        return $currencyInfo;
    }

    /**
     * Get item options for

     * @param array $options
     * @return array
     */
    public function _getItemOptions($options)
    {
        $result = array();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }
    
    /**
     * Get list of all options for product
     
     * @param Mage_Sales_Model_Quote_Item $item
     * @params boolean $isOrder
     * @return array
     */
    protected function _getOptionList($item, $isOrder)
    {
        $helper = Mage::helper('catalog/product_configuration');
        if ($isOrder) {
            $options = $item->getProductOptions();
            $options = $this->_getItemOptions($options);
        } else {
            $options = $helper->getConfigurableOptions($item);
        }
        $opts = array();
        foreach ($options as $option) {
          $formattedOptionValue = $helper->getFormattedOptionValue($option);
          $opts[$option['label']] = $formattedOptionValue['value'];
        }
        return $opts;
    }

    /**
     * Returns product for specified item
     * 
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string
     */
    protected function _getProduct($item) 
    {
        $product = $item->getProduct();
        if (is_null($product)) {
            if (!$item->getData('product')) {
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                $item->setProduct($product);
            }
        }
        return $product;
    }

    /**
     * Returns product url for specified product
     * 
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string
     */
    protected function _getProductUrl($item) 
    {
        $option  = $item->getOptionByCode('product_type');
        if ($option) {
            $product = $this->_getProduct($option);
        } else {
            $product = $this->_getProduct($item);
        }

        return $product->getUrlModel()->getUrl($product);
    }

    /**
     * Returns product information for each product
     * passed into function
     *
     * @param array $items
     * @params boolean $isOrder
     * @return array $data
     */
    protected function _getCartItems($items, $isOrder)
    {
        $data = array();

        foreach ($items as $item) {
            $product = $this->_getProduct($item);
           
            $info = array();
            $info['ds'] = (float)$item->getDiscountAmount();
            $info['tx'] = (float)$item->getTaxAmount();
            $info['pr'] = (float)$product->getFinalPrice();
            $info['bpr'] = (float)$product->getPrice();
            if ($isOrder) {
                $info['qt'] = (float)$item->getQtyOrdered();
            } else {
                $info['qt'] = (float)$item->getQty();
            }
            $info['id'] = $product->getId();
            $info['url'] = $this->_getProductUrl($item);
            $info['nm'] = $product->getName();
            $info['img'] = (string)Mage::helper('catalog/image')->init($product, 'thumbnail');
            $info['sku'] = $item->getSku();
            $info['cat'] = $this->_getCategoryNames($product);
            // need to get stock quantity from child product of configurable products
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            if ($stock) {
                $info['sq'] = (float)$stock->getQty();
            }

            
            $typeId = $product->getTypeId();
            if ($typeId == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                $info['opt'] = $this->_getOptionList($item, $isOrder);
            }
            
            $data[] = $info;

        }
        return $data;
    }
    
    /**
     * Determines and returns page route
     *
     * @return string
     */
    protected function _getRoute()
    {
        return Mage::app()
            ->getFrontController()
            ->getAction()
            ->getFullActionName();
    }
    
    /**
     * Determines if its a product page or not
     *
     * @return boolean
     */
    public function isProduct()
    {
        return 'catalog_product_view' == $this->_getRoute();
    }

    /**
     * Determines if Confirmation page or not
     *
     * @return boolean
     */
    public function isConfirmation()
    {
        $request = Mage::app()->getRequest();
        return false !== strpos($request->getRouteName(), 'checkout')
            && 'success' == $request->getActionName();
    }

    /**
     * Retrieves page route and breadcrumb info and populates page
     * attribute
     *
     * @return array $this
     */
    public function populatePageData()
    {
        $crumb = array();
        foreach (Mage::helper('catalog')->getBreadcrumbPath() as $item) {
            
            $crumb[] = $item['label'];
        }
        
        $this->setPage(
            array(
                'route' => $this->_getRoute(),
                'bc' => $crumb
            )
        );
        return $this;
    }

    /**
     * Retrieves cart information and populates cart attribute
     *
     * @return array $this
     */
    public function populateCartData()
    {
        $cart = Mage::getModel('checkout/cart');
        $cartQuote = $cart->getQuote();
        if ($cartQuote->getItemsCount() > 0) {
            $data = array();
            if ($cartQuote->getId()) {
                $data['id'] = (string)$cartQuote->getId();
            }
            if ($cartQuote->getSubtotal()) {
                $data['st'] = (float)$cartQuote->getSubtotal();
            }
            if ($cartQuote->getGrandTotal()) {
                $data['tt'] = (float)$cartQuote->getGrandTotal();
            }
            if ($cartQuote->getItemsCount()) {
                $data['qt'] = (float)$cartQuote->getItemsQty();
            }
            $data['cur'] = $this->_getCurrencyInfo();
            $data['li'] = $this
                ->_getCartItems($cartQuote->getAllVisibleItems(), false);
            $this->setCart($data);
        }
        return $this;
    }

    /**
     * Retrieves user information and populates user attribute
     *
     * @return array $this
     */
    public function populateUserData()
    {
        $session = Mage::helper('customer');
        $customer = $session->getCustomer();
        $data = array();
        if ($customer) {
            $data['auth'] = $session->isLoggedIn();
            $data['ht'] = false;
            $data['nv'] = true;
            $data['cur'] = $this->_getCurrencyInfo();
            $data['cg'] = Mage::getSingleton('customer/session')
                ->getCustomerGroupId();
            if ($customer->getId()) {
                $orders = Mage::getModel('sales/order')->getCollection();
                $orders->addAttributeToFilter('customer_id', $customer->getId());
                if ($orders) {
                    $ocnt = $orders->getSize();
                    if ($ocnt > 0) {
                        $data['ht'] = true;
                        $data['ocnt'] = $ocnt;  
                    }
                }
                if ($customer->getDob()) {
                    $dob = new DateTime($customer->getDob());
                    $data['by'] = $dob->format('Y');
                }
                if ($customer->getGender()) {
                    $data['gndr'] = $customer->getGender();
                }
                $data['id'] = $customer->getId();
                $data['nv'] = false;
                $data['since'] = $customer->getCreatedAt();
            }
            $this->setUser($data);
        }
        return $this;
    }

    /**
     * Retrieves order information and populates tr attribute
     *
     * @return array $this
     */
    public function populateOrderData()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if (!$orderId) {
            return false;
        }
        $order = Mage::getModel('sales/order')->load($orderId);
        $transaction = array();
        if ($order) {
            if ($order->getIncrementId()) {
                $transaction['id'] = $order->getIncrementId();
            }
            if ($order->getSubtotal()) {
                $transaction['st'] = (float)$order->getSubtotal();
            }
            if ($order->getTaxAmount()) {
                $transaction['tx'] = (float)$order->getTaxAmount();
            }
            if ($order->getPayment()->getMethodInstance()->getTitle()) {
                $transaction['type'] = $order->getPayment()->getMethodInstance()->getTitle();
            }
            $transaction['cur'] = $this->_getCurrencyInfo();
            $ccType = $order->getPayment()->getCcType();
            if ($ccType) {   
                $aType = Mage::getSingleton('payment/config')->getCcTypes(); 
                if (isset($aType[$ccType])) {
                    $transaction['ccType'] = $aType[$ccType];
                }
            }
            if ($order->getGrandTotal()) {
                $transaction['tt'] = (float)$order->getGrandTotal();
            }
            if ($order->getTotalQtyOrdered()) {
                $transaction['qt'] = (float)$order->getTotalQtyOrdered();
            }
            if ($order->getCouponCode()) {
                $transaction['coup'] = array($order->getCouponCode());
            }
            if ($order->getDiscountAmount()) {
                $transaction['ds'] = abs((float)$order->getDiscountAmount());
            }
            $transaction['li'] = $this
                ->_getCartItems($order->getAllVisibleItems(), true);
            $transaction['sh'] = (float)$order->getShippingAmount();
            $transaction['shm'] = $order->getShippingMethod()
                ? $order->getShippingMethod() : '';
            $this->setTr($transaction);
        }
        return $this;
    }

    /**
     * Retrieves product information and populates product attribute
     *
     * @return array $this
     */
    public function populateProductData()
    {
        // registry does not exist when we are cached
        if ($product = Mage::registry('current_product')) {
            $data['cat'] = $this->_getCategoryNames($product);
            $data['id']  = $product->getId();
            $data['nm']  = $product->getName();
            $data['desc'] = strip_tags($product->getDescription());
            $data['url'] = $product->getProductUrl();
            $data['sku'] = $product->getSku();
            $data['bpr'] = (float)$product->getPrice();
            $data['cur'] = $this->_getCurrencyInfo();
            $data['pr'] = (float)$product->getFinalPrice();
            
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            if ($stock) {
                $data['sq'] = (float)$stock->getQty();
            }
            
            $data['img'] = (string)Mage::helper('catalog/image')->init($product, 'thumbnail');
            $this->setProduct($data);
        }
        return $this;       
    }
    
    

}