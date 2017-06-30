<?php
/**
 * Talkable SocialReferrals for Magento
 *
 * @package     Talkable_SocialReferrals
 * @author      Talkable (http://www.talkable.com/)
 * @copyright   Copyright (c) 2015 Talkable (http://www.talkable.com/)
 * @license     MIT
 */

class Talkable_SocialReferrals_Block_Purchase extends Mage_Checkout_Block_Onepage_Success
{

    public function getCheckoutOrder()
    {
        if ($this->getOrderId()) {
            return Mage::getModel("sales/order")->loadByIncrementId($this->getOrderId());
        }
    }

}
