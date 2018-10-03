<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

Mage::getConfig()->saveConfig('carbon/settings/icons', 'disable');


/** update checkout icon */
$installer->getConnection()->query(
    "UPDATE cms_block SET content = REPLACE(
                    content, 
                    '<i class=\"fa-li fa fa-exclamation-triangle\"></i>', 
                    '<i class=\"fas fa-exclamation-triangle\"></i>'
                    )"
);

$installer->getConnection()->query(
    "UPDATE cms_block SET content = REPLACE(
                    content, 
                    '<i class=\"fa-li fa fa-calendar\"></i>', 
                    '<i class=\"fal fa-calendar-alt\"></i>'
                    )"
);

$installer->endSetup();
