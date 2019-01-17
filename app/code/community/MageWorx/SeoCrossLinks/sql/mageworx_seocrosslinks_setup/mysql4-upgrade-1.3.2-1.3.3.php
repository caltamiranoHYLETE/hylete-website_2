<?php
/**
 * MageWorx
 * MageWorx SeoCrossLinks Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoCrossLinks
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('mageworx_seocrosslinks/crosslink'),
        'nofollow_rel',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'nullable' => false,
            'unsigned' => true,
            'default'  => 0,
            'comment'  => 'Nofollow rel for crosslink',
        )
    );

$installer->endSetup();



