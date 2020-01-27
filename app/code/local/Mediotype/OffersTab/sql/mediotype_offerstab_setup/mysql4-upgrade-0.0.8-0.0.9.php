<?php

/**
 * Install Image and Description message column for Offers Tab records.
 *
 * @category  Configuration
 * @package   Mediotype_OffersTab
 * @author    Rick Buczynski <rick@mediotype.com>
 * @copyright 2018 Mediotype
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('mediotype_offerstab/offer'),
        'description',
        'VARCHAR(255)'
    );
$installer->getConnection()
    ->addColumn(
        $installer->getTable('mediotype_offerstab/offer'),
        'image',
        'VARCHAR(255)'
    );
$installer->getConnection()
    ->addColumn(
        $installer->getTable('mediotype_offerstab/offer'),
        'image',
        'VARCHAR(255)'
    );
$installer->getConnection()
    ->addColumn(
        $installer->getTable('mediotype_offerstab/offer'),
        'feed_status',
        'SMALLINT(6)'
    );

$installer->endSetup();
