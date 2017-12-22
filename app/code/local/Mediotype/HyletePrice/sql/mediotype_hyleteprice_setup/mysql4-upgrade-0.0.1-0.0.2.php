<?php

$installer = new Mage_Catalog_Model_Resource_Setup();
$installer->startSetup();

$installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'is_hylete_price_clearance', array(
	'group' => 'General Information',
	'input' => 'select',
	'type' => 'yesno',
	'label' => 'Is this a Hylete Price Clearance category?',
	'source' => 'eav/entity_attribute_source_boolean',
	'backend' => '',
	'default' => 0,
	'visible' => true,
	'required' => false,
	'visible_on_front' => true,
	'user_defined' => true,
	'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$installer->endSetup();
