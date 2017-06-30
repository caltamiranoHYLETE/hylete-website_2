<?php

$installer = $this;
$installer->startSetup();

/**
 * Create table 'bakerloo_restful/unsent'
 */

$installer->run("
    CREATE TABLE IF NOT EXISTS {$installer->getTable('bakerloo_email/unsent')} (
      `id` int(11) unsigned NOT NULL auto_increment,
      `order_id` int(11) unsigned NOT NULL,
      `customer_id` int(11) unsigned NOT NULL,
      `to_email` varchar(255) null,
      `attachment` varchar(255) null,
      `email_type` enum('magento', 'receipt', 'both'),
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Emails to be sent to customers.';
");

$installer->endSetup();