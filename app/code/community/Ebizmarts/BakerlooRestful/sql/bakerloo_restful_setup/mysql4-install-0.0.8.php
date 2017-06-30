<?php

$installer = $this;
$installer->startSetup();

/**
 * Create table 'bakerloo_restful/order'
 */

$installer->run("
    CREATE TABLE IF NOT EXISTS {$installer->getTable('bakerloo_restful/order')} (
      `id` int(11) unsigned NOT NULL auto_increment,
      `admin_user` varchar(255) character set utf8 NOT NULL default '',
      `order_id` int(11) unsigned NOT NULL,
      `order_increment_id` varchar(50) character set utf8 NOT NULL default '',
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Orders created with Bakerloo POS.';
");

$installer->endSetup();