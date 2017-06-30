<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$oldMagento = Icommerce_Default::getMagentoVersion() < 1600 || !method_exists($installer, 'getFkName');

$installer->startSetup();

$tableName = $installer->getTable('matrixratesmodifier/matrixratesmodifier');

$definition = "varchar(255) DEFAULT '' COMMENT 'Short description'";
if (!$oldMagento) {
    $definition = array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => false,
            'length' => '255',
            'comment' => 'Short description'
    );
}

$installer->getConnection()->addColumn($tableName, 'short_description', $definition);

$installer->endSetup();