<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 * @author      Tobias Åström
 */

$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

/* appapi_auth */

$tableName = $installer->getTable('appapi/auth');

if (!Icommerce_Db::tableExists($tableName)) {
    $table = $connection->newTable($tableName)
        ->addColumn('auth_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('auto_increment' => true, 'unsigned' => true, 'nullable' => false,
            'primary' => true,), 'AppApi auth_id')
        ->addColumn('auth_token', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array('length' => 32, 'nullable' => false), 'Customer auth_token')
        ->addColumn('auth_timestamp', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array('nullable' => false,), 'Customer auth_timestamp')
        ->addColumn('auth_valid_to', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array('nullable' => false,), 'Customer auth_valid_to')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,), 'Customer customer_id')
        ->addIndex($this->getIdxName($tableName, array(
            'auth_id',
            'auth_token'
        ), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), array(
            'auth_id',
            'auth_token'
        ), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
        ->addForeignKey($this->getFkName($tableName, 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id', $this->getTable('customer/entity' ), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('AppApi Auth');

    $connection->createTable($table);
}

/* appapi_nonce */

$tableName = $installer->getTable('appapi/nonce');

if (!Icommerce_Db::tableExists($tableName)) {
    $table = $connection->newTable($tableName)
        ->addColumn('nonce_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('auto_increment' => true, 'unsigned' => true, 'nullable' => false,
            'primary' => true,), 'AppApi nonce_id')
        ->addColumn('auth_nonce', Varien_Db_Ddl_Table::TYPE_TEXT, null, array('unique' => true, 'nullable' => false,), 'Customer auth_nonce')
        ->addColumn('auth_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false,), 'AppApi auth_id')
        ->addIndex($this->getIdxName($tableName, array(
            'nonce_id'
        )), array(
            'nonce_id'
        ))
        ->addForeignKey($this->getFkName($tableName, 'auth_id', 'appapi/auth', 'auth_id'),
            'auth_id', $this->getTable('appapi/auth' ), 'auth_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('AppApi Nonce');

    $connection->createTable($table);
}

/* save the setup */

$installer->endSetup();