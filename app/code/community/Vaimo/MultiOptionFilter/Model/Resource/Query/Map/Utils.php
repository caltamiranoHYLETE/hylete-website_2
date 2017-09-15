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

class Vaimo_MultiOptionFilter_Model_Resource_Query_Map_Utils
{
    /**
     * @var Vaimo_MultiOptionFilter_Model_Resource_Helper_Mysql4
     */
    protected $_resourceUtils;

    /**
     * @var Vaimo_MultiOptionFilter_Helper_Array
     */
    protected $_arrayUtils;

    public function __construct()
    {
        $this->_arrayUtils = Mage::helper('multioptionfilter/array');
        $this->_resourceUtils = Mage::getResourceHelper('multioptionfilter');
    }

    public function getCommonFilters(array $map, $items)
    {
        $skippedPatterns = $this->_resourceUtils->getSearchValuesForTableNames(
            $this->_arrayUtils->flattenArrayKeys(array_filter($map, function ($item) {
                return !reset($item);
            }))
        );

        return array_filter($items, function ($item) use ($skippedPatterns) {
            foreach ($skippedPatterns as $pattern) {
                if (strstr($item, $pattern) !== false) {
                    return false;
                }
            }

            return true;
        });
    }

    public function getNameReplacer(array $map)
    {
        $from = array();
        $to = array();

        foreach ($map as $item) {
            if (reset($item) === false || reset($item) === key($item)) {
                continue;
            }

            $from = array_replace($from, $this->_resourceUtils->getSearchValuesForTableNames(array(
                key($item)
            )));

            $to = array_replace($to, $this->_resourceUtils->getSearchValuesForTableNames(array(
                reset($item)
            )));
        }

        return array_combine($from, $to);
    }
}
