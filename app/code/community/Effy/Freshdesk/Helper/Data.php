<?php
/**
 * Effy_Freshdesk extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Freshdesk
 * @package        Effy_Freshdesk
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Freshdesk default helper
 *
 * @category    Freshdesk
 * @package     Effy_Freshdesk
 * @author      Ultimate Module Creator
 */
class Effy_Freshdesk_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function isConfigSet()
    {
        return $this->getDomain() !== '' && $this->getAdminEmail() !== '' && ($this->getPassword() !== '' || $this->getAdminApiKey() !== '');
    }

    public function getDomain()
    {
        return trim(Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_ACCOUNT_DOMAIN));
    }

    public function getAdminEmail()
    {
        return trim(Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_ACCOUNT_EMAIL));
    }

    public function getAdminApiKey()
    {
        return trim(Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_ACCOUNT_API_KEY));
    }

    public function getPassword()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_ACCOUNT_PASSWORD);
    }

    public function isSSOEnabled()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_SSO_ENABLED);
    }

    public function getSSOSecret()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_SSO_SECRET);
    }

    public function getSSOLoginUrl()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_SSO_LOGIN_URL);
    }

    public function getSSOLogoutUrl()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_SSO_LOGOUT_URL);
    }

    public function getOrderIdField()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_ORDERS_ORDER_ID);
    }

    public function isContactUsFormEnabled()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_CHANNELS_CONTACT_US_ENABLED) == 1;
    }

    public function isFeedbackWidgetEnabled()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_CHANNELS_FEEDBACK_WIDGET_ENABLED) == 1;
    }

    public function getFeedbackWidgetCode()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_CHANNELS_FEEDBACK_WIDGET_CODE);
    }

    public function isSupportLinkEnabled()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_CHANNELS_ENABLE_SUPPORT_LINK) == 1;
    }

    public function getSupportLink()
    {
        try {
            return Mage::getSingleTon('effy_freshdesk/freshdesk')->getUrl();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return 'http://www.freshdesk.com';
    }

    public function isCustomerViewEnabled()
    {
        return Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_CUSTOMER_VIEW_ENABLE_CUSTOMER_VIEW) == 1;
    }

    public function isTicketTabEnabled()
    {
        return $this->isCustomerViewEnabled();
        /*&& Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_CUSTOMER_VIEW_ENABLE_TICKET_TAB) == 1;*/
    }

    public function isRecentTicketsGridEnabled()
    {
        return $this->isCustomerViewEnabled()
        && Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_CUSTOMER_VIEW_ENABLE_RECENT_TICKET) == 1;
    }

    public function getPriorityDefault()
    {
        return (int)Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_TICKETS_PRIORITY);
    }

    public function getStatusDefault()
    {
        return (int)Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_TICKETS_STATUS);
    }

    public function getStatusClose()
    {
        return (int)Mage::getStoreConfig(Effy_Freshdesk_Helper_Const::XML_TICKETS_STATUS_CLOSE);
    }

    public function getCreateOrderTicketUrl(Mage_Sales_Model_Order $order)
    {
        return $this->_getUrl('freshdesk/ticket/create', array('order_id' => $order->getId()));
    }

    public function setCurrentTicket($ticket)
    {
        Mage::unregister(Effy_Freshdesk_Helper_Const::REGISTER_CURRENT_FRESHDESK_TICKET);
        Mage::register(Effy_Freshdesk_Helper_Const::REGISTER_CURRENT_FRESHDESK_TICKET, $ticket);
    }

    public function getCurrentTicket()
    {
        return Mage::registry(Effy_Freshdesk_Helper_Const::REGISTER_CURRENT_FRESHDESK_TICKET);
    }

    public function getCurrentUser()
    {
        if (null === ($user = Mage::registry(Effy_Freshdesk_Helper_Const::REGISTER_CURRENT_FRESHDESK_USER))) {
            Mage::unregister(Effy_Freshdesk_Helper_Const::REGISTER_CURRENT_FRESHDESK_USER);

            if (Mage::getSingleton('customer/session')->getCustomer()) {
                $user = Mage::getModel('effy_freshdesk/user')->load(Mage::getSingleton('customer/session')->getCustomer()->getEmail());
            } else {
                $user = false;
            }

            Mage::register(Effy_Freshdesk_Helper_Const::REGISTER_CURRENT_FRESHDESK_USER, $user);
        }

        return $user;
    }
    
    /**
     * convert array to options
     *
     * @access public
     * @param $options
     * @return array
     * @author Ultimate Module Creator
     */
    public function convertOptions($options)
    {
        $converted = array();
        foreach ($options as $option) {
            if (isset($option['value']) && !is_array($option['value']) &&
                isset($option['label']) && !is_array($option['label'])) {
                $converted[$option['value']] = $option['label'];
            }
        }
        return $converted;
    }

    public function getOrderDetail($order)
    {
        // if the admin site has a custom URL, use it
        $urlModel = Mage::getModel('adminhtml/url')->setStore('admin');
        $shippingAddress = Mage::getModel('sales/order_address')->load($order->getShippingAddressId())->getData();
        $shippingData = array(
            'region' => $shippingAddress['region'], 
            'postcode' => $shippingAddress['postcode'], 
            'lastname' => $shippingAddress['lastname'], 
            'street' => $shippingAddress['street'], 
            'city' => $shippingAddress['city'], 
            'email' => $shippingAddress['email'], 
            'telephone' => $shippingAddress['telephone'], 
            'country_id' => $shippingAddress['country_id'], 
            'firstname' => $shippingAddress['firstname'], 
            'prefix' => $shippingAddress['prefix'], 
            'suffix' => $shippingAddress['suffix'], 
            'middlename' => $shippingAddress['middlename'], 
            'company' => $shippingAddress['company']
        );
        $orderInfo = array(
            'id' => $order->getIncrementId(),
            'status' => $order->getStatus(),
            'created' => $order->getCreatedAt(),
            'updated' => $order->getUpdatedAt(),
            'customer' => array(
                'name' => $order->getCustomerName(),
                'email' => $order->getCustomerEmail(),
                'ip' => $order->getRemoteIp(),
                'guest' => (bool)$order->getCustomerIsGuest(),
            ),
            'store' => $order->getStoreName(),
            "discount_amount" => $order->getDiscountAmount(),
            "delivery_method" => $order->getShippingMethod(),
            "sub_total" => $order->getBaseGrandTotal(),
            'grand_total' => $order->getGrandTotal(),
            'total_paid' => $order->getTotalPaid(),
            'total' => $order->getGrandTotal(),
            'currency' => $order->getOrderCurrencyCode(),
            'items' => array(),
            'shippingAddress' => $shippingData,
            'shippingCharges' => (float)$order->getShippingAmount(),
            'admin_url' => $urlModel->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId())),
        );

        foreach($order->getItemsCollection(array(), true) as $item) {
            $orderInfo['items'][] = array(
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'quantity' => (int) $item->getQtyOrdered(),
                'price' => (float) $item->getPrice(),
                'tax' => (float) $item->getTaxAmount()
            );
        }

        return $orderInfo;
    }
}
