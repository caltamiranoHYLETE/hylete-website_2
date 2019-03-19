<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->delete($this->getTable('core/config_data'), [
    'path like ?' => '%design/theme/%',
    'scope = ?' => 'websites',
    'scope_id = ?' => 2
]);


$installer->setConfigData('design/theme/template', 'marketing', 'websites', 2);
$installer->setConfigData('design/theme/default', 'b2b', 'websites', 2);

$installer->endSetup();
