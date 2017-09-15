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

class Vaimo_MultiOptionFilter_Model_Attribute_Options extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getOptionArray()
    {
        $filterableAttributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addIsFilterableFilter();

        $options = array(
            'cat' => 'Category [cat]'
        );

        foreach ($filterableAttributes as $attributes) {
            $attributeCode = $attributes->getAttributeCode();
            $options[$attributeCode] = $attributes->getFrontendLabel() . ' ['. $attributeCode . ']';
        }

        return $options;
    }

    public function getAllOptions()
    {
        $options = array();

        foreach ($this->getOptionArray() as $index => $value) {
            $options[] = array(
               'value' => $index,
               'label' => $value
            );
        }

        return $options;
    }
}