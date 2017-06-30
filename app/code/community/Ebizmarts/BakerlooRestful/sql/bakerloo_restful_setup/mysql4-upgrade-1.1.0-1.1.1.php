<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('bakerloo_restful/notification')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `title` varchar(255) NOT NULL,
	  `description` text,
	  `severity` tinyint(3) unsigned NOT NULL default '0', /*CRITICAL = 1;MAJOR = 2;MINOR = 3;NOTICE = 4;*/
	  `url` varchar(255) NOT NULL,
	  `is_read` tinyint(1) unsigned NOT NULL default '0',
	  `is_remove` tinyint(1) unsigned NOT NULL default '0',
	  `date_added` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `IDX_SEVERITY` (`severity`),
	  KEY `IDX_IS_READ` (`is_read`),
	  KEY `IDX_IS_REMOVE` (`is_remove`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$installer->getTable('bakerloo_restful/notificationstore')}` (
      `notification_id` int(10) unsigned NOT NULL,
      `store_id`    smallint(5) unsigned NOT NULL,
      PRIMARY KEY (`notification_id`,`store_id`),
      CONSTRAINT `FK_POS_NOTIFICATION_STORE` FOREIGN KEY (`notification_id`) REFERENCES `{$installer->getTable('bakerloo_restful/notification')}` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT `FK_POS_NOTIFICATION_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Discounts to Stores';

");

$installer->endSetup();