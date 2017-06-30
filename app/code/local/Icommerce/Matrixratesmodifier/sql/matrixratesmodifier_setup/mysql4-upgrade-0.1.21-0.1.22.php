<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$oldMagento = Icommerce_Default::getMagentoVersion() < 1600 || !method_exists($installer, 'getFkName');

$installer->startSetup();

$tableName = $installer->getTable('matrixratesmodifier/matrixratesmodifier');

$definition = "varchar(255) DEFAULT NULL COMMENT 'Field to store shipping logo'";
if (!$oldMagento) {
    $definition = array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'length' => '255',
            'comment' => 'Field to store shipping logo.'
    );
}

$installer->getConnection()->addColumn($tableName, 'logo', $definition);

$installer->endSetup();