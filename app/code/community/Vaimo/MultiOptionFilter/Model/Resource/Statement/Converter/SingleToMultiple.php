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

use Vaimo_MultiOptionFilter_Model_Resource_Helper_Mysql4 as MysqlHelper;

class Vaimo_MultiOptionFilter_Model_Resource_Statement_Converter_SingleToMultiple implements
    Vaimo_MultiOptionFilter_Model_Resource_Statement_ConverterInterface
{
    public function getMatchPattern()
    {
        return "/\\s?=\\s?'(\\b.+?[^\\\\])'/s";
    }

    public function convert(array $matches)
    {
        if (!isset($matches[0]) || strstr(reset($matches), MysqlHelper::VALUE_SEPARATOR) === false) {
            return reset($matches);
        }

        $explodedValues = array_map(function ($item) {
            return '\'' . $item . '\'';
        }, explode(MysqlHelper::VALUE_SEPARATOR, array_pop($matches)));

        return sprintf(' IN (%s)', implode(MysqlHelper::VALUE_SEPARATOR, $explodedValues));
    }
}
