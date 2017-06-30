<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Menu_Model_Resource_Catalog_Category_Tree_Flat
{
    /**
     * addFieldToFilter implementation for flat category list. Reason for doing it this way is to get over the problems
     * that are caused by addFieldToFilter implementation for flat categories being tremendously different (not to mention
     * the difference across different Magento versions) from the implementation in normal entity collection.
     *
     * @param $collection
     * @param $conditions
     */
    public function addFieldToFilter($collection, $conditions)
    {
        $_collection = Mage::getResourceModel('catalog/category_collection');
        $_select = $_collection->getSelect();
        $_from = $_select->getPart(Zend_Db_Select::FROM);
        $_where = $_select->getPart(Zend_Db_Select::WHERE);
        $_collection->addFieldToFilter($conditions);
        $conditions = array_diff($_select->getPart(Zend_Db_Select::WHERE), $_where);

        /**
         * Make the target collection inherit the WHERE statements created by addFieldToFilter
         */
        $select = $collection->getSelect();
        $from = $select->getPart(Zend_Db_Select::FROM);

        foreach ($conditions as &$condition) {
            $condition = str_replace('`' . key($_from) . '`', '`' . key($from) . '`', $condition);
        }

        $select->setPart(Zend_Db_Select::WHERE, array_merge($select->getPart(Zend_Db_Select::WHERE), $conditions));
    }
}