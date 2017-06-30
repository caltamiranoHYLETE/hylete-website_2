<?php

/* @var $installer MagicToolbox_Magic360_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$oldModulesInstalled = Mage::helper('magic360/params')->checkForOldModules();
if(empty($oldModulesInstalled)) {
    $mtDefaultValues = Mage::helper('magic360/params')->getDefaultValues();
} else {
    $mtDefaultValues = Mage::helper('magic360/params')->getFixedDefaultValues();
}

//NOTE: quotes need to be escaped
$mtDefaultValues = serialize($mtDefaultValues);

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('magic360/settings')}`;
CREATE TABLE `{$this->getTable('magic360/settings')}` (
    `setting_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `website_id` smallint(5) unsigned default NULL,
    `group_id` smallint(5) unsigned default NULL,
    `store_id` smallint(5) unsigned default NULL,
    `package` varchar(255) NOT NULL default '',
    `theme` varchar(255) NOT NULL default '',
    `last_edit_time` datetime default NULL,
    `custom_settings_title` varchar(255) NOT NULL default '',
    `value` text,
    PRIMARY KEY (`setting_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT INTO `{$this->getTable('magic360/settings')}` (`setting_id`, `website_id`, `group_id`, `store_id`, `package`, `theme`, `last_edit_time`, `custom_settings_title`, `value`) VALUES (NULL, NULL, NULL, NULL, '', '', NULL, 'Edit Magic 360 default settings', '{$mtDefaultValues}');

");

$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('magic360/gallery')}` (
    `product_id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT,
    `columns` tinyint (2) unsigned NOT NULL,
    `gallery` mediumtext,
    PRIMARY KEY (`product_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

");

$installer->endSetup();

?>