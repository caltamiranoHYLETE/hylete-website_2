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
 * @category    Vaimo
 * @package     Vaimo_BlockAjax
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

class Vaimo_BlockAjax_Model_Response_Serializer
{
    protected $_valueSeparator = array(27, 30);
    protected $_keySegmentSeparator = array(27, 29);

    protected function _asciiCodesToString($characterCodes)
    {
        $string = '';

        foreach ($characterCodes as $code) {
            $string .= chr($code);
        }

        return $string;
    }

    protected function _getValueSeparator()
    {
        return $this->_asciiCodesToString($this->_valueSeparator);
    }

    protected function _getKeySegmentSeparator()
    {
        return $this->_asciiCodesToString($this->_keySegmentSeparator);
    }

    public function serialize($data, $keyStack = '')
    {
        $valueSeparator = $this->_getValueSeparator();
        $keySeparator = $this->_getKeySegmentSeparator();
        $serialized = '';

        foreach ($data as $key => $value) {
            $_key = $keyStack . ($keyStack ? $keySeparator : '') . $key;

            if (is_array($value)) {
                $serialized .= $this->serialize($value, $_key);
            } else {
                $serialized .= $_key . $valueSeparator . $value . $valueSeparator;
            }
        }

        return $serialized;
    }

    public function deserialize($serializedData)
    {
        $valueSeparator = $this->_getValueSeparator();
        $keySeparator = $this->_getKeySegmentSeparator();
        $data = array();

        $keyPathsAndValues = explode($valueSeparator, $serializedData);
        $keyPieces = array();
        $arrayFactory = new stdClass;

        foreach ($keyPathsAndValues as $index => $value) {
            if ($index % 2 == 0) {
                $keyPieces = explode($keySeparator, $value);
            } else {
                $_data = array();
                while ($keyPieces) {
                    $key = array_pop($keyPieces);

                    $arrayFactory->{(string)$key} = !$_data ? $value : $_data;
                    $_data = (array)$arrayFactory;

                    unset($arrayFactory->{(string)$key});
                }

                $data = array_merge_recursive($data, $_data);
            }
        }

        return $data;
    }
}