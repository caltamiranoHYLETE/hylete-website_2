<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$installer->getTable('bakerloo_restful/catalogtrash')} (
      `id` int(11) unsigned NOT NULL auto_increment,
      `store_id` smallint(5) unsigned NULL,
      `product_id` int(11) unsigned NOT NULL,
      `product_sku` varchar(255) NULL,
      `product_name` varchar(255) NULL,
      `action` enum('delete', 'remove_website'),
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Deleted and removed from website products.';
");

$installer->endSetup();