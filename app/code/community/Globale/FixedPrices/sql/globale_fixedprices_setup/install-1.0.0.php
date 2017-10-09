<?php
/**
 * Installation script for the Global-e FixedPrices
 */
$Installer = $this;
$Installer->startSetup();

$FixedPrices = $Installer->getConnection()->newTable($Installer->getTable('globale_fixedprices'))
->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'primary'        => true,
            'nullable'       => false,
            'auto_increment' => true,
            'identity'       => true
        ),  'id')
        ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable'       => false
        ), 'product_id')
        ->addColumn('country_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
            'nullable'       => true
        ), 'country_code')
        ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 3, array(
            'nullable'       => false
        ), 'currency_code')
        ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'       => false
        ), 'price')
        ->addColumn('special_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'       => true
        ), 'special_price')
        ->addColumn('date_from', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
            'nullable'       => true
        ), 'date_from')
        ->addColumn('date_to', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
            'nullable'       => true
        ), 'date_to')
        ->addIndex(
            $Installer->getIdxName(
                $Installer->getTable('globale_order_addresses'),
                array('product_id', 'currency_code'),
                'product_currency',
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('product_id', 'currency_code'),
            'product_currency',
            array(
                'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            )
        );
$Installer->getConnection()->createTable($FixedPrices);
$Installer->endSetup();