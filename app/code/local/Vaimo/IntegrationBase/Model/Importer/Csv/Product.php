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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Importer_Csv_Product extends Vaimo_IntegrationBase_Model_Importer_Csv_Abstract
{
    protected $_eventPrefix = 'product';
    protected $_ignoredAttributes = array();

    protected function _importRow($data)
    {
        $sku = '';
        $attributeSetId = 0;
        $typeId = '';
        $parentSku = null;
        $configurableAttributes = array();
        $productData = array();

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'sku':
                    $sku = $value;
                    break;
                case 'attribute_set_id':
                    $attributeSetId = Mage::getSingleton('catalog/config')->getAttributeSetId(Mage_Catalog_Model_Product::ENTITY, $value);
                    break;
                case 'type_id':
                    $typeId = $value;
                    break;
                case 'parent_sku':
                    $parentSku = $value;
                    break;
                case 'configurable_attributes':
                    $configurableAttributes = explode(',', $value);
                    break;
                case 'category_ids':
                    $productData['category_ids'] = explode(',', $value);
                    break;
                default:
                    $value = Mage::helper('integrationbase/attribute')->getAttributeValue(Mage_Catalog_Model_Product::ENTITY, $key, $value);
                    if ($value !== false) {
                        $productData[$key] = $value;
                    } else {
                        $this->_ignoredAttributes[$key] = true;
                    }
                    break;
            }
        }

        if (!$sku) {
            Mage::throwException('Sku not specified');
        }

        if (!$attributeSetId) {
            Mage::throwException('Attribute Set Id not specified');
        }

        if (!$typeId) {
            Mage::throwException('Type Id not specified');
        }

        if ($typeId == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && !$configurableAttributes) {
            Mage::throwException('Configurable Attributes not specified');
        }

        /** @var Vaimo_IntegrationBase_Model_Product $product */
        $product = Mage::getModel('integrationbase/product');
        $product->load($sku, 'sku');
        $product->setSku($sku);
        $product->setAttributeSetId($attributeSetId);
        $product->setTypeId($typeId);
        $product->setParentSku($parentSku);
        $product->setConfigurableAttributes($configurableAttributes);
        $product->setProductData($productData);
        $product->setRawData($data);
        $product->setRowStatus(Vaimo_IntegrationBase_Helper_Data::ROW_STATUS_IMPORT);
        $product->save();
    }
}