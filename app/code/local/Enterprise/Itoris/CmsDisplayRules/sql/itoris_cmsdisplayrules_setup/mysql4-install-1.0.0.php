<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_CMSDISPLAYRULES
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

  
$this->startSetup();

$this->run("

create table if not exists {$this->getTable('itoris_cms_display_rules_view')} (
	`view_id` int unsigned not null auto_increment primary key,
	`scope` enum('default', 'website', 'store') not null,
	`scope_id` int unsigned not null,
	unique(`scope`, `scope_id`)
) engine = InnoDB default charset = utf8;

create table if not exists {$this->getTable('itoris_cms_display_rules_settings')} (
	`setting_id` int unsigned not null auto_increment primary key,
	`view_id` int unsigned not null,
	`product_id` int(10) unsigned null,
	`key` varchar(255) not null,
	`value` int unsigned not null,
	`type` enum('text', 'default') null,
	unique(`view_id`, `key`, `product_id`),
	foreign key (`view_id`) references {$this->getTable('itoris_cms_display_rules_view')} (`view_id`) on delete cascade on update cascade,
	foreign key (`product_id`) references {$this->getTable('catalog_product_entity')} (`entity_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists {$this->getTable('itoris_cms_display_rules_settings_text')} (
	`setting_id` int unsigned not null,
	`value` text not null,
	index(`setting_id`),
	foreign key (`setting_id`) references {$this->getTable('itoris_cms_display_rules_settings')} (`setting_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists  {$this->getTable('itoris_cms_display_rules_page')} (
    `page_id` smallint(6) not null primary key,
    `start_date` date null,
    `finish_date` date null,
    `another_cms` int not null,
    foreign key (`page_id`) references {$this->getTable('cms_page')} (`page_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists  {$this->getTable('itoris_cms_display_rules_page_group')} (
    `page_id` smallint(6) not null,
    `group_id` smallint(5) unsigned not null,
    foreign key (`page_id`) references {$this->getTable('itoris_cms_display_rules_page')} (`page_id`) on delete cascade on update cascade,
    foreign key (`group_id`) references {$this->getTable('customer_group')} (`customer_group_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists  {$this->getTable('itoris_cms_display_rules_block')} (
    `block_id` int(11) unsigned NOT NULL,
    `start_date` date null,
    `finish_date` date null,
    `another_cms` int not null,
    foreign key (`block_id`) references {$this->getTable('cms_block')} (`block_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

create table if not exists {$this->getTable('itoris_cms_display_rules_block_group')} (
    `block_id` smallint(6) not null,
    `group_id` smallint(5) unsigned not null,
    foreign key (`block_id`) references {$this->getTable('itoris_cms_display_rules_block')} (`block_id`) on delete cascade on update cascade,
    foreign key (`group_id`) references {$this->getTable('customer_group')} (`customer_group_id`) on delete cascade on update cascade
) engine = InnoDB default charset = utf8;

");

$this->endSetup();
?>