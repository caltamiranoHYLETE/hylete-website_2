<?php
/**
 * Copyright (c) 2009-2012 Vaimo AB
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
 * @package     Icommerce_SlideshowManager
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 */

class Icommerce_SlideshowManager_Model_Setup extends Mage_Eav_Model_Entity_Setup
{
    private $_table_add_columns = array(
        'icommerce_slideshow' => array(
            'width'  => 'ADD COLUMN `width` int(11) NOT NULL',
            'height' => 'ADD COLUMN `height` int(11) NOT NULL',
            'thumbnails' => 'ADD COLUMN `thumbnails` int(1) DEFAULT 0 NOT NULL',
            'valid_from' => 'ADD COLUMN `valid_from` datetime DEFAULT NULL',
            'valid_to'   => 'ADD COLUMN `valid_to` datetime DEFAULT NULL',
        ),
        'icommerce_slideshow_item' => array(
            'type'  => 'ADD COLUMN `type` varchar(255) NOT NULL',
            'align' => 'ADD COLUMN `align` varchar(255) NOT NULL',
            'slideshow_content' => 'ADD COLUMN `slideshow_content` text NOT NULL',
            'positiontop'       => 'ADD COLUMN `positiontop` int(11) NOT NULL',
            'positionleft'      => 'ADD COLUMN `positionleft` int(11) NOT NULL',
            'positiontoptype'   => 'ADD COLUMN `positiontoptype` varchar(255) NOT NULL',
            'positionlefttype'  => 'ADD COLUMN `positionlefttype` varchar(255) NOT NULL',
            'backgroundimage'   => 'ADD COLUMN `backgroundimage` varchar(255) NOT NULL',
        ),
    );

    /**
     * @return Mage_Core_Model_Resource_Setup
     */
    public function applyUpdates()
    {
        // TODO your new logic goes here

        $installer = $this;
        $installer->startSetup();
        if (!$this->tableExists($this->getTable('icommerce_slideshow'))) {
            $installer->run("
            CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_slideshow')}` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `status` varchar(255) NOT NULL,
              `position` int(11) NOT NULL,
              `created_on` datetime NOT NULL,
              `created_by` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;");
        }

        if (!$this->tableExists($this->getTable('icommerce_slideshow_item'))) {
            $installer->run("
            CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_slideshow_item')}` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `slideshow_id` int(11) NOT NULL,
              `filename` varchar(255) NOT NULL,
              `image_alt` varchar(255) NOT NULL,
              `title` varchar(255) NOT NULL,
              `image_text` text NOT NULL,
              `link` varchar(255) NOT NULL,
              `link_target` varchar(255) NOT NULL,
              `status` varchar(255) NOT NULL,
              `created_on` datetime NOT NULL,
              `created_by` int(11) NOT NULL,
              `position` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

            ");
        }

        foreach ($this->_table_add_columns as $table => $columns) {
            $add_columns = array();
            $select = 'DESCRIBE ' . $this->getTable($table);
            $existing_columns = $this->getConnection()->fetchPairs($select);
            foreach ($columns as $column => $alter_sql) {
                if (!isset($existing_columns[$column])) {
                    $add_columns[] = $alter_sql;
                }
            }
            if (!empty($add_columns)) {
                $installer->run(sprintf('ALTER TABLE `%s` %s;', $installer->getTable($table), implode(', ', $add_columns)));
            }
        }


        $result = $this->getConnection()->fetchAll('SHOW INDEX FROM `icommerce_slideshow_item`');
        if (!empty($result)) {
            $idx_keys = array();
            foreach ($result as $idx) {
                if (isset($idx['Key_name'])) {
                    $idx_keys[$idx['Key_name']] = $idx['Key_name'];
                }
            }
            if (!isset($idx_keys['IDX_SLIDEITEMS'])) {
                $installer->run(sprintf('ALTER TABLE `%s` %s;', $installer->getTable('icommerce_slideshow_item'), 'ADD INDEX `IDX_SLIDEITEMS` (`slideshow_id`,`status`,`position`)'));
            }
            if (!isset($idx_keys['IDX_SLIDEPOS'])) {
                $installer->run(sprintf('ALTER TABLE `%s` %s;', $installer->getTable('icommerce_slideshow_item'), 'ADD INDEX `IDX_SLIDEPOS` (`slideshow_id`, `position`)'));
            }
        }
        $installer->endSetup();

        return parent::applyUpdates();
    }
}
