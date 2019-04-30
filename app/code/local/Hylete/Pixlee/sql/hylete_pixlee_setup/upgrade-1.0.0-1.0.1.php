<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'pixlee_album', array(
    'group'                    => 'General',
    'input'                    => 'textarea',
    'type'                     => 'varchar',
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

$installer->endSetup();
