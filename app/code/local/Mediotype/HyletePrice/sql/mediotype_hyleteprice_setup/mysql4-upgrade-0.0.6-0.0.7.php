<?php


$installer = new Mage_Eav_Model_Entity_Setup();

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer->startSetup();

$installer->addAttribute(
	Mage_Catalog_Model_Product::ENTITY,
	'is_on_flash_sale',
	array(
		'group' => 'General',
		'input' => 'select',
		'type' => 'int',
		'label' => 'Is on Flash Sale',
		'source' => 'eav/entity_attribute_source_boolean',
		'backend' => '',
		'default' => 0,
		'visible' => true,
		'required' => false,
		'visible_on_front' => true,
		'user_defined' => true,
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'apply_to' => 'simple,configurable'
	)
);

$installer->endSetup();
