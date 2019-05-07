<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$connection = $installer->getConnection();
$installer->startSetup();

/*
 * Update type from varchar to text
 */
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'pixlee_album', array(
    'group'                    => 'General',
    'input'                    => 'textarea',
    'type'                     => 'text',
    'label'                    => 'Pixlee Album',
    'backend'                  => '',
    'visible'                  => true,
    'required'                 => false,
    'visible_on_front'         => true,
    'wysiwyg_enabled'          => true,
    'is_html_allowed_on_front' => true,
    'global'                   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'               => 4
));

$attributeId = $installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'pixlee_album');
$entityTypeId = $installer->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);

$varcharTable = $this->getTable(array('catalog/product', 'varchar'));
$textTable = $this->getTable(array('catalog/product', 'text'));
$cols = array('entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value');

/*
 * Transfer all data from the varchar table to the text table
 */
$select = $connection->select()
    ->from($varcharTable, $cols)
    ->where('attribute_id = ?', $attributeId)
    ->where('entity_type_id = ?', $entityTypeId);

$query = $select->insertFromSelect($textTable, $cols);
$connection->query($query);

/*
 * Delete the varchar table values
 */
$connection->delete($varcharTable, array(
    'attribute_id = ?' => $attributeId,
    'entity_type_id = ?' => $entityTypeId
));

$connection->query($query);
$installer->endSetup();
