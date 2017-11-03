<?php
$installer=$this;
$installer->startSetup();
$db = Mage::getSingleton('core/resource')->getConnection('core_write');
$table_prefix = Mage::getConfig()->getTablePrefix();
$installer->run("
  DROP TABLE IF EXISTS ".$this->getTable('cminds_coupon/coupon_error_count').";
  CREATE TABLE ".$this->getTable('cminds_coupon/coupon_error_count')." (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `rule_id` INT(11) NOT NULL,
          `coupon_id` int(11) unsigned NOT NULL,
          `customer_not_assigned` int(11) unsigned NOT NULL DEFAULT '0',
          `doesnt_match_conditions` int(11) unsigned NOT NULL DEFAULT '0',
          `expired` int(11) unsigned NOT NULL DEFAULT '0',
          `default_error` int(11) unsigned NOT NULL DEFAULT '0',
          `over_used` int(11) unsigned NOT NULL DEFAULT '0',
          `over_used_by_customer` int(11) unsigned NOT NULL DEFAULT '0',
          `last_occured` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE= InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");
$installer->run("
  DROP TABLE IF EXISTS ".$this->getTable('cminds_coupon/error_log').";
  CREATE TABLE ".$this->getTable('cminds_coupon/error_log')." (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `coupon_id` int(11) unsigned NOT NULL,
          `error_type` int(11) unsigned NOT NULL,
          `datetime` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
