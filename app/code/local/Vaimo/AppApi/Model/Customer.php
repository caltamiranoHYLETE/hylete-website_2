<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 * @author      Tobias Åström
 */

class Vaimo_AppApi_Model_Customer extends Vaimo_AppApi_Model_Abstract
{
    public function listDetails($customerId, $detailLevel)
    {
        $collection = Mage::getModel('customer/customer')->load($customerId);

        $details[$customerId] = $this->_getHelper()->getCustomerDetails($collection, $detailLevel)->toArray();

        if ($details) {
            $email = $collection->getEmail();
            $defaultBillingId = $collection->getDefaultBilling();
            $defaultShippingId = $collection->getDefaultShipping();

            $detailLevelExtended = ($detailLevel == Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT || $detailLevel == Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_FULL ? true : false);

            if ($email && $detailLevelExtended) {
                $details[$customerId]['is_subscribed'] = ($this->_getCustomerSubscription($email) ? 1 : 0);
            }
            if ($defaultBillingId && $detailLevelExtended) {
                $details[$customerId]['default_billing'] = $this->_getAddress($defaultBillingId)->toArray();
            }
            if ($defaultShippingId && $detailLevelExtended) {
                $details[$customerId]['default_shipping'] = $this->_getAddress($defaultShippingId)->toArray();
            }
        }

        return $details;
    }

    public function listAddressDetails($addressId, $detailLevel)
    {
        $details = array();

        $collection = $this->_getAddress($addressId);

        if ($collection) {
            $details[$addressId] = $this->_getHelper()->getAddressDetails($collection, $detailLevel)->toArray();
        }

        return $details;
    }

    public function listWishlistDetails($customerId, $detailLevel)
    {
        $wishlist = array();

        $customer = Mage::getModel('customer/customer');
        $customer->load($customerId);

        $collection = Mage::getSingleton('wishlist/wishlist')->loadByCustomer($customer);

        foreach ($collection->getItemCollection() as $item) {
            $wishlist[$item->getId()] = $this->_getHelper()->getWishlistDetails($item, $detailLevel)->toArray();
        }

        return $wishlist;
    }

    public function listOrders($customerId, $detailLevel)
    {
        $orders = array();

        $collection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('customer_id', $customerId);

        foreach ($collection->getItems() as $order) {
            $orders[$order->getId()] = $this->_getHelper()->getOrderDetails($order, $detailLevel)->toArray();
        }

        $detailLevelExtended = ($detailLevel == Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT || $detailLevel == Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_FULL ? true : false);

        if ($orders && $detailLevelExtended) {
            foreach ($this->_getOrdersAddresses(array_keys($orders)) as $orderId => $addresses) {
                $orders[$orderId]['addresses'] = $addresses;
            }
            foreach ($this->_getOrdersItems(array_keys($orders)) as $orderId => $items) {
                $orders[$orderId]['order_items'] = $items;
            }
            foreach ($this->_getOrdersComments(array_keys($orders)) as $orderId => $comments) {
                $orders[$orderId]['order_comments'] = $comments;
            }
        }

        return $orders;
    }

    protected function _getCustomerSubscription($email)
    {
        $collection = Mage::getModel('newsletter/subscriber')->loadByEmail($email);

        return $collection->isSubscribed();
    }

    protected function _getAddress($addressId)
    {
        $address = Mage::getModel('customer/address')->load($addressId);

        return $address;
    }

    protected function _getOrdersAddresses(array $orderIds)
    {
        $addresses = array();

        $collection = Mage::getResourceModel('sales/order_address_collection');

        $collection->addAttributeToFilter('parent_id', $orderIds);

        foreach ($collection->getItems() as $item) {
            $addresses[$item->getParentId()][] = $item->toArray();
        }

        return $addresses;
    }

    protected function _getOrdersItems(array $orderIds)
    {
        $items = array();

        $collection = Mage::getResourceModel('sales/order_item_collection');

        $collection->addAttributeToFilter('order_id', $orderIds);

        foreach ($collection->getItems() as $item) {
            $items[$item->getOrderId()][] = $item->toArray();
        }

        return $items;
    }

    protected function _getOrdersComments(array $orderIds)
    {
        $comments = array();

        foreach ($this->_getOrdersCommentsCollection($orderIds)->getItems() as $item) {
            $comments[$item->getParentId()][] = $item->toArray();
        }

        return $comments;
    }

    protected function _getOrdersCommentsCollection(array $orderIds)
    {
        $collection = Mage::getResourceModel('sales/order_status_history_collection');
        $collection->setOrderFilter($orderIds);

        return $collection;
    }

    public function getCustomerList($websiteId)
    {
        $dataArray = array();
        $collection = Mage::getModel('customer/customer')->getCollection();
        $collection->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname');

        if ($websiteId != 0) {
            $collection->addFieldToFilter('website_id',$websiteId);
        }
        $this->_getHelper()->dispatchUpdateEventObject('app_api_customer_list_collection', $collection, array('collection' => $collection));

        foreach ($collection as $customer) {
            $dataArray[] = $customer->toArray();
        }

        return $dataArray;
    }
}