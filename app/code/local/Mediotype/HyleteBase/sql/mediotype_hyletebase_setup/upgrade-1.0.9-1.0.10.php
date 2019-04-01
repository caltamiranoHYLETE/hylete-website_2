<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Update category short_description attribute to text type.
 */
$installer->updateAttribute(
        Mage_Catalog_Model_Category::ENTITY,
        'short_description',
        'backend_type',
        'text'
    );

$installer->endSetup();
