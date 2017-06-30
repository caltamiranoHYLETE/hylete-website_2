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

class Vaimo_MultiOptionFilter_Model_Resource_Statement_Converter_SingleToMultiple implements
    Vaimo_MultiOptionFilter_Model_Resource_Statement_ConverterInterface
{
    /**
     * @var /Closure
     */
    protected $_matcher;

    public function convert($statement)
    {
        if (!$this->_matcher) {
            $separator = ',';

            $read = Mage::getSingleton('core/resource')->getConnection('core_read');

            $this->_matcher = function($assignmentMatches) use ($read, $separator) {
                if (!isset($assignmentMatches[0]) || strstr($assignmentMatches[0], $separator) === false) {
                    return $assignmentMatches[0];
                }

                $valueMatches = array();
                preg_match("/'([^']+)'/", $assignmentMatches[0], $valueMatches);

                return $read->quoteInto(' IN (?)', explode($separator, $valueMatches[1]));
            };
        }

        $statement = preg_replace_callback("/\\s?=\\s?'[^']+'/", $this->_matcher, $statement);

        return $statement;
    }
}