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

/**
 * Capture types model
 *
 * @category   Bestworlds
 * @package    Bestworlds_AbandonedCart
 * @author     Best Worlds Team <info@bestworlds.com>
 */
class Bestworlds_AbandonedCart_Model_Capturetypes extends Mage_Core_Model_Abstract 
{
    const DURING_CHECKOUT   = 'Checkout';
    const LEAD_MAGNET       = 'LeadMagnet';
    const EMAIL_MARKETING   = 'EmailMarketing';
    const ADD2CARTPROMPT    = 'Add2CartPrompt';
    const OUIBOUNCE         = 'Ouibounce';
    const LOGGED_IN         = 'LoggedIn';

    public function _construct()
    {
        parent::_construct();
        $this->_init('abandonedcart/capturetypes');
    }
}
