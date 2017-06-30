<?php

$installer = $this;

$installer->startSetUp();

$installer->run("
  CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_backup_files')}` (
    `id` int (11) unsigned NOT NULL auto_increment,
    `device_key` varchar(50) NOT NULL default '',
    `device_name` varchar(50) NOT NULL default '',
    `backup_file_name` varchar(100) NOT NULL default '',
    `storage` enum('magento', 'dropbox', 'drive') NOT NULL,
    `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY(`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Backup files'
");

$installer->endSetUp();