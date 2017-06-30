<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

/** @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'vaimo_integration_base_creditmemo'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('integrationbase/creditmemo'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Id')
    ->addColumn('row_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => Vaimo_IntegrationBase_Helper_Data::ROW_STATUS_IMPORTED,
        ), 'Row Status')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
        ), 'Updated At')
    ->addColumn('raw_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Raw Data')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => false,
        ), 'Increment Id')
    ->addColumn('do_offline', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(), 'Refund Offline')
    ->addColumn('comment_text', Varien_Db_Ddl_Table::TYPE_TEXT, 500, array(), 'Credit Memo Comment')
    ->addColumn('shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Refund Shipping')
    ->addColumn('adjustment_positive', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Adjustment Refund')
    ->addColumn('adjustment_negative', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Adjustment Fee')
    ->addColumn('send_email', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(), 'Email Copy of Credit Memo')
    ->addColumn('refund_customerbalance_return_enable', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(), 'Refund to Store Credit')
    ->addColumn('refund_customerbalance_return', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Refund to Store Credit Amount')
    ->addIndex($installer->getIdxName('integrationbase/creditmemo', array('row_status')), array('row_status'))
    ->addIndex($installer->getIdxName('integrationbase/creditmemo', array('increment_id')), array('increment_id'))
    ->setComment('Integration Base Creditmemo');

$installer->getConnection()->createTable($table);

/**
 * Create table 'vaimo_integration_base_creditmemo_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('integrationbase/creditmemo_item'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        ), 'Parent Id')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(), 'SKU')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Qty')
    ->addColumn('back_to_stock', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(), 'Return to Stock')
    ->addColumn('raw_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Raw Data')
    ->addIndex($installer->getIdxName('integrationbase/creditmemo_item', array('parent_id')), array('parent_id'))
    ->addForeignKey($installer->getFkName('integrationbase/creditmemo_item', 'parent_id', 'integrationbase/creditmemo', 'id'),
        'parent_id', $installer->getTable('integrationbase/creditmemo'), 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Integration Base Creditmemo Item');

$installer->getConnection()->createTable($table);

$installer->endSetup();