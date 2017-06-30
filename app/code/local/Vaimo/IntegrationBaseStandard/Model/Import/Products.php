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

class Vaimo_IntegrationBaseStandard_Model_Import_Products extends Vaimo_IntegrationBaseStandard_Model_Import_Abstract
{
    const CONFIG_XML_PATH_DISABLE_NEW_PRODUCTS = 'integrationbase/standard_products/new_products_disabled';
    const CONFIG_XML_PATH_NEW_PRODUCTS_ONLY = 'integrationbase/standard_products/new_products_only';

    protected $_successMessage = '%d products(s) imported';
    protected $_failureMessage = '%d products(s) failed to import';
    /** @var Mage_Catalog_Model_Product $_productModel  */
    protected $_productModel = null;
    protected $_attributeSetId = null;
    protected $_websiteIds = null;
    protected $_taxClassId = null;
    protected $_duplicateSkus = array();

    protected function _construct()
    {
        $this->_productModel = Mage::getModel('catalog/product');
    }

    protected function _getAttributeSetId()
    {
        if (!$this->_attributeSetId) {
            $this->_attributeSetId = Mage::getModel('eav/entity_type')
                ->loadByCode(Mage_Catalog_Model_Product::ENTITY)
                ->getDefaultAttributeSetId();
        }

        return $this->_attributeSetId;
    }

    protected function _getWebsiteIds()
    {
        if (!$this->_websiteIds) {
            /** $website Mage_Core_Model_Website */
            foreach (Mage::app()->getWebsites() as $website) {
                $this->_websiteIds[] = $website->getId();
            }
        }

        return $this->_websiteIds;
    }

    protected function _getTaxClassId()
    {
        if (!$this->_taxClassId) {
            $this->_taxClassId = 4;
        }

        return $this->_taxClassId;
    }

    protected function _importProduct($parentProduct)
    {
        $disableNewProducts = Mage::getStoreConfig(self::CONFIG_XML_PATH_DISABLE_NEW_PRODUCTS);
        $newProductsOnly = Mage::getStoreConfig(self::CONFIG_XML_PATH_NEW_PRODUCTS_ONLY);

        if (!isset($parentProduct['sku'])) {
            $this->_log('sku empty, skipping product' );
            return;
        }
        if (!isset($parentProduct['type']) || !isset($parentProduct['attribute_set'])) {
            $this->_log('Required field missing, skipping product ' . $parentProduct['sku'] );
            return;
        }
        switch ($parentProduct['type']) {
            case 'configurable':
                if (!isset($parentProduct['configurable_attributes'])) {
                    $this->_log('configurable_attributes field missing, skipping product ' . $parentProduct['sku'] );
                    return;
                }
                break;
        }
        $this->_log($parentProduct['sku']);
        $isNewProduct = $this->_productModel->getIdBySku($parentProduct['sku']) ? false : true;

        /** @var Vaimo_IntegrationBase_Model_Product $product */
        $product = Mage::getModel('integrationbase/product');
        $product->load($parentProduct['sku'], 'sku');
        $product->setSku($parentProduct['sku']);
        $productData = array();

        if ($isNewProduct || !$product->getId()) {
            $attribute_set_id = Icommerce_Eav::getAttributeSetId($parentProduct['attribute_set'], "catalog_product");
            if ($attribute_set_id) {
                $product->setAttributeSetId($attribute_set_id);
            } else {
                $product->setAttributeSetId($this->_getAttributeSetId());
            }
            $product->setTypeId($parentProduct['type']);
            if (isset($parentProduct['configurable_attributes'])) $product->setConfigurableAttributes(array($parentProduct['configurable_attributes']));
            if (isset($parentProduct['parent_sku'])) $product->setParentSku($parentProduct['parent_sku']);
        }

        if ($isNewProduct) {
            if (isset($parentProduct['parent_sku'])) {
                $productData['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
            } else {
                $productData['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;
            }
            if ($disableNewProducts) {
                $productData['status'] = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            } else {
                $productData['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
            }
            $productData['website_ids'] = $this->_getWebsiteIds();
            $productData['tax_class_id'] = $this->_getTaxClassId();
            $productData['price'] = 0;
        } else {
            if ($newProductsOnly) {
                $attributesToExclude = explode(',', str_replace(' ', '', $newProductsOnly));
                foreach ($attributesToExclude  as $exclude) {
                    foreach ($parentProduct['attributes'] as $storeId => $storeArr) {
                        foreach ($storeArr as $id => $value) {
                            if ($id==$exclude) {
                                unset($parentProduct['attributes'][$storeId][$id]);
                            }
                        }
                    }
                }
            }
        }

        foreach ($parentProduct['attributes'] as $storeId => $data) {
            try {
                $store = Mage::app()->getStore($storeId);
            } catch (Exception $e) {
                $this->_log('Store ' . $storeId . ' not found, ignoring all attributes for that store (' .$e->getMessage() .')' );
                continue;
            }
            $storeId = $store->getStoreId();
            foreach ($data as $id => $attrib) {
                $data[$id] = Mage::helper('integrationbase/attribute')->getAttributeValue('catalog_product', $id,$attrib); // $storeId
            }

            if ($storeId == 0) {
                $productData = array_merge($productData, $data);
            } else {
                foreach ($data as $key => $value) {
                    /** @var Vaimo_IntegrationBase_Model_Attribute $attribute */
                    $attribute = Mage::getModel('integrationbase/attribute');
                    $attribute->loadByLookup('sku', $parentProduct['sku'], 'catalog_product', $key, $storeId);
                    $attribute->setStoreId($storeId);
                    $attribute->setEntityType('catalog_product');
                    $attribute->setAttributeCode($key);
                    $attribute->setAttributeValue($value);
                    $attribute->setLookupField('sku');
                    $attribute->setLookupValue($parentProduct['sku']);
                    $attribute->setRowStatus(Vaimo_IntegrationBase_Helper_Data::ROW_STATUS_IMPORT);
                    $attribute->save();
                }
            }
        }

        if (isset($parentProduct['category_ids'])) {
            $productData['category_ids'] = explode(',', $parentProduct['category_ids']);
        }

        $product->setProductData($productData);
        $product->setRawData($parentProduct);
        $product->setRowStatus(Vaimo_IntegrationBase_Helper_Data::ROW_STATUS_IMPORT);
        $product->save();

        if(is_array($parentProduct['links'])){
            foreach($parentProduct['links'] as $storeLinkData){
                foreach($storeLinkData as $linkData){

                    $productLink = Mage::getModel("integrationbase/link");
                    $productLink->setRowStatus(Vaimo_IntegrationBase_Helper_Data::ROW_STATUS_IMPORT);
                    $productLink->setCreatedAt(date("Y-m-d H:i:s"));
                    $productLink->setUpdatedAt(date("Y-m-d H:i:s"));
                    $productLink->setRawData(serialize($linkData));
                    $productLink->setProductSku($parentProduct['sku']);
                    $productLink->setLinkedProductSku($linkData['sku']);
                    $productLink->setLinkTypeCode(strtolower($linkData['attributes']['type']));
                    $productLink->save();

                }
            }
        }

    }

    public function import($filename)
    {
        $this->_log('Reading file: ' . $filename);
        $this->_log('');

        // import pre order
        $productData = Mage::getSingleton('integrationbasestandard/xml_parser')->parse($filename, '/integrationbase/product');

        $this->_log('');
        $this->_log(count($productData) . ' products received, adding to integration base');

        foreach ($productData as $key => $product) {
            try {
                $this->_importProduct($product);
                $this->_successCount++;
            } catch (Exception $e) {
                $this->_log($e->getMessage());
                $this->_failureCount++;
            }

        }

        $this->_log('Import completed');

        foreach ($this->_duplicateSkus as $sku => $count) {
            if ($count > 1) {
                $this->_log($sku . ' - ' . $count);
            }
        }
    }
}