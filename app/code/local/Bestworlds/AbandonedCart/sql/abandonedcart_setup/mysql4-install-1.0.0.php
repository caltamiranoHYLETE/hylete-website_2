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
$this->startSetup();
$this->getConnection()
    ->addColumn($this->getTable('sales/quote'), 'email_captured_from', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 50,
        'default'   => '',
        'comment'   => 'Describe how the email address was captured to the cart.'
    ));
$this->endSetup(); 