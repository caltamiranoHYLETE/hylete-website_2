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
 */

class Vaimo_Menu_Model_Entity_Attribute_Frontend_Widget extends Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
{
    public function getValue(Varien_Object $object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        if ($widgetInstanceId = $object->getData($attributeCode)) {
            $widgetInstance = Mage::getModel('widget/widget_instance')->load($widgetInstanceId);

            if (!$widgetInstance->getId()) {
                return '';
            }

            $type = $widgetInstance->getInstanceType();
            $config = Mage::getSingleton('widget/widget')->getXmlElementByType($type);

            $data = array(
                'widget_label' => (string)$config->name,
                'instance_id' => $widgetInstance->getId(),
                'widget_type' => $type,
                'parameters' => $widgetInstance->getWidgetParameters()
            );

            return $this->_crPost($data);
        }

        return '';
    }

    /**
     * Custom implementation for urlEncode to get int-based arrays defined as [] = value + to make sure spaces are encoded
     * as %20 (the <5.4 PHP has no good encoding options for this)
     */
    protected function _crPost($dataArray, $base = '', $isArrayKey = 0)
    {
        $result = array();

        if (!is_array($dataArray)) {
            return false;
        }

        foreach ((array)$dataArray as $key => $value) {
            if ($isArrayKey) {
                if (is_numeric($key)) {
                    $key = $base . "[]";
                } else {
                    $key = $base . "[$key]";
                }
            } else {
                if (is_int($key)) {
                    $key = $base . $key;
                }
            }

            if (is_array($value) || is_object($value)) {
                $result[] = $this->_crPost($value, $key, 1);
                continue;
            }

            $result[] = rawurlencode($key) . "=" . rawurlencode($value);
        }

        return implode("&", $result);
    }
}