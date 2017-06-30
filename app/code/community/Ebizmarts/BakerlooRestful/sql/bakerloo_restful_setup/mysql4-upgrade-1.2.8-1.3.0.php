<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$installer->getTable('bakerloo_restful/customertrash')} (
      `id` int(11) unsigned NOT NULL auto_increment,
      `customer_id` int(11) unsigned NOT NULL,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Deleted customers.';
");

$installer->endSetup();