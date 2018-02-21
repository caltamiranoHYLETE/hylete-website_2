<?php

$installer = $this;

$installer->startSetup();

// MYLES: Use PHP constructs, not raw SQL

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('mediotype_offerstab/offer')};

CREATE TABLE {$this->getTable('mediotype_offerstab/offer')} (
  `offer_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
