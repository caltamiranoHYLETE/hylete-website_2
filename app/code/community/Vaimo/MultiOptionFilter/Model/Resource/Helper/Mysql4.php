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

class Vaimo_MultiOptionFilter_Model_Resource_Helper_Mysql4
{
    const VALUE_SEPARATOR = ',';

    public static function escapeTableName($name)
    {
        return Mage_DB_Mysqli::TABLE_ESCAPER . $name . Mage_DB_Mysqli::TABLE_ESCAPER . '.';
    }

    public function getSearchValuesForTableNames($names)
    {
        return array_merge(
            array_map(array($this, 'escapeTableName'), $names),
            array_map(function ($item) {
                return ' ' . $item . '.';
            }, $names),
            array_map(function ($item) {
                return '(' . $item . '.';
            }, $names)
        );
    }

    /**
     * @param $select
     * @param callable $interceptor
     * @return mixed
     */
    public function createInterceptedClone($select, \Closure $interceptor)
    {
        if (!$select) {
            return false;
        }

        return Mage::helper('multioptionfilter/proxy')->createInstance(clone $select, array(
            '__toString' => $interceptor
        ));
    }
}
