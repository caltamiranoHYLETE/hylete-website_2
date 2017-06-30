<?php
/**
 * Talkable SocialReferrals for Magento
 *
 * @package     Talkable_SocialReferrals
 * @author      Talkable (http://www.talkable.com/)
 * @copyright   Copyright (c) 2015 Talkable (http://www.talkable.com/)
 * @license     MIT
 */

class Talkable_SocialReferrals_Block_Multishipping_Purchase extends Mage_Checkout_Block_Multishipping_Success
{

    public function getCheckoutOrder()
    {
        return Mage::getResourceModel("sales/order_collection")
                   ->addFieldToSelect("*")
                   ->addFieldToFilter("customer_id", Mage::getSingleton("customer/session")->getCustomer()->getId())
                   ->setOrder("created_at", "DESC")
                   ->getFirstItem();
    }

}
