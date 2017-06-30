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

class Vaimo_Cms_Model_Grid
{
    protected function _sortItems(&$items)
    {
        usort($items, function($a, $b) {
            if ($a['row'] == $b['row']) {
                return $a['col'] < $b['col'] ? -1 : 1;
            }

            return $a['row'] < $b['row'] ? -1 : 1;
        });
    }

    protected function _removeArrayKeysFromRowsAndItems($rows)
    {
        foreach ($rows as &$row) {
            $row = array_values(array_filter($row));
            unset($row);
        }

        return array_values(array_filter($rows));
    }

    protected function _flattenGrid($gridItems, $itemStartAttribute, $itemLengthAttribute)
    {
        $_flat = array();
        $_items = array();
        foreach ($gridItems as $item) {
            $colIndex = $item[$itemStartAttribute];
            $length = $item[$itemLengthAttribute];

            if (!isset($_flat[$colIndex])) {
                $_flat[$colIndex] = 0;
                $_items[$colIndex] = array();
            }

            $_flat[$colIndex] = max($_flat[$colIndex], $length);
            $_items[$colIndex][] = $item;
        }

        $indexes = array_keys($_flat);
        ksort($indexes);
        foreach ($indexes as $index) {
            if (isset($_flat[$index])) {
                for ($i = 1; $i < $_flat[$index]; $i++) {
                    $_index = $index + $i;
                    if (isset($_flat[$_index])) {
                        if ($_flat[$_index] + $_index > $_flat[$index] + $index) {
                            $_flat[$index] = $_index + $_flat[$_index] - $index;
                        }

                        $_items[$index] = array_merge($_items[$index], $_items[$_index]);
                        unset($_flat[$_index]);
                        unset($_items[$_index]);
                    }
                }
            }
        }

        return array('groups' => $_flat, 'items' => $_items);
    }

    public function flatGridDefinitionToNestedRows($gridItems)
    {
        $this->_sortItems($gridItems);

        $rows = array();
        $_rows = $this->_flattenGrid($gridItems, 'row', 'size_y');

        foreach ($_rows['groups'] as $start => $length) {
            $items = $_rows['items'][$start];
            $_cols = $this->_flattenGrid($items, 'col', 'size_x');
            $_rowBreak = $start + $length;
            $rows[$_rowBreak] = array();
            foreach ($_cols['groups'] as $_start => $_length) {
                $_colBreak = $_start + $_length;
                $rows[$_rowBreak][$_colBreak] = $_cols['items'][$_start];
            }
        }

        /**
         * Deal with columns that have more than one item
         */
        foreach ($rows as $rowBreak => &$cols) {
            if (count($cols) == 1) {
                $cols = reset($cols);

                foreach ($cols as &$col) {
                    unset($col['row']);
                    unset($col['size_y']);
                }

                continue;
            }

            ksort($cols);

            foreach ($cols as $colBreak => &$colItems) {
                if (count($colItems) > 1) {
                    $_items = $colItems;
                    $colItems = array();

                    $spaceOccupiedByItems = array();
                    foreach ($_items as $_item) {
                        $colItems['row'] = !isset($colItems['row']) ? $_item['row'] : min($colItems['row'], $_item['row']);
                        $colItems['col'] = !isset($colItems['col']) ? $_item['col'] : min($colItems['col'], $_item['col']);

                        if (!isset($spaceOccupiedByItems[$_item['row']])) {
                            $spaceOccupiedByItems[$_item['row']] = 0;
                        }

                        $spaceOccupiedByItems[$_item['row']] += $_item['size_x'];
                    }

                    $colItems['size_y'] = $rowBreak - $colItems['row'];
                    $colItems['size_x'] = $colBreak - $colItems['col'];
                    unset($colItems['size_y']);

                    $colLimit = 12;
                    $sizeGuide = array();

                    $_newItems = array();
                    $sizeModifier = $colLimit / $colItems['size_x'];

                    $this->_sortItems($_items);

                    foreach ($_items as $_key => $item) {
                        if (!isset($_newItems[$_key])) {
                            $item['row'] = ($item['row'] - $colItems['row']) + 1;
                            $item['col'] = (($item['col'] - $colItems['col']) * $sizeModifier) + 1;
                            $item['size_x'] = $item['size_x'] * $sizeModifier;

                            $_newItems[$_key] = $item;
                        }
                    }

                    unset($colItems['row']);

                    reset($_items);
                    while (current($_items) !== false) {
                        $item = current($_items);
                        $_key = key($_items);

                        $takesFullSpace = ($colItems['size_x'] == $spaceOccupiedByItems[$item['row']])
                            && $item['size_x'] == $colItems['size_x'];

                        $originalCol = $item['col'];
                        $originalX = $item['size_x'];

                        unset($_item);
                        $_item = &$_newItems[$_key];

                        $nextItem = false;
                        $lastItem = false;
                        if (isset($_newItems[$_key+1])) {
                            $nextItem = $_newItems[$_key+1];
                        }

                        $_lastKey = $_key - 1;
                        $itemsFollowEachOther = false;
                        if ($_lastKey >= 0 && isset($_newItems[$_lastKey])) {
                            $lastItem = $_newItems[$_lastKey];
                            $itemsFollowEachOther = $_items[$_lastKey]['col'] + $_items[$_lastKey]['size_x'] == $item['col'];
                        }

                        if ($lastItem['row'] != $_item['row']) {
                            $lastItem = false;
                        }

                        if ($nextItem['row'] != $_item['row']) {
                            $nextItem = false;
                        }

                        /**
                         * Deal with float-point numbers
                         */
                        if ((int)$_item['size_x'] != (float)$_item['size_x'] || (int)$_item['col'] != (float)$_item['col']) {
                            if ($takesFullSpace || $itemsFollowEachOther && $nextItem) {
                                if (ceil($_item['size_x']) == (int)floor($nextItem['col']) && $_item['size_x'] != $nextItem['col']) {
                                    $_item['size_x'] = (int)floor($_item['size_x']);
                                    $sizeGuide[$originalX] = (int)$_item['size_x'];
                                    reset($_items);
                                    continue;
                                }
                            }

                            if ($takesFullSpace || $itemsFollowEachOther && $lastItem) {
                                if (floor($_item['col']) == $lastItem['size_x'] + 1 && $_item['col'] != $lastItem['size_x'] + 1) {
                                    $_item['col'] = (int)floor($_item['col']);
                                    reset($_items);
                                    continue;
                                }
                            }

                            if ($takesFullSpace && !$nextItem && $lastItem) {
                                $_item['col'] = (int)floor($lastItem['col'] + $lastItem['size_x']);
                                $_item['size_x'] = $colLimit - $_item['col'] + 1;
                                reset($_items);
                                continue;
                            }

                            if ($originalCol + $originalX == $colBreak) {
                                $_roundedX = round($_item['size_x']);
                                $_flooredCol = (int)floor($_item['col']);
                                if ($_roundedX + $_flooredCol == $colLimit + 1) {
                                    $_item['size_x'] = (int)$_roundedX;
                                    $_item['col'] = (int)$_flooredCol;
                                    $sizeGuide[$originalX] = (int)$_roundedX;
                                    reset($_items);
                                    continue;
                                }
                            }

                            /**
                             * We'll let the size rounding dictate on how we should round COL
                             */
                            if ($_item['size_x'] - round($_item['size_x']) < 0.3) {
                                $_item['col'] = (int)floor($_item['col']);
                            } else {
                                $_item['col'] = (int)round($_item['col']);
                            }

                            $_item['size_x'] = (int)round($_item['size_x']);

                            /**
                             * Deal with situations where the item ends in the end of the grid, but did not originally
                             */
                            $willSpanToEnd = $_item['col'] + $_item['size_x'] == $colLimit + 1;
                            $didNotSpanToEndOriginally = $originalCol + $originalX < $colLimit + 1;

                            if ($lastItem) {
                                $gapBetweenLastItemInThisRow = $lastItem['col'] + $_item['size_x'] < $_item['col'];

                                if ($willSpanToEnd && $didNotSpanToEndOriginally && $gapBetweenLastItemInThisRow) {
                                    $_item['col'] = $_item['col'] - 1;
                                }
                            }
                        }

                        next($_items);
                    }

                    $_items = $_newItems;

                    $_rows = $this->flatGridDefinitionToNestedRows($_items);

                    foreach ($_rows as &$_row) {
                        $firstItem = reset($_row);
                        if (is_array($firstItem) && is_numeric(key($firstItem))) {
                            $_row = reset($_row);
                        }
                    }

                    $colItems['rows'] = $_rows;
                    if ($colItems['size_x'] == 12) {
                        $colItems = $colItems['rows'];
                    }
                } else {
                    $colItems = reset($colItems);
                    unset($colItems['row']);
                    unset($colItems['size_y']);
                }
            }

            unset($colItems);
            unset($cols);
        }

        $rows = $this->_removeArrayKeysFromRowsAndItems($rows);

        foreach ($rows as &$row) {
            $firstItem = reset($row);
            if (is_array($firstItem) && is_numeric(key($firstItem))) {
                $row = reset($row);
            }
        }

        return $rows;
    }
}