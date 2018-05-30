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
 * Adminhtml report block
 *
 * @category   Bestworlds
 * @package    Bestworlds_AbandonedCart
 * @author     Best Worlds Team <info@bestworlds.com>
 */
class Bestworlds_AbandonedCart_Block_Adminhtml_Reports extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    const CLOSETIME = 3600;

    public function __construct() 
    {
        $this->_controller = 'adminhtml_reports';
        $this->_blockGroup = 'abandonedcart';
        $this->_headerText = Mage::helper('abandonedcart')->__('Abandoned Cart Reports');
        parent::__construct();
        $this->_removeButton('add');
    }
}