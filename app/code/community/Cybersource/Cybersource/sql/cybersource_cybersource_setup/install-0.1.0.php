<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getTable('cybersourcesop/token');

/** @var $ddlTable Varien_Db_Ddl_Table */
$ddlTable = $installer->getConnection()->newTable($table);
$ddlTable->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'primary'  => true,
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
    ), 'Primary Key ID')
    ->addColumn('token_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false),'Token ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false),'Customer ID')
    ->addColumn('cc_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'nullable' => false,), 'Credit Card Type')
    ->addColumn('cc_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
        'nullable' => false,), 'Credit Card Number')
    ->addColumn('cc_expiration', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
        'nullable' => false,), 'Credit Card Expiration Date')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_INTEGER, 255, array(
        'nullable' => true,), 'Is Default')
    ->addColumn('merchant_ref', Varien_Db_Ddl_Table::TYPE_INTEGER, 255, array(
        'nullable' => true,), 'Merchant Reference')
    ->setComment('CybersourceSOP Token Table');

$installer->getConnection()->createTable($ddlTable);

$tableSalesQuotePayment = $installer->getTable('sales/quote_payment');
$installer->getConnection()
    ->addColumn($tableSalesQuotePayment, 'cybersource_vc_order_id', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 200,
        'nullable' => true,
        'comment' => 'VisaCheckout Order ID'));
$installer->getConnection()
    ->addColumn($tableSalesQuotePayment, 'cybersource_vc_authorize_id', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 200,
        'nullable' => true,
        'comment' => 'VisaCheckout Authorize ID'));
$installer->getConnection()->addColumn($tableSalesQuotePayment, 'cybersource_vc_capture_id', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 200,
    'nullable' => true,
    'comment' => 'VisaCheckout Capture ID'));
$tableSalesOrderPayment = $installer->getTable('sales/order_payment');
$installer->getConnection()
    ->addColumn($tableSalesOrderPayment, 'cybersource_vc_order_id', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 200,
        'nullable' => true,
        'comment' => 'VisaCheckout Order ID'));
$installer->getConnection()
    ->addColumn($tableSalesOrderPayment, 'cybersource_vc_authorize_id', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 200,
        'nullable' => true,
        'comment' => 'VisaCheckout Authorize ID'));

$installer->getConnection()->addColumn($tableSalesOrderPayment, 'cybersource_vc_capture_id', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 200,
    'nullable' => true,
    'comment' => 'VisaCheckout Capture ID'));

$installer->getConnection()
    ->addColumn($installer->getTable('tax/tax_class'),'cs_tax_code', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment'   => 'CyberSource Tax Code'
    ));

$installer->endSetup();
