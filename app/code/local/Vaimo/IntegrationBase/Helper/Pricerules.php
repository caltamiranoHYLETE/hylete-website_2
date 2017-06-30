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
 * @package     Vaimo_IntegarionBase
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Peter Lembke <peter.lembke@vaimo.com>
 */
class Vaimo_IntegrationBase_Helper_Pricerules extends Mage_Core_Helper_Abstract
{

    protected $_MyConfig = array(
        'types' => 'integrationbase/apply_rules/apply_to_types',
        'product' => 'integrationbase/apply_rules/apply_after_product_import',
        'attribute' => 'integrationbase/apply_rules/apply_after_attribute_import',
        'price' => 'integrationbase/apply_rules/apply_after_price_import',
        'zero' => 'integrationbase/apply_rules/apply_on_zero_price'
    );

    /**
     * Get a config by its alias from the array above
     * @param $alias
     * @return bool|mixed
     */
    protected function _GetConfig($alias)
    {
        if (!isset($this->_MyConfig[$alias])) {
            return false;
        }
        return Mage::getStoreConfig($this->_MyConfig[$alias]);
    }

    /**
     * Check if we should bother with this area
     * and in the case of attribute import, if the attribute that are updated
     * are one that can affect price rules
     * @param string $area
     * @param string $attributeName
     * @return bool
     */
    protected function _CheckArea($area = '', $attributeName = '')
    {
        if($this->_GetConfig($area) == '0') {
            return false;
        }

        if ($area === 'attribute') {
            $attributeCandidates = array('sku', 'category_ids', 'price');
            if (!in_array(strtolower($attributeName), $attributeCandidates)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Determine if you should apply the price rules on a product
     * @since 2014-02-20
     * @param string $area | product or attribute or price
     * @param int $price | the product price
     * @param string $type | the product type (simple or configurable)
     * @param string $attributeName
     * @return bool
     */
    public function shouldIApply($area = '', $price = 0, $type = '', $attributeName = '')
    {

        if (!$this->_CheckArea($area, $attributeName)) {
            return false;
        }

        if ($price == 0) {
            if ($this->_GetConfig('zero') == '0') {
                return false;
            }
        }

        $answer = explode(',', $this->_GetConfig('types'));
        if (!in_array($type, $answer)) {
            return false;
        }

        return true; // Yes you should apply the price rules on this product
    }

    /**
     * Apply price rules on products
     * @since 2014-02-20
     * @param array $products | Magento products in an array
     * @param string $area | product or attribute or price
     * @param string $attributeName
     * @return bool
     */
    public function applyAllRulesToProducts($products, $area, $attributeName = '')
    {
        if (!$this->_CheckArea($area, $attributeName)) {
            return false;
        }
        $ok = true;
        foreach ($products as $product) {
            if (!$this->applyAllRulesToProduct($product, $area, $attributeName)){
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * Apply price rules on product
     * @since 2014-02-20
     * @param $product | Magento product object
     * @param $area | string | product or attribute or price
     * @param $attributeName
     * @return bool
     */
    public function applyAllRulesToProduct($product, $area, $attributeName = '')
    {
        $price = $product->getPrice();
        $type = $product->getTypeId();
        if (!$this->shouldIApply($area, $price, $type, $attributeName)) {
            return false;
        }
        Mage::getModel('catalogrule/rule')->applyAllRulesToProduct($product);
        return true;
    }

}
