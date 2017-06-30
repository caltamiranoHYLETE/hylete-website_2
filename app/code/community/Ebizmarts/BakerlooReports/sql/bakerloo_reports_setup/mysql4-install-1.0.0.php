<?php


$installer = $this;
$installer->startSetup();

$installer->run("
  CREATE TABLE IF NOT EXISTS {$installer->getTable('bakerloo_reports/report')} (
      `id` int(11) NOT NULL auto_increment,
      `report_name` varchar(255) NOT NULL default '',
      `table_name` varchar(255) NOT NULL default '',
      `create_sql` TEXT NULL default '',
      `data_sources` TEXT NULL,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY (`id`)
  ) ENGINE=InnoDb DEFAULT CHARSET=utf8 COMMENT='Definition of reports.';
");

$installer->endSetup();