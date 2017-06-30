<?php
/**
 * Talkable SocialReferrals for Magento
 *
 * @package     Talkable_SocialReferrals
 * @author      Talkable (http://www.talkable.com/)
 * @copyright   Copyright (c) 2015 Talkable (http://www.talkable.com/)
 * @license     MIT
 */

class Talkable_SocialReferrals_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        if (Mage::helper("socialreferrals")->isInviteEnabled()) {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_forward("defaultNoRoute"); // 404 page
        }
    }

}
