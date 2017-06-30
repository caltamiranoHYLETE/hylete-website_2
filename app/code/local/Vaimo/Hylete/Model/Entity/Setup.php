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
 * @package     Vaimo_Module
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */
class Vaimo_Hylete_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
    private $attributeOptionsCache;
    /** @var $eavConfig Mage_Eav_Model_Config */
    private $eavConfig;

    /**
     * @param string $attributeKey
     * @param array $attributeSettings
     */
    public function addOrUpdateCustomerAttributes($attributeKey, $attributeSettings)
    {
        $attributeId = $this->getAttribute('customer', $attributeKey, 'attribute_id');
        if (!$attributeId) {
            $attributeOptions = $this->getCustomerAttributeOptions($attributeSettings);
            $this->addAttribute('customer', $attributeKey, $attributeOptions);
        } else {
            if (isset($attributeSettings['option']) && is_array($attributeSettings['option'])) {
                foreach ($attributeSettings['option'] as $optionKey => $optionValue) {
                    if ($this->optionExistsInAttribute($attributeKey, $optionValue)) {
                        unset($attributeSettings['option'][$optionKey]);
                    }
                }
                if (count($attributeSettings['option'])) {
                    $attributeOptions['option']['attribute_id'] = $attributeId;
                    $attributeOptions['option']['values'] = $attributeSettings['option'];
                    $this->addAttributeOption($attributeOptions['option']);
                }
            }
        }
        /** @var Mage_Eav_Model_Entity_Attribute_Abstract $attributeObject */
        $attributeObject = $this->getCustomerAttributeObject($attributeKey);
        $attributeObject->setData('used_in_forms', array(
            'customer_account_create',
            'customer_account_edit'
        ))->setData('is_visible', 1)->setData('sort_order', 3000)->save();
    }

    /**
     * @param string $label
     * @param string $input
     * @param array | string $inputOptions
     *
     * @return array
     * @throws Exception
     */
    private function getCustomerAttributeOptions($attributeSettings)
    {
        $label = isset($attributeSettings['label']) ? $attributeSettings['label'] : '';
        $input = isset($attributeSettings['input']) ? $attributeSettings['input'] : '';
        $optionValues = isset($attributeSettings['option']) ? $attributeSettings['option'] : array();
        $attributeOptions = array(
            'label' => $label,
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'required' => false,
            'system' => false,
            'user_defined' => true,
            'default' => '',
            'unique' => false,
            'note' => 'Added For Hylete Custom Customer Registration'
        );
        if ($input === 'text') {
            $attributeOptions['type'] = 'varchar';
            $attributeOptions['input'] = 'text';
            $attributeOptions['source'] = '';
        } elseif ($input === 'select' && count($optionValues)) {
            $attributeOptions['type'] = 'int';
            $attributeOptions['input'] = 'select';
            $attributeOptions['source'] = 'eav/entity_attribute_source_table';
            $attributeOptions['option'] = array(
                'values' => $optionValues
            );
        } elseif ($input === 'multiselect' && count($optionValues)) {
            $attributeOptions['type'] = 'varchar';
            $attributeOptions['input'] = 'multiselect';
            $attributeOptions['backend'] = 'eav/entity_attribute_backend_array';
            $attributeOptions['source'] = 'eav/entity_attribute_source_table';
            $attributeOptions['option'] = array(
                'values' => $optionValues
            );
        } else {
            throw new Exception('No matching input and optionValues');
        }

        return $attributeOptions;
    }

    /**
     * @param string $attributeKey
     *
     * @return false|Mage_Eav_Model_Entity_Attribute_Abstract
     */
    private function getCustomerAttributeObject($attributeKey)
    {
        if (!$this->eavConfig) {
            $this->eavConfig = Mage::getSingleton('eav/config');
        }

        return $this->eavConfig->getAttribute('customer', $attributeKey);
    }

    /**
     * @param string $attributeKey
     * @param string $option
     *
     * @return bool
     */
    private function optionExistsInAttribute($attributeKey, $option)
    {
        $attribute = $this->getCustomerAttributeObject($attributeKey);
        if (!$attribute) {
            return false;
        }
        $attributeId = $attribute->getId();
        if (!isset($this->attributeOptionsCache) || !isset($this->attributeOptionsCache[$attributeId])) {
            $this->attributeOptionsCache[$attributeId] = $attribute->getSource()
                ->getAllOptions(false, true);
        }
        $currentAttributeOptions = $this->attributeOptionsCache[$attributeId];
        foreach ($currentAttributeOptions as $currentOptionValue) {
            if ($currentOptionValue['label'] == $option) {
                return true;
            }
        }

        return false;
    }
}