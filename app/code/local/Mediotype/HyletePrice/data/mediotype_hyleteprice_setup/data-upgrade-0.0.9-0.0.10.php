<?php

/**
 * Update customer groups labels, special price labels to include "Price" suffix
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
$valueTable         = $installer->getTable('eav/attribute_option_value');

$schedule           = array(
    'attributes' => array(
        'special_price_label' => array(
            'Back It'   => 'Back It Price',
            'Try It'    => 'Try It Price',
        ),
    ),
    'groups' => array(
        'Admin/Testing'             => 'HYLETE price',
        'Everyday Athlete'          => 'HYLETE price',
        'Employee'                  => 'HYLETE price',
        'HYLETE Investors'          => 'investor price',
        'Influencer/Compete Team'   => 'team price',
        'Influencer/Gym Owner'      => 'team price',
        'Influencer/Service League' => 'team price',
        'Influencer/Train Team'     => 'team price',
        'NASM'                      => 'team price',
        'NOT LOGGED IN'             => 'HYLETE price',
        'Pro Deal'                  => 'team price',
    ),
);

$installer->startSetup();

/**
 * Update Special Price Label options
 */

foreach ($schedule['attributes'] as $attributeCode => $values) {
    $valueCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
        ->setAttributeFilter($installer->getAttribute($entityType, $attributeCode, 'attribute_id'))
        ->setStoreFilter(0);

    foreach ($values as $label => $newLabel) {
        $value = $valueCollection->getItemByColumnValue('default_value', $label);

        if ($value) {
            $connection->update(
                $valueTable,
                array('value' => $newLabel),
                $connection->quoteInto('(option_id = ? AND store_id = 0)', $value->getOptionId())
            );
        }
    }
}

Mage::log('Updated special price labels.', Zend_Log::DEBUG, 'system.log', true);

/**
 * Update customer group price labels
 */

foreach ($schedule['groups'] as $groupCode => $newLabel) {
    $group = Mage::getModel('customer/group')->load($groupCode, 'customer_group_code');

    if (is_numeric($group->getId())) {
        $group->setCustomerGroupHyletePriceLabel($newLabel)->save();
    }
}

Mage::log('Updated customer group price labels.', Zend_Log::DEBUG, 'system.log', true);

$installer->endSetup();
