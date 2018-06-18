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
$query = "INSERT INTO ".$this->getTable('core/config_data')." (`scope`, `scope_id`, `path`, `value`) VALUES ('default', '0', 'abandonedcart/basic/installed_time', now());";
$this->run($query);
$this->endSetup(); 