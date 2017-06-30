<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$installer->getTable('bakerloo_restful/discount')} (
      `id` int(11) unsigned NOT NULL auto_increment,
      `discount_max` decimal(12,4) DEFAULT NULL,
      `discount_type` enum('percentage', 'amount'),
      `discount_description` varchar(255),
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$this->getTable('bakerloo_restful/discountstore')}` (
      `discount_id` int(11) unsigned NOT NULL,
      `store_id`    smallint(5) unsigned NOT NULL,
      PRIMARY KEY (`discount_id`,`store_id`),
      CONSTRAINT `FK_BAKERLOO_DISCOUNT_STORE` FOREIGN KEY (`discount_id`) REFERENCES `{$this->getTable('bakerloo_restful/discount')}` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT `FK_BAKERLOO_DISCOUNT_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Discounts to Stores';

");

$installer->endSetup();