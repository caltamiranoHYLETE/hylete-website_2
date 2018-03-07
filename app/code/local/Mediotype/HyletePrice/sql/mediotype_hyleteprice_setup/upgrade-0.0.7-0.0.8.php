<?php

/**
 * Replace flash sale product attribute.
 * 
 * - Collect existing flash sale attribute values
 * - Remove flash sale attribute
 * - Create replacement special price label attribute
 * - Import collected flash sale values to replacement
 * 
 * @category  Setup
 * @package   Mediotype_HyletePrice
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer          = $this;
/* @var $connection Varien_Db_Adapter_Interface */
$connection         = $installer->getConnection();
$entityType         = Mage_Catalog_Model_Product::ENTITY;
$productModel       = Mage::getModel('catalog/product');
$attributeModel     = Mage::getModel('eav/entity_attribute');
$oldAttribute       = 'is_on_flash_sale';
$newAttribute       = 'special_price_label';
$attributeSetIds    = $installer->getAllAttributeSetIds($entityType);

$installer->startSetup();

// Collect existing flash sale attribute values

$attributeId    = $installer->getAttribute($entityType, $oldAttribute, 'attribute_id');
$table          = $installer->getAttributeTable($entityType, $oldAttribute);
$select         = $connection->select()
    ->from($table, array('entity_id', 'value'))
    ->where('attribute_id = ?', $attributeId);

$existingValues = $connection->fetchAll($select);

// Remove flash sale attribute

$installer->removeAttribute($entityType, $oldAttribute);

Mage::log(sprintf('Removed attribute `%s`', $oldAttribute), Zend_Log::DEBUG, 'system.log', true);

// Create replacement special price label attribute

$installer->addAttribute(
    $entityType,
    $newAttribute,
    array(
        'group'             => 'Prices',
        'input'             => 'select',
        'type'              => 'int',
        'label'             => 'Special Price Label',
        'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
        'visible'           => true,
        'required'          => false,
        'visible_on_front'  => true,
        'user_defined'      => true,
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'apply_to'          => null,
        'option'            => array(
            'values' => array(
                'Flash Sale',
                'Back It',
                'Try It',
            ),
        ),
    )
);

$attributeModel->load(
    $installer->getAttribute($entityType, $newAttribute, 'attribute_id')
);

$flashSaleValueId = $attributeModel->getSource()->getOptionId('Flash Sale');

if (empty($flashSaleValueId)) {
    throw new Exception('Failed to locate target option value ID.');
}

foreach ($attributeSetIds as $setId) {
    $installer->addAttributeToSet(
        $entityType,
        $setId,
        'Prices',
        2
    );
}

Mage::log(sprintf('Installed attribute `%s`', $newAttribute), Zend_Log::DEBUG, 'system.log', true);

// Import collected flash sale values to replacement

foreach ($existingValues as $existingValue) {
    $productModel->reset()
        ->setId($existingValue['entity_id'])
        ->setStoreId(0)
        ->setData($newAttribute, ((int) $existingValue['value'] > 0 ? $flashSaleValueId : null))
        ->getResource()
        ->saveAttribute($productModel, $newAttribute);

    Mage::log(
        sprintf(
            'Migrated value %s to `%s` for product %d',
            $productModel->getData($newAttribute) ?: 'NULL',
            $newAttribute,
            $productModel->getId()
        ),
        Zend_Log::DEBUG,
        'system.log',
        true
    );
}

$installer->endSetup();
