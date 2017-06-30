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
 * Create table 'vaimo_integration_base_queue'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('integrationbase/queue'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Id')
    ->addColumn('entity_type_code', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(), 'Entity Type Code')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        ), 'Entity Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
        ), 'Created At')
    ->addColumn('exported_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => true,
        ), 'Exported At')
    ->addIndex($installer->getIdxName('integrationbase/queue', array('entity_type_code')), array('entity_type_code'))
    ->addIndex($installer->getIdxName('integrationbase/queue', array('exported_at')), array('exported_at'))
    ->setComment('Integration Base Queue');

$installer->getConnection()->createTable($table);

$installer->endSetup();