<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_MultiOptionFilter_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Mysql4
{
    public function setCountsToDistinctMode($select)
    {
        $columns = $select->getPart(Zend_Db_Select::COLUMNS);

        array_walk_recursive($columns, function(&$item) use ($select) {
            if (strstr($item, 'COUNT(') === false) {
                return;
            }

            $select->distinct(false);

            $item = new Zend_Db_Expr(str_replace(
                array('COUNT(*)', 'COUNT(', 'COUNT(DISTINCT DISTINCT'),
                array('COUNT(DISTINCT e.entity_id)', 'COUNT(DISTINCT ', 'COUNT(DISTINCT'),
                $item
            ));
        });

        $select->setPart(Zend_Db_Select::COLUMNS, $columns);
    }

    public function changeJoinConditions(
        $select, Vaimo_MultiOptionFilter_Model_Resource_Statement_ConverterInterface $converter, $aliasFilter = array()
    ) {
        $from = $select->getPart(Zend_Db_Select::FROM);
        $keys = $aliasFilter ?: array_keys($from);

        foreach ($keys as $key) {
            if (!isset($from[$key])) {
                continue;
            }

            $partItem = &$from[$key];

            if (!isset($partItem['joinCondition']) || !$partItem['joinCondition']) {
                continue;
            }

            if (!$condition = $converter->convert($partItem['joinCondition'])) {
                continue;
            }

            $partItem['joinCondition'] = $condition;
            unset($partItem);
        }

        $select->setPart(Zend_Db_Select::FROM, $from);

        return $select;
    }
}