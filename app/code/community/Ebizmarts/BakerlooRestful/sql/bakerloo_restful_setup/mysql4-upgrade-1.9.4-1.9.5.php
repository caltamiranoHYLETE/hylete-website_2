<?php

$installer = $this;
$installer->startSetup();


//add order date to bakerloo_restful_orders
$ordersTable = $installer->getTable('bakerloo_restful/order');

$installer->getConnection()->addColumn($ordersTable, 'order_date', "datetime NULL");

$select = $installer->getConnection()
    ->select()
    ->from(array('br' => $ordersTable));

$select->reset()
    ->join(
        array('so' => $installer->getTable('sales/order')),
        'br.order_id = so.entity_id',
        array('order_date' => 'so.created_at')
    );

$updateSql = $select->crossUpdateFromSelect(array('br' => $ordersTable));

$installer->getConnection()->query($updateSql);



//add order payment method to bakerloo_restful_orders
$ordersTable = $installer->getTable('bakerloo_restful/order');

$installer->getConnection()->addColumn($ordersTable, 'payment_method', "varchar(255) NULL");

$select = $installer->getConnection()
    ->select()
    ->from(array('br' => $ordersTable));

$select->reset()
    ->join(
        array('so' => $installer->getTable('sales/order_payment')),
        'br.order_id = so.parent_id',
        array('payment_method' => 'so.method')
    );

$updateSql = $select->crossUpdateFromSelect(array('br' => $ordersTable));

$installer->getConnection()->query($updateSql);


//add payment method to shift_movements
$shiftActs = $installer->getTable('bakerloo_restful/shift_activity');

$installer->getConnection()->addColumn($shiftActs, 'payment_method', "VARCHAR(255) NOT NULL DEFAULT '' ");


$installer->endSetup();