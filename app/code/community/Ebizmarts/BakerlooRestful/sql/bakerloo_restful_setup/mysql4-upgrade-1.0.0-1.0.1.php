<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$installer->getTable('bakerloo_restful/inventorydelta')} (
      `id` int(10) unsigned NOT NULL auto_increment,
      `product_id` int(10) unsigned,
      `inventory_item_id` int(10) unsigned,
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Inventory deltas since cataloginventory_stock_item has no created_at.';
");

$installer->endSetup();