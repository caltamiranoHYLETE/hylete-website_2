<?php

$installer = $this;

$installer->startSetup();

Mage::helper('rewards/mysql4_install')->addColumns($installer, $this->getTable('catalogrule'), array (
    "`points_only_mode` TINYINT(1) DEFAULT '0'",
));

$installer->endSetup();



