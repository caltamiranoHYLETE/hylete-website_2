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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Import_Link extends Vaimo_IntegrationBase_Model_Import_Abstract
{
    protected $_eventPrefix = 'product_link';

    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_productModel = null;

    /**
     * @var Mage_Catalog_Model_Resource_Product_Link
     */
    protected $_productLinkModel = null;
    protected $_linkTypeTable = null;
    protected $_linkTable = null;
    protected $_linkTypes = array();
    protected $_linkAttributes = array();

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/link', 'Product linking');
        $this->_successMessage = '%d product(s) linked';
        $this->_failureMessage = '%d product(s) failed to link';
        $this->_productModel = Mage::getModel('catalog/product');
        $this->_productLinkModel = Mage::getResourceModel('catalog/product_link');
        $this->_linkTypeTable = $this->_getTableName('catalog/product_link_type');
        $this->_linkTable = $this->_getTableName('catalog/product_link');
    }

    /**
     * Gets link type id based on code.
     * If link type doesn't exist, it creates new
     *
     * @param string $code
     * @return int
     */
    public function getLinkTypeId($code)
    {
        if (!isset($this->_linkTypes[$code])) {
            $select = $this->_getRead()
                ->select()
                ->from($this->_linkTypeTable)
                ->where('code = :code');

            $bind = array(':code' => $code);

            $linkId = $this->_getRead()->fetchOne($select, $bind);

            if (!$linkId) {
                $this->_getWrite()->insert($this->_linkTypeTable, array('code' => $code));
                $linkId = $this->_getWrite()->lastInsertId($this->_linkTypeTable);
            }

            $this->_linkTypes[$code] = $linkId;
        }

        return $this->_linkTypes[$code];
    }

    /**
     * Gets link id
     *
     * @param int $productId
     * @param int $linkedProductId
     * @param int $linkTypeId
     * @return int
     */
    public function getLinkId($productId, $linkedProductId, $linkTypeId)
    {
        $select = $this->_getRead()
            ->select()
            ->from($this->_linkTable)
            ->where('product_id = :product_id')
            ->where('linked_product_id = :linked_product_id')
            ->where('link_type_id = :link_type_id');

        $bind = array(
            ':product_id' => (int)$productId,
            ':linked_product_id' => (int)$linkedProductId,
            ':link_type_id' => (int)$linkTypeId,
        );

        $id = $this->_getRead()->fetchOne($select, $bind);

        return $id;
    }

    /**
     * Gets all attributes for link type
     *
     * @param int $linkTypeId
     * @return array
     */
    protected function _getLinkAttributes($linkTypeId)
    {
        if (!isset($this->_linkAttributes[$linkTypeId])) {
            $this->_linkAttributes[$linkTypeId] = $this->_productLinkModel->getAttributesByType($linkTypeId);
        }

        return $this->_linkAttributes[$linkTypeId];
    }

    /**
     * @param string $type
     * @param string $value
     * @return float|int|string
     */
    protected function _prepareAttributeValue($type, $value)
    {
        if ($type == 'int') {
            $value = (int)$value;
        } elseif ($type == 'decimal') {
            $value = (float)sprintf('%F', $value);
        }

        return $value;
    }

    /**
     * Creates link and link attribute values
     *
     * @param int $productId
     * @param int $linkedProductId
     * @param int $linkTypeId
     * @param array $data
     */
    protected function _addLink($productId, $linkedProductId, $linkTypeId, $data)
    {
        $linkId = $this->getLinkId($productId, $linkedProductId, $linkTypeId);

        if (!$linkId) {
            $bind = array(
                'product_id' => $productId,
                'linked_product_id' => $linkedProductId,
                'link_type_id' => $linkTypeId,
            );

            $this->_getWrite()->insert($this->_linkTable, $bind);
            $linkId = $this->_getWrite()->lastInsertId($this->_linkTable);
        }

        $attributes = $this->_getLinkAttributes($linkTypeId);

        foreach ($attributes as $attributeInfo) {
            if (isset($data[$attributeInfo['code']])) {
                if ($attributeTable = $this->_productLinkModel->getAttributeTypeTable($attributeInfo['type'])) {
                    $value = $this->_prepareAttributeValue($attributeInfo['type'], $data[$attributeInfo['code']]);
                    $bind = array(
                        'product_link_attribute_id' => $attributeInfo['id'],
                        'link_id'                   => $linkId,
                        'value'                     => $value
                    );
                    $this->_getWrite()->insertOnDuplicate($attributeTable, $bind, array('value'));
                }
            }
        }
    }

    /**
     * Imports link
     *
     * @param Vaimo_IntegrationBase_Model_Link $item
     * @return bool
     */
    protected function _importRecord($item)
    {
        $this->_log($item->getLinkTypeCode() . ': ' . $item->getProductSku() . ' <- ' . $item->getLinkedProductSku() . ' (create)');

        $productId = $this->_productModel->getIdBySku($item->getProductSku());

        if (!$productId) {
            Mage::throwException('Product not found ' . $item->getProductSku());
        }

        $linkedProductId = $this->_productModel->getIdBySku($item->getLinkedProductSku());

        if (!$linkedProductId) {
            Mage::throwException('Linked product not found ' . $item->getLinkedProductSku());
        }

        $linkTypeId = $this->getLinkTypeId($item->getLinkTypeCode());

        $this->_addLink($productId, $linkedProductId, $linkTypeId, $item->getLinkData());

        return $this;
    }

    /**
     * Deletes link
     *
     * @param Vaimo_IntegrationBase_Model_Link $item
     * @return bool
     */
    protected function _deleteRecord($item)
    {
        $this->_log($item->getLinkTypeCode() . ': ' . $item->getProductSku() . ' <- ' . $item->getLinkedProductSku() . ' (delete)');

        $productId = $this->_productModel->getIdBySku($item->getProductSku());
        $linkedProductId = $this->_productModel->getIdBySku($item->getLinkedProductSku());
        $linkTypeId = $this->getLinkTypeId($item->getLinkTypeCode());

        if ($linkId = $this->getLinkId($productId, $linkedProductId, $linkTypeId)) {
            $this->_getWrite()->delete($this->_linkTable, array('link_id = ?' => $linkId));
        }

        return $this;
    }
}