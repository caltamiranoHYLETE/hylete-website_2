<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

$installer = $this;
$installer->startSetup();

/** @var Varien_Db_Adapter_Interface $connection */
$connection = $this->getConnection();

$blockTable = $installer->getTable('cms/block');

$columnName = 'block_id';
$columnType = 'INT(11)  UNSIGNED';

$foreignKeys = array();
foreach ($connection->listTables() as $tableName) {
    foreach ($connection->getForeignKeys($tableName) as $foreignKey) {
        if ($foreignKey['REF_TABLE_NAME'] != $blockTable || $foreignKey['REF_COLUMN_NAME'] != $columnName) {
            continue;
        }

        $foreignKeys[] = $foreignKey;
    }
}

foreach ($foreignKeys as $foreignKey) {
    $connection->dropForeignKey($foreignKey['TABLE_NAME'], $foreignKey['FK_NAME']);
}

$connection->modifyColumn($blockTable, $columnName, $columnType . '  AUTO_INCREMENT  COMMENT \'Block ID\'');

foreach ($foreignKeys as $foreignKey) {
    $connection->modifyColumn(
        $foreignKey['TABLE_NAME'],
        $foreignKey['COLUMN_NAME'],
        $columnType . '  COMMENT \'Block ID\''
    );
}

foreach ($foreignKeys as $foreignKey) {
    $connection->addForeignKey(
        $foreignKey['FK_NAME'],
        $foreignKey['TABLE_NAME'],
        $foreignKey['COLUMN_NAME'],
        $foreignKey['REF_TABLE_NAME'],
        $foreignKey['REF_COLUMN_NAME'],
        $foreignKey['ON_DELETE'],
        $foreignKey['ON_UPDATE']
    );
}

$installer->endSetup();
