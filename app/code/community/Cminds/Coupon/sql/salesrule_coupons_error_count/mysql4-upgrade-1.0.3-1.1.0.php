<?php
$installer=$this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('salesrule'),
        'errors_serialized',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => true,
            'default' => null,
            'comment' => 'Custom errors'
        )
    );
$installer->endSetup();
