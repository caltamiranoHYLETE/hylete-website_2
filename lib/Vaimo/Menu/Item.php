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
 * @comment     Stripped-down version of Varien_Object with some extra features
 */

/**
 * Class Vaimo_Menu_Item
 *
 * @method $this setIsPseudo(bool $isPseudo)
 * @method bool getIsPseudo()
 * @method bool setHasPseudos(bool $hasPseudos)
 * @method bool getHasPseudos()
 */
class Vaimo_Menu_Item extends Varien_Object
{
    public function hasChildren()
    {
        return isset($this->_data['children']) && $this->_data['children'];
    }

    public function addChild($item)
    {
        if (!isset($this->_data['children'])) {
            $this->_data['children'] = array();
        }

        $this->_data['children'][] = $item;

        return $item;
    }

    public function addPseudoChild(Vaimo_Menu_Item $item = null)
    {
        if ($item === null) {
            $item = new Vaimo_Menu_Item();
        }

        $item->setIsPseudo(true);
        $this->setHasPseudos(true);

        if (!$item->hasEntityId()) {
            if (isset($this->_data['entity_id'])) {
                $entityId = $this->_data['entity_id'] . '_' . $this->getChildCount();
            } else {
                $entityId = md5(microtime());
            }

            $item->setEntityId('pseudo_' . $entityId);
        }

        if (isset($this->_data['level'])) {
            $item->setLevel($this->_data['level']);
        }

        return $this->addChild($item);
    }

    public function getLastChild()
    {
        if ($this->hasChildren()) {
            return end($this->_data['children']);
        }

        return false;
    }

    public function getChild($childIndex)
    {
        if (!$this->hasChildren()) {
            return false;
        }

        return $this->_data['children'][$childIndex];
    }

    public function getChildCount()
    {
        if (!$this->hasChildren()) {
            return 0;
        }

        return count($this->_data['children']);
    }

    protected function _serialize($data, $attributeFilter = array(), $valueSeparator = '=', $fieldSeparator = '; ', $quote = '"', $addKeys = true)
    {
        $_data = array();
        foreach ($data as $key => $value) {
            if ($attributeFilter && !is_numeric($key) && !in_array($key, $attributeFilter)) {
                continue;
            }

            $_value = $value;
            $_quotes = $quote;

            if ($_value instanceof Vaimo_Menu_Item) {
                $_value = $_value->serialize($attributeFilter);
                $_quotes = '';
            } elseif (is_array($_value)) {
                $_value = $this->_serialize($_value, $attributeFilter, $valueSeparator, $fieldSeparator, $quote='"', false);
                $_quotes = array('(', ')');
            }

            $before = $after = $_quotes;
            if (is_array($_quotes)) {
                list($before, $after) = $_quotes;
            }

            $_data[] = ($addKeys ? ($key . $valueSeparator) : '') . $before . $_value . $after;
        }

        return implode($fieldSeparator, $_data);
    }

    public function serialize($attributeFilter = array(), $valueSeparator = '=', $fieldSeparator = '; ', $quote = '"')
    {
        return '{' . $this->_serialize($this->_data, $attributeFilter, $valueSeparator, $fieldSeparator, $quote) . '}';
    }
}