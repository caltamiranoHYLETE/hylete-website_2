<?php
/**
 * @author    ${userEmail}
 * @copyright 2019 ${company}. All rights reserved.
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('customcss/customcss'),'name', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable'  => false,
        'length'    => 255,
        'after'     => null,
        'comment'   => 'Name or Area'
    ));

$installer->endSetup();
