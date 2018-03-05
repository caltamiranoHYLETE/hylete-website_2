<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('shippingoverride2'), 'vendor', "int (11) unsigned");

$installer->getConnection()->addIndex(
    $installer->getTable('shippingoverride2'),
    $installer->getIdxName(
        'shippingoverride2',
        array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
    ),
    array('website_id', 'dest_country_id', 'dest_region_id', 'dest_zip'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
);

$installer->endSetup();