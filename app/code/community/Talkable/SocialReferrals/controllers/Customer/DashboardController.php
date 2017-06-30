<?php
/**
 * Talkable SocialReferrals for Magento
 *
 * @package     Talkable_SocialReferrals
 * @author      Talkable (http://www.talkable.com/)
 * @copyright   Copyright (c) 2015 Talkable (http://www.talkable.com/)
 * @license     MIT
 */

class Talkable_SocialReferrals_Customer_DashboardController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        if (Mage::helper("socialreferrals")->isAdvocateDashboardEnabled()) {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_forward("defaultNoRoute"); // 404 page
        }
    }

}
