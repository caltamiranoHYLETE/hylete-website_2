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
 * @package     Vaimo_SocialLogin
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 */

/** @var Vaimo_SocialLogin_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

/*
$installer->run("
    CREATE TABLE `vaimo_sociallogin` (
      `sociallogin_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Field for unique id',
      `customer_id` int(10) unsigned NOT NULL COMMENT 'Magento customer ID',
      `facebook_id` bigint(20) unsigned DEFAULT NULL COMMENT 'The user ID from Facebook',
      `google_id` varchar(50) DEFAULT NULL COMMENT 'The user ID from Google',
      `twitter_id` varchar(50) DEFAULT NULL COMMENT 'The user ID from Twitter',
      PRIMARY KEY (`sociallogin_id`),
      KEY `customer_id_index` (`customer_id`),
      KEY `facebook_id_index` (`facebook_id`),
      KEY `google_id_index` (`google_id`),
      KEY `twitter_id_index` (`twitter_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Different social IDs for the customer';
");
*/

$tablename = $installer->getTable('sociallogin/login');

/** @var Varien_Db_Ddl_Table $table */
$table = $installer->getConnection()
    ->newTable($tablename)
    ->addColumn('sociallogin_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'auto_increment' => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Field for unique id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Magento customer ID')
    ->addColumn('facebook_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'The user ID from Facebook')
    ->addColumn('google_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'  => true,
        ), 'The user ID from Google')
    ->addColumn('twitter_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
        'nullable'  => true,
        ), 'The user ID from Twitter')
    ->addIndex('customer_id_index',array('customer_id'))
    ->addIndex('facebook_id_index',array('facebook_id'))
    ->addIndex('google_id_index',array('google_id'))
    ->addIndex('twitter_id_index',array('twitter_id'));

/** Varien_Db_Ddl_Table::setComment doesn't exist in older Magento */
if (method_exists($table, 'setComment')) {
    $table->setComment('Different social IDs for the customer');
}
$installer->getConnection()->createTable($table);

if (Icommerce_Default::getMagentoVersion() < 1800) {
    // auto_increment not handled correctly in older
    $installer->run("ALTER TABLE {$tablename}  MODIFY COLUMN sociallogin_id int(10) unsigned NOT NULL AUTO_INCREMENT");
}
$installer->endSetup();
