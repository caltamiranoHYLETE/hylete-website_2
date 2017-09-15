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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

use Vaimo_MultiOptionFilter_Model_Resource_Helper_Mysql4 as Utils;
use Varien_Db_Select as Select;

class Vaimo_MultiOptionFilter_Model_Resource_Query_Manipulator
{
    /**
     * @var Vaimo_MultiOptionFilter_Model_Resource_Query_Mapper
     */
    protected $_mapper;

    /**
     * @var Vaimo_MultiOptionFilter_Model_Resource_Query_Map_Utils
     */
    protected $_mapAnalyser;

    public function __construct()
    {
        $this->_mapper = Mage::getResourceSingleton('multioptionfilter/query_mapper');
        $this->_mapAnalyser = Mage::getResourceSingleton('multioptionfilter/query_map_utils');
    }

    public function copyFilter(Select $origin, Select $target, array $tableFilter = array())
    {
        $map = array_replace(
            $this->_mapper->mapTables($origin, $target),
            array_fill_keys($tableFilter, array('' => false))
        );

        $items = $this->_mapAnalyser->getCommonFilters($map, $origin->getPart(Varien_Db_Select::WHERE));
        $replacer = $this->_mapAnalyser->getNameReplacer($map);

        list($from, $to) = array(array_keys($replacer), array_values($replacer));

        return $target->setPart(Varien_Db_Select::WHERE, array_map(function ($index, $item) use ($from, $to) {
            return str_replace($from, $to, !$index ? preg_replace('#^AND #i', '', $item) : $item);
        }, array_keys(array_values($items)), $items));
    }

    public function setCountsToDistinctMode(Select $select)
    {
        $columns = $select->getPart(Select::COLUMNS);

        array_walk_recursive($columns, function (&$item) use ($select) {
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

        return $select->setPart(Select::COLUMNS, $columns);
    }

    public function updateAddedJoins(Select $selectBefore, Select $selectAfter, array $converters)
    {
        $joinsCurrent = $selectAfter->getPart(Zend_Db_Select::FROM);

        $targetedJoins = array_intersect_key(
            $joinsCurrent,
            array_diff_key($joinsCurrent, $selectBefore->getPart(Zend_Db_Select::FROM))
        );

        $utils = Mage::getResourceSingleton('multioptionfilter/query_part_utils');

        $updatedJoins = array_reduce($converters, function ($targetedJoins, $converter) use ($utils) {
            return $utils->getUpdateJoinConditions($targetedJoins, $converter);
        }, $targetedJoins);

        $selectAfter->setPart(Zend_Db_Select::FROM, array_replace($joinsCurrent, $updatedJoins));
    }
}
