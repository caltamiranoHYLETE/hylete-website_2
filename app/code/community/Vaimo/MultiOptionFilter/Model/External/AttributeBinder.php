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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

/**
 * @deprecated Legacy code. Should be either moved to AttributeBinder module or be deleted
 */
class Vaimo_MultiOptionFilter_Model_External_AttributeBinder
{
    /**
     * @var Vaimo_MultiOptionFilter_Helper_App
     */
    protected $_appHelper;

    public function __construct()
    {
        $this->_appHelper = Mage::helper('multioptionfilter/app');
    }

    public function getOptionUrl($attributeCode, $optionId)
    {
        if (!$rootUrlKey = $this->_getRootUrlKey()) {
            return null;
        }

        $attributes = array();

        foreach ($this->_appHelper->getRequestedState() as $attributeKey => $options) {
            if ($attributeKey == 'price' || $attributeKey == 'cat') {
                continue;
            }

            foreach ($options as $optionKey => $optionVal) {
                $attributes[$attributeKey] = $optionKey;
            }
        }

        if (isset($attributes[$attributeCode]) && $optionId == $attributes[$attributeCode]) {
            unset($attributes[$attributeCode]);
        } else {
            $attributes[$attributeCode] = $optionId;
        }

        $url = Mage::helper('categoryattributebinder')
            ->optionValsToUrl($attributes, null, null, array($rootUrlKey), null, false);

        return $url ? Mage::getBaseUrl() . $url : null;
    }

    protected function _getRootUrlKey()
    {
        static $categoryUrlKey;

        if ($categoryUrlKey !== null) {
            return $categoryUrlKey;
        }

        if (!$categoryId = Mage::getStoreConfig('multioptionfilter/settings/default_category_id')) {
            return '';
        }

        return Mage::getResourceModel('catalog/category')->getAttributeRawValue(
            $categoryId,
            'url_key',
            Mage::app()->getStore()->getId()
        );
    }
}