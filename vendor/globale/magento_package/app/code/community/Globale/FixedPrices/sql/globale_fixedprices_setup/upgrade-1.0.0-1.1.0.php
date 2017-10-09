<?php
/**
 * Installation script for the Global-e FixedPrices
 */
$Installer = $this;
$Installer->startSetup();

$Installer->getConnection()->changeColumn(
    $Installer->getTable('globale_fixedprices'),
    'product_id',
    'product_code',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 100,
        'comment' => 'Product SKU from Magento'
    )
);

$Installer->endSetup();