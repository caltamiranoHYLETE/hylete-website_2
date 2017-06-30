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
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 */

/** @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'vaimo_integration_base_attribute'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('integrationbase/attribute'))
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
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array('nullable' => false), 'Store Id')
    ->addColumn('entity_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Entity Type')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'Entity Id')
    ->addColumn('attribute_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Attribute Code')
    ->addColumn('attribute_value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Attribute Value')
    ->addIndex($installer->getIdxName('integrationbase/attribute', array('row_status')), array('row_status'))
    ->addIndex($installer->getIdxName('integrationbase/attribute', array('entity_type')), array('entity_type'))
    ->addIndex($installer->getIdxName('integrationbase/attribute', array('entity_id')), array('entity_id'))
    ->addIndex($installer->getIdxName('integrationbase/attribute', array('attribute_code')), array('attribute_code'))
    ->setComment('Integration Base Attribute');

$installer->getConnection()->createTable($table);

$installer->endSetup();