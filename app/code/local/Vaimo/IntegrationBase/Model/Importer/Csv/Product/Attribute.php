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

class Vaimo_IntegrationBase_Model_Importer_Csv_Product_Attribute extends Vaimo_IntegrationBase_Model_Importer_Csv_Abstract
{
    protected $_eventPrefix = 'product_attribute';

    protected function _importRow($data)
    {
        $sku = '';
        $storeId = 0;
        $attributes = array();

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'sku':
                    $sku = $value;
                    break;
                case 'store_id':
                    $storeId = $value;
                    break;
                default:
                    $attributes[$key] = $value;
                    break;
            }
        }

        if (!$sku) {
            Mage::throwException('Sku not specified');
        }

        foreach ($attributes as $key => $value) {
            $value = Mage::helper('integrationbase/attribute')->getAttributeValue(Mage_Catalog_Model_Product::ENTITY, $key, $value, $storeId);
            /** @var Vaimo_IntegrationBase_Model_Attribute $attribute */
            $attribute = Mage::getModel('integrationbase/attribute');
            $attribute->loadByLookup('sku', $sku, Mage_Catalog_Model_Product::ENTITY, $key, $storeId);
            $attribute->setLookupField('sku');
            $attribute->setLookupValue($sku);
            $attribute->setStoreId($storeId);
            $attribute->setEntityType(Mage_Catalog_Model_Product::ENTITY);
            $attribute->setAttributeCode($key);
            $attribute->setAttributeValue($value);
            $attribute->setRawData($data);
            $attribute->setRowStatus(Vaimo_IntegrationBase_Helper_Data::ROW_STATUS_IMPORT);
            $attribute->save();
        }
    }
}