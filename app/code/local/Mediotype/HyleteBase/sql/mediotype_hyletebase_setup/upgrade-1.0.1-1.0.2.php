<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->updateAttribute('catalog_product', 'rollover_image', 'used_in_product_listing', '1');
$installer->updateAttribute('catalog_product', 'rollover_image', 'is_visible_on_front', '1');

$installer->endSetup();
