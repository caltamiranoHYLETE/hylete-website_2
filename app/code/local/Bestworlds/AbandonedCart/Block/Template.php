<?php
/**
 * Best Worlds
 * http://www.bestworlds.com
 * 888-751-5348
 * 
 * Need help? contact us:
 *  http://www.bestworlds.com/contact-us
 * 
 * Want to customize or need help with your store?
 *  Phone: 888-751-5348
 *  Email: info@bestworlds.com
 *
 * @category    Bestworlds
 * @package     Bestworlds_AbandonedCart
 * @copyright   Copyright (c) 2018 Best Worlds
 * @license     http://www.bestworlds.com/software_product_license.html
 */

class Bestworlds_AbandonedCart_Block_Template extends Mage_Core_Block_Template 
{
    public function getAction()
    {
        return Mage::getBaseUrl().'abandonedcart/main/registeremail';
    }

    public function getEmailQuote()
    {
        if (Mage::getModel('core/cookie')->get('bw_lightbox_off')) return true;
        if ($this->getQuote()->getId() && $this->getQuote()->getItemsCount() &&  !$this->getQuote()->getCustomerEmail()) return false;
        return true;
    }

    public function getLogin()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}
