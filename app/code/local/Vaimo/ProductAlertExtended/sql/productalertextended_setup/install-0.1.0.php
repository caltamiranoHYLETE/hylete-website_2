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
 * @package     Vaimo_ProductAlertExtended
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Giorgos Tsioutsiouliklis <giorgos@vaimo.com>
 */

/**
 *
 * @var $installer Mage_Eav_Model_Entity_Setup
 */
$installer = $this;

$installer->startSetup ();

/**
 * Create table 'vaimo_product_alert_stock'
 */
$table = $installer->getConnection ()->newTable ( $installer->getTable ( 'productalertextended/stock' ) )->addColumn ( 'alert_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array (
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true 
), 'Alert Id' )->addColumn ( 'customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array (), 'Customer id' )->addColumn ( 'email', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array (), 'E-mail for the alert' )->addColumn ( 'product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array (
        'unsigned' => true,
        'nullable' => false,
        'default' => '0' 
), 'Product id' )->addColumn ( 'website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array (
        'unsigned' => true,
        'nullable' => false,
        'default' => '0' 
), 'Website id' )->addColumn ( 'add_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array (
        'nullable' => false 
), 'Product alert add date' )->addColumn ( 'send_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array (), 'Product alert send date' )->addColumn ( 'send_count', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array (
        'unsigned' => true,
        'nullable' => false,
        'default' => '0' 
), 'Send Count' )->addColumn ( 'status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array (
        'unsigned' => true,
        'nullable' => false,
        'default' => '0' 
), 'Product alert status' )->addIndex ( $installer->getIdxName ( 'productalertextended/stock', array (
        'customer_id' 
) ), array (
        'customer_id' 
) )->addIndex ( $installer->getIdxName ( 'productalertextended/stock', array (
        'product_id' 
) ), array (
        'product_id' 
) )->addIndex ( $installer->getIdxName ( 'productalertextended/stock', array (
        'website_id' 
) ), array (
        'website_id' 
) )->addForeignKey ( $installer->getFkName ( 'productalertextended/stock', 'website_id', 'core/website', 'website_id' ), 'website_id', $installer->getTable ( 'core/website' ), 'website_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE )->addForeignKey ( $installer->getFkName ( 'productalertextended/stock', 'customer_id', 'customer/entity', 'entity_id' ), 'customer_id', $installer->getTable ( 'customer/entity' ), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE )->addForeignKey ( $installer->getFkName ( 'productalertextended/stock', 'product_id', 'catalog/product', 'entity_id' ), 'product_id', $installer->getTable ( 'catalog/product' ), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE )->setComment ( 'Product Alert Extended Stock' );

$installer->getConnection ()->createTable ( $table );

$installer->endSetup ();