<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('customcss/customcss')};
    CREATE TABLE {$this->getTable('customcss/customcss')} (
    `customcss_id` smallint(6) unsigned NOT NULL auto_increment COMMENT 'Custom Css ID',
    `code` text NULL default '' COMMENT 'CSS Code',
    `filename` varchar(100) NOT NULL COMMENT 'Filename',
    `version_hash` varchar(100) NOT NULL COMMENT 'Version Hash',
    `creation_time` timestamp NULL default NULL COMMENT 'Creation Time',
    `update_time` timestamp NULL default NULL COMMENT 'Modification Time',
    `is_active` smallint(6) NOT NULL default '1' COMMENT 'CSS Code Active',
    PRIMARY KEY (`customcss_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Custom CSS Table';
");

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('customcss/customcss_store')};
    CREATE TABLE {$this->getTable('customcss/customcss_store')} (
      `customcss_id` smallint(6) NOT NULL COMMENT 'Custom CSS ID',
      `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
      PRIMARY KEY (`customcss_id`,`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Custom CSS To Store Linkage Table';
");

$installer->getConnection()->addIndex(
        $installer->getTable('customcss/customcss_store'),
        $installer->getIdxName('customcss/customcss_store', array('store_id')),
        array('store_id')
);

/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
        $installer->getFkName('customcss/customcss_store', 'customcss_id', 'customcss/customcss', 'customcss_id'),
        $installer->getTable('customcss/customcss_store'),
        'customcss_id',
        $installer->getTable('customcss/customcss'),
        'customcss_id'
);

$installer->getConnection()->addForeignKey(
        $installer->getFkName('customcss/customcss_store', 'store_id', 'core/store', 'store_id'),
        $installer->getTable('customcss/customcss_store'),
        'store_id',
        $installer->getTable('core/store'),
        'store_id'
);

$installer->endSetup();