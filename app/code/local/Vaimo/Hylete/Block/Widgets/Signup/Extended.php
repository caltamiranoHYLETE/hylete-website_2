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
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_Hylete_Block_Widgets_Signup_Extended extends Mage_Customer_Block_Form_Register implements Mage_Widget_Block_Interface
{
    const REQUIRED_SELECT_CLASS = 'validate-select';
    const REQUIRED_CHECKBOX_CLASS = 'validate-one-required-by-name';
    const REQUIRED_INPUTTEXT_CLASS = 'required-entry';
    const REQUIRED_LABEL = 'required';
    private $eavAttributes = array();
    /** @var Mage_Eav_Model_Config */
    private $eavConfig;

    protected function _construct()
    {
        $this->eavConfig = Mage::getModel('eav/config');
        parent::_construct();
    }

    /**
     * @param string $attributeName
     * @param bool $required
     *
     * @return string
     */
    protected function getAttributeLabel($attributeName, $required = false)
    {
        $html = '<label';
        if ($required) {
            $html .= ' class="' . self::REQUIRED_LABEL . '" ';
        }
        $html .= '>';
        $html .= $this->__($this->getAttributeFromModel($attributeName)->getFrontendLabel());
        if ($required) {
            $html .= '<span class="' . self::REQUIRED_LABEL . '">*</span>';
        }
        $html .= '</label>';

        return $html;
    }

    /**
     * @param string $attributeName
     * @param bool $required
     *
     * @return string
     * @throws Exception
     */
    protected function getInputHtml($attributeName, $required = false)
    {
        $type = $this->getAttributeInputType($attributeName);
        switch ($type) {
            case 'multiselect':
                return $this->getCheckboxHtml($attributeName, $required);
            case 'text':
                return $this->getInputTextHtml($attributeName, $required);
            case 'select':
                return $this->getSelectHtml($attributeName, $required);
            default:
                throw new Exception('Unrecognised type ' . $type . 'for attribute ' . $attributeName);
        }
    }

    /**
     * @param string $attributeName
     *
     * @return  Mage_Eav_Model_Entity_Attribute_Abstract|false
     */
    private function getAttributeFromModel($attributeName)
    {
        if (!isset($this->eavAttributes[$attributeName])) {
            $this->eavAttributes[$attributeName] = $this->eavConfig->getAttribute('customer', $attributeName);
        }

        return $this->eavAttributes[$attributeName];
    }

    /**
     * @param string $attributeName
     *
     * @return mixed
     */
    private function getAttributeInputType($attributeName)
    {
        $attribute = $this->getAttributeFromModel($attributeName);

        return $attribute->getData('frontend_input');
    }

    /**
     * @param string $attributeName
     * @param bool $required
     *
     * @return string
     */
    private function getCheckboxHtml($attributeName, $required)
    {
        $options = $this->getAttributeOptions($attributeName);
        $lastKey = $this->getLastKey($options);
        $html = '';
        foreach ($options as $key => $option) {
            $name = $this->getAttributeName($attributeName);
            $html .= '<input type = "checkbox" name = "' . $name . '[]" ';
            $html .= 'id = "' . $name . $option['value'] . '" value = "' . $option['value'] . '"';
            if ($required && $key == $lastKey) {
                $html .= 'class="' . self::REQUIRED_CHECKBOX_CLASS . '" ';
            }
            $html .= '>';
            $html .= '<label for="' . $name . $option['value'] . '" > ' . $this->__($option['label']) . '</label >';
        }

        return $html;
    }

    /**
     * @param string $attributeName
     * @param bool $required
     *
     * @return string
     */
    private function getInputTextHtml($attributeName, $required)
    {
        $html = '<input type = "text" id = "' . $this->getAttributeName($attributeName) . '" ';
        $html .= ' name = "' . $this->getAttributeName($attributeName) . '" class="input-text';
        if ($required) {
            $html .= ' ' . self::REQUIRED_INPUTTEXT_CLASS;
        }
        $html .= '">';

        return $html;
    }

    /**
     * @param string $attributeName
     * @param bool $required
     *
     * @return string
     */
    private function getSelectHtml($attributeName, $required)
    {
        $html = '<select name = "' . $this->getAttributeName($attributeName) . '"';
        if ($required) {
            $html .= ' class="' . self::REQUIRED_SELECT_CLASS . '"';
        }
        $html .= '>';
        $html .= '<option value=""> </option>';
        foreach ($this->getAttributeOptions($attributeName) as $option) {
            $html .= '<option data-select="' . $option['data'] . '" value= "' . $option['value'] . '">' . $this->__($option['label']) . '</option >';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * @param string $attributeName
     *
     * @return string
     */
    private function getAttributeName($attributeName)
    {
        $attribute = $this->getAttributeFromModel($attributeName);

        return $this->__($attribute->getName());
    }

    /**
     * @param string $attributeName
     *
     * @return array
     */
    private function getAttributeOptions($attributeName)
    {
        $attribute = $this->getAttributeFromModel($attributeName);
        $defaultOptions = $attribute->getSource()->getAllOptions(false, true);
        $storeOptions = $attribute->getSource()->getAllOptions(false);
        $parsedOptions = array();
        foreach ($defaultOptions as $key => $value) {
            $parsedOptions[$key] = array(
                'data' => $this->generateSlug($value['label']),
                'value' => $storeOptions[$key]['value'],
                'label' => $storeOptions[$key]['label']
            );
        }

        return $parsedOptions;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function generateSlug($input)
    {
        $slug = preg_replace("/[^a-zA-Z0-9- ]/", '', trim(strtolower($input)));

        return str_replace(' ', '-', $slug);
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    private function getLastKey($options)
    {
        end($options);
        $lastKey = key($options);
        reset($options);

        return $lastKey;
    }
}