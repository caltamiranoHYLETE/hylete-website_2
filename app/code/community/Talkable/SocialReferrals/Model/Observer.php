<?php
/**
 * Talkable SocialReferrals for Magento
 *
 * @package     Talkable_SocialReferrals
 * @author      Talkable (http://www.talkable.com/)
 * @copyright   Copyright (c) 2015 Talkable (http://www.talkable.com/)
 * @license     MIT
 */

class Talkable_SocialReferrals_Model_Observer extends Mage_Core_Model_Abstract
{

    public function customerDashboardLink(Varien_Event_Observer $observer)
    {
        if (Mage::helper("socialreferrals")->isAdvocateDashboardEnabled()) {
            $observer->getEvent()->getLayout()->getUpdate()->addHandle("customer_dashboard_handle");
        }
    }

}
