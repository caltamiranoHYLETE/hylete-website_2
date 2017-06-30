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
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 * @author      Urmo Schmidt
 */

/** @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'vaimo_integration_base_product'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('integrationbase/product'))
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
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(), 'SKU')
    ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
        ), 'Attribute Set ID')
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable' => false,
        'default'  => Mage_Catalog_Model_Product_Type::DEFAULT_TYPE,
        ), 'Type ID')
    ->addColumn('parent_sku', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(), 'Parent SKU')
    ->addColumn('configurable_attributes', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Configurable Attributes')
    ->addColumn('product_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Product Data')
    ->addIndex($installer->getIdxName('integrationbase/product', array('row_status')), array('row_status'))
    ->addIndex($installer->getIdxName('integrationbase/product', array('sku')), array('sku'))
    ->setComment('Integration Base Product');

$installer->getConnection()->createTable($table);

/**
 * Create table 'vaimo_integration_base_stock'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('integrationbase/stock'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
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
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(), 'SKU')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Qty')
    ->addColumn('stock_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Stock Status')
    ->addColumn('stock_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Stock Data')
    ->addIndex($installer->getIdxName('integrationbase/stock', array('row_status')), array('row_status'))
    ->addIndex($installer->getIdxName('integrationbase/stock', array('sku')), array('sku'))
    ->setComment('Integration Base Stock');

$installer->getConnection()->createTable($table);

/**
 * Create table 'vaimo_integration_base_price'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('integrationbase/price'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
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
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => 0,
    ), 'Store Id')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(), 'SKU')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Price')
    ->addColumn('special_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(), 'Special Price')
    ->addColumn('special_from_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(), 'Special Price From Date')
    ->addColumn('special_to_date', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(), 'Special Price To Date')
    ->addColumn('tax_class_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => null,
        ), 'Tax Class ID')
    ->addColumn('product_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Product Data')
    ->addIndex($installer->getIdxName('integrationbase/price', array('row_status')), array('row_status'))
    ->addIndex($installer->getIdxName('integrationbase/price', array('sku')), array('sku'))
    ->addIndex($installer->getIdxName('integrationbase/price', array('store_id')), array('store_id'))
    ->setComment('Integration Base Stock');

$installer->getConnection()->createTable($table);

$installer->endSetup();