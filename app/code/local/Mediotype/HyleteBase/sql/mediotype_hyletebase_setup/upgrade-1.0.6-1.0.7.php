<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description', [
    'group' => 'Images and text',
    'type' => 'varchar',
    'label' => 'Short Description',
    'input' => 'textarea',
    'default' => '',
    'sort_order' => 1,
    'required' => false,
    'wysiwyg_enabled' => true,
    'visible_on_front' => true,
    'is_html_allowed_on_front' => true,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
]);


$attribute = $installer->getAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description');
$attributeSetId = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Category::ENTITY)->getDefaultAttributeSetId();
$installer->addAttributeToGroup(Mage_Catalog_Model_Category::ENTITY, $attributeSetId, 'Images and text', $attribute['attribute_id'], '6');
$installer->endSetup();
