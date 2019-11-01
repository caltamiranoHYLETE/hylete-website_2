<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Block_Adminhtml_Customer_Edit_Tab_View_Tickets
 */
class Effy_Freshdesk_Block_Adminhtml_Customer_Edit_Tab_View_Tickets extends Mage_Adminhtml_Block_Customer_Edit_Tab_View_Accordion
{
    protected function _prepareLayout()
    {
        /** @var Mage_Adminhtml_Block_Customer_Edit_Tab_View_Accordion $accordion */
        $accordion = $this->getLayout()->getBlock('accordion');
        if ($accordion instanceof Mage_Adminhtml_Block_Customer_Edit_Tab_View_Accordion) {

            $customer = Mage::registry('current_customer');

            $accordion->setId('customerViewAccordion');

            $accordion->addItem('lastOrders', array(
                'title'       => Mage::helper('customer')->__('Recent Orders'),
                'ajax'        => true,
                'content_url' => $this->getUrl('*/*/lastOrders', array('_current' => true)),
            ));

            // add shopping cart block of each website
            foreach (Mage::registry('current_customer')->getSharedWebsiteIds() as $websiteId) {
                $website = Mage::app()->getWebsite($websiteId);

                // count cart items
                $cartItemsCount = Mage::getModel('sales/quote')
                    ->setWebsite($website)->loadByCustomer($customer)
                    ->getItemsCollection(false)
                    ->addFieldToFilter('parent_item_id', array('null' => true))
                    ->getSize();
                // prepare title for cart
                $title = Mage::helper('customer')->__('Shopping Cart - %d item(s)', $cartItemsCount);
                if (count($customer->getSharedWebsiteIds()) > 1) {
                    $title = Mage::helper('customer')->__('Shopping Cart of %1$s - %2$d item(s)', $website->getName(), $cartItemsCount);
                }

                // add cart ajax accordion
                $accordion->addItem('shopingCart' . $websiteId, array(
                    'title'   => $title,
                    'ajax'    => true,
                    'content_url' => $this->getUrl('*/*/viewCart', array('_current' => true, 'website_id' => $websiteId)),
                ));
            }

            // count wishlist items
            $wishlistCount = Mage::getModel('wishlist/item')->getCollection()
                ->addCustomerIdFilter($customer->getId())
                ->addStoreData()
                ->getSize();
            // add wishlist ajax accordion
            $accordion->addItem('wishlist', array(
                'title' => Mage::helper('customer')->__('Wishlist - %d item(s)', $wishlistCount),
                'ajax'  => true,
                'content_url' => $this->getUrl('*/*/viewWishlist', array('_current' => true)),
            ));

            if(Mage::helper('freshdesk')->isRecentTicketsGridEnabled()) {
                $accordion->addItem('freshdesk_tickets', array(
                    'title'       => Mage::helper('freshdesk')->__('Recent Tickets'),
                    'ajax'        => true,
                    'content_url' => $this->getUrl('*/freshdesk/customerView', array('_current' => true)),
                ));
            }
        } else {
            //Mage::getSingleton('adminhtml/session')->addWarning("Can't get parent block for Recent Tickets");
            Mage::logException(new Effy_Freshdesk_Exception("Can't get parent block for Recent Tickets"));
        }
    }
}
