<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

/**
 * Installer
 */
/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Add product attributes to track subscription products
 */
if (!$installer->getAttributeId('catalog_product', 'subscription_enabled')) {
    $installer->addAttribute('catalog_product', 'subscription_enabled', array(
        'group' => 'Subscribe Pro',
        'label' => 'Subscription Enabled',
        'type' => 'int',
        'input' => 'select',
        'default' => '0',
        'class' => '',
        'backend' => '',
        'frontend' => '',
        'source' => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'apply_to' => 'simple,bundle,configurable,virtual,downloadable',
        'user_defined' => false,
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'visible_in_advanced_search' => false,
        'unique' => false
    ));
}

/**
 * Add attributes to customer
 */
$entityTypeId     = $installer->getEntityTypeId('customer');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('customer', 'subscribe_pro_customer_id', array(
    'label'             => 'Subscribe Pro Customer ID',
    'type'              => 'varchar',
    'input'             => 'text',
    'backend'           => '',
    'global'            =>  Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'source'            => '',
    'visible'           => true,
    'required'          => false,
    'default'           => '',
    'frontend'          => '',
    'unique'            => false,
    'note'              => ''
));

/** @var Mage_Eav_Model_Config $eavConfig */
$eavConfig = Mage::getSingleton('eav/config');
$spCustomerIdAttribute = $eavConfig->getAttribute('customer', 'subscribe_pro_customer_id');

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'subscribe_pro_custom_id',
    '999'  //sort_order
);

$spCustomerIdAttribute
    ->setData('used_in_forms', array(
        'adminhtml_customer',
    ))
    ->setData('is_used_for_customer_segment', true)
    ->setData('is_system', 0)
    ->setData('is_user_defined', 1)
    ->setData('is_visible', 1)
    ->setData('sort_order', 100)
;
$spCustomerIdAttribute->save();

/**
 * Add attributes to quote
 */
$installer->addAttribute('quote', 'subscribe_pro_custom_shipping_price', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'visible'  => false,
    'required' => false
));

/**
 * Add attributes to order item
 */
$installer->addAttribute('order_item', 'item_fulfils_subscription', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'visible'  => true,
    'required' => false
));
$installer->addAttribute('order_item', 'subscription_id', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
));
$installer->addAttribute('order_item', 'subscription_interval', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
));
$installer->addAttribute('order_item', 'subscription_reorder_ordinal', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'visible'  => false,
    'required' => false
));
$installer->addAttribute('order_item', 'subscription_next_order_date', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_DATE,
    'visible'  => false,
    'required' => false
));

/**
 * Add attributes to quote item
 */
$installer->addAttribute('quote_item', 'item_fulfils_subscription', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'visible'  => true,
    'required' => false
));
$installer->addAttribute('quote_item', 'subscription_id', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
));
$installer->addAttribute('quote_item', 'subscription_interval', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
));
$installer->addAttribute('quote_item', 'subscription_reorder_ordinal', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'visible'  => false,
    'required' => false
));
$installer->addAttribute('quote_item', 'subscription_next_order_date', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_DATE,
    'visible'  => false,
    'required' => false
));

/**
 * Add attributes to quote item - Front-end use on initial subscription order
 */
$installer->addAttribute('quote_item', 'create_new_subscription_at_checkout', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'visible'  => true,
    'required' => false
));
$installer->addAttribute('quote_item', 'new_subscription_interval', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
));


/**
 * Clean up installer
 */
$installer->endSetup();

