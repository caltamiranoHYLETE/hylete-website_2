<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_IntegrationBaseStandard
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Kjell Holmqvist <kjell.holmqvist@vaimo.com>
 */

class Vaimo_IntegrationBaseStandard_Model_Xml_Parser
{
    const ATTRIBUTES_FIELD = 'attributes';
    const LINKS_FIELD = 'links';
    const DEFAULT_STORE = 'admin';

    protected $_result = array();
    protected $_path;
    protected $_currentPaths = array();
    protected $_currentPath = '';
    protected $_currentTag = '';
    protected $_currentRow = array();
    protected $_store = '';
    protected $_linkIndex = 0;

    protected function _startElement($parser, $name, $attributes)
    {

        if($name == "link" && $this->_path . '/' . self::LINKS_FIELD == '/' . implode('/', $this->_currentPaths)){
            $name .= "_".$this->_linkIndex;
            $this->_linkIndex++;
        }


        $this->_currentPath = '/' . implode('/', $this->_currentPaths);
        $this->_currentPaths[] = $name;
        $this->_currentTag = $name;

        if ($this->_path == '/' . implode('/', $this->_currentPaths)) {
            $this->_currentRow = array();
        }

        if ($this->_path . '/' . self::ATTRIBUTES_FIELD == '/' . implode('/', $this->_currentPaths)) {
            if (!is_array($this->_currentRow)) $this->_currentRow = array();
            if (isset($attributes['store'])) {
                $this->_store = $attributes['store'];
            } else {
                $this->_store = self::DEFAULT_STORE;
            }
            if (!isset($this->_currentRow[self::ATTRIBUTES_FIELD])) $this->_currentRow[self::ATTRIBUTES_FIELD] = array();
            if (!isset($this->_currentRow[self::ATTRIBUTES_FIELD][$this->_store])) $this->_currentRow[self::ATTRIBUTES_FIELD][$this->_store] = array();
        }
        if ($this->_path . '/' . self::LINKS_FIELD == '/' . implode('/', $this->_currentPaths)) {
            if (isset($attributes['store'])) {
                $this->_store = $attributes['store'];
            } else {
                $this->_store = self::DEFAULT_STORE;
            }
        }
        if ($this->_path . '/' . self::LINKS_FIELD . '/' . $this->_currentTag == '/' . implode('/', $this->_currentPaths)) {
            if (!is_array($this->_currentRow)) $this->_currentRow = array();

            if (!isset($this->_currentRow[self::LINKS_FIELD])){ $this->_currentRow[self::LINKS_FIELD] = array(); }
            if (!isset($this->_currentRow[self::LINKS_FIELD][$this->_store])){ $this->_currentRow[self::LINKS_FIELD][$this->_store] = array(); }
            if (!isset($this->_currentRow[self::LINKS_FIELD][$this->_store]["link"."_".$this->_linkIndex - 1])){  $this->_currentRow[self::LINKS_FIELD][$this->_store][$this->_currentTag] = array("attributes" => $attributes); }
        }
    }

    protected function _endElement($parser, $name)
    {
        if ($this->_path == '/' . implode('/', $this->_currentPaths)) {
            $this->_result[] = $this->_currentRow;
        }

        array_pop($this->_currentPaths);
        $this->_currentPath = '/' . implode('/', $this->_currentPaths);
        $this->_currentTag = '';
    }

    protected function _characterData($parser, $data)
    {
        if ($this->_currentPath == $this->_path . '/' . self::ATTRIBUTES_FIELD && $this->_currentTag) {
            $this->_currentRow[self::ATTRIBUTES_FIELD][$this->_store][$this->_currentTag] .= $data;
        } elseif ($this->_currentPath == $this->_path . '/' . self::LINKS_FIELD && $this->_currentTag) {
            $this->_currentRow[self::LINKS_FIELD][$this->_store][$this->_currentTag]['sku'] .= $data;
        } elseif ($this->_currentPath == $this->_path && $this->_currentTag && $this->_currentTag!=self::ATTRIBUTES_FIELD && $this->_currentTag!=self::LINKS_FIELD) {
            $this->_currentRow[$this->_currentTag] .= $data;
        }
    }

    protected function _xmlToArray($xmlValues)
    {
        $xmlArray = array();
        $current = & $xmlArray;
        $repeatedTagIndex = array();
        $priority = 'tag';
        $getAttributes = true;

        foreach ($xmlValues as $data) {
            $tag = $data['tag'];
            $type = $data['type'];
            $level = $data['level'];
            $attributes = isset($data['attributes']) ? $data['attributes'] : array();
            $value = isset($data['value']) ? $data['value'] : null;
            $result = array();
            $attributesData = array();

            if (isset($value)) {
                if ($priority == 'tag') {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }
            if (isset($attributes) and $getAttributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag')
                        $attributesData[$attr] = $val;
                    else
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }

            if ($type == 'open') {
                $parent[$level - 1] = & $current;
                if (!is_array($current) || !in_array($tag, array_keys($current))) {
                    $current[$tag] = $result;

                    if ($attributesData)
                        $current[$tag . '_attr'] = $attributesData;

                    $repeatedTagIndex[$tag . '_' . $level] = 1;

                    $current = &$current[$tag];
                } else {
                    if (isset ($current[0])) {
                        $current[$repeatedTagIndex[$tag . '_' . $level]] = $result;
                        $repeatedTagIndex[$tag . '_' . $level]++;
                    } else {
                        $current = array(
                            $current[$tag],
                            $result
                        );
                        $repeatedTagIndex[$tag . '_' . $level] = 2;
                        if (isset ($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }

                    }
                    $lastItemIndex = $repeatedTagIndex[$tag . '_' . $level] - 1;
                    $current = & $current[$lastItemIndex];
                }
            } elseif ($type == 'complete') {
                if (!isset ($current[$tag])) {
                    $current[$tag] = $result;
                    $repeatedTagIndex[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributesData)
                        $current[$tag . '_attr'] = $attributesData;
                } else {
                    if (isset ($current[0]) and is_array($current)) {
                        $current[$repeatedTagIndex[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $getAttributes and $attributesData) {
                            $current[$tag][$repeatedTagIndex[$tag . '_' . $level] . '_attr'] = $attributesData;
                        }

                        $repeatedTagIndex[$tag . '_' . $level]++;
                    } else {
                        $current = array(
                            $current[$tag],
                            $result
                        );
                        $repeatedTagIndex[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $getAttributes) {
                            if (isset ($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset ($current[$tag . '_attr']);
                            }
                            if ($attributesData) {
                                $current[$tag][$repeatedTagIndex[$tag . '_' . $level] . '_attr'] = $attributesData;
                            }
                        }

                        $repeatedTagIndex[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            } elseif ($type == 'close') {
                $current = & $parent[$level - 1];
            }
        }

        return $xmlArray;
    }

    public function parse($filename, $path)
    {
        $this->_result = array();
        $this->_path = $path;

        if (!$fp = fopen($filename, 'r')) {
            Mage::throwException('Could not open file');
        }

        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_set_element_handler($parser, '_startElement', '_endElement');
        xml_set_character_data_handler($parser, '_characterData');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);

        while ($data = fread($fp, 50)) {
            if (!xml_parse($parser, $data, feof($fp))) {
                Mage::throwException(
                    sprintf('XML error: %s at line %d',
                    xml_error_string(xml_get_error_code($parser)),
                    xml_get_current_line_number($parser))
                );
            }
        }

        xml_parser_free($parser);
        fclose($fp);

        return $this->_result;
    }

    public function parseToArray($filename)
    {
        if (!is_readable($filename)) {
            Mage::throwException('Could not read file');
        }

        $data = file_get_contents($filename);

        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($data), $xmlValues);

        xml_parser_free($parser);

        // _xmlToArray function is borrowed from Naturkompaniet.
        //
        // Comment by Urmo:
        // this is ugly function now, I plan to make it better in future when I get time,
        // clean up and actually use xml_parse function, instead of xml_parse_into_struct
        return $this->_xmlToArray($xmlValues);
    }
}