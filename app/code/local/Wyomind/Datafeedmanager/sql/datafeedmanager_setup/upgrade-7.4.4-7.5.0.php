<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
        $installer->getTable('datafeedmanager_configurations'), 'use_sftp', array(
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable' => true,
    'length' => 1,
    'default' => 0,
    'comment' => 'Use sftp'
        )
);

$installer->endSetup();
