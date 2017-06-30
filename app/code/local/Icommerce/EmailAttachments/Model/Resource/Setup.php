<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @category    Icommerce
 * @package     Icommerce_EmailAttachments
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */
class Icommerce_EmailAttachments_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    const ATTACHMENTS_COLUMN = 'attachments';
    const FILESYSTEM_FLAG_COLUMN = 'is_file_system_used';

    /**
     * Apply module resource install, upgrade and data scripts
     *
     * @return Mage_Core_Model_Resource_Setup
     */
    public function applyUpdates()
    {
        try {
            //any version of Magento is expected here
            $tableName = $this->getTable('core/email_queue');
            if ($this->tableExists($tableName)) {
                //Magento CE 1.9.1.0, EE 1.14.1.0 or higher is expected here
                $this->startSetup();
                $this->addCustomColumns($tableName);
                $this->endSetup();
            }
        } catch (Exception $e) {
            //logging is not needed, because getTable may throw exception if table doesn't exist
        }
        return parent::applyUpdates();
    }

    /**
     * Add custom columns to appropriate table
     *
     * @param string $table
     *
     * @return Icommerce_EmailAttachments_Model_Resource_Setup
     */
    public function addCustomColumns($table) {
        $connection = $this->getConnection();
        foreach ($this->getDefaultColumns() as $columnName => $definition) {
            if (!$connection->tableColumnExists($table, $columnName)) {
                $connection->addColumn($table, $columnName, $definition);
            }
        }
        return $this;
    }

    /**
     * Retrieve definition for default columns
     *
     * @return array
     */
    public function getDefaultColumns()
    {
        return array(
            self::ATTACHMENTS_COLUMN => array(
                'type' => Varien_Db_Ddl_Table::TYPE_BLOB,
                'length'    => '1024k',
                'nullable'  => true,
                'comment'   => ucfirst(self::ATTACHMENTS_COLUMN),
            ),
            self::FILESYSTEM_FLAG_COLUMN => array(
                'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
                'nullable'  => false,
                'default'   => 0,
                'comment'   => 'Is File System Used',
            ),
        );
    }
}
