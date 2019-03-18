<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


$installer->setConfigData('campaigns/advocate_dashboard', 1);
$installer->endSetup();
