<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$content = <<<EOD
<p class="login_registration"><strong>HYLETE account perks:</strong></p> 
<ul class="login_registration"> 
   <li>earn <a href="{{store url=''}}points" class="blue-link">HYLETE points</a> on your orders</li> 
   <li>access exclusive offers</li> 
   <li>100% fit guarantee + free U.S. return shipping</li> 
   <li>quick checkout + saved order history</li> 
</ul>
EOD;

$block = Mage::getModel('cms/block');
$block->setTitle('Registration Page HYLETEnation Top');
$block->setIdentifier('registration_page_hyletenation_top');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($content);
$block->save();
$installer->endSetup();
