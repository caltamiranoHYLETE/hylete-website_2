<?php
/**
 * Copyright (c) 2009-2012 Vaimo AB
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
 * @package     Icommerce_ImageBinder
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 */

class Icommerce_Imagebinder_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Holds all the data about product links (which parent links to which children)
     *
     * @var array
     */
    protected $_productSuperlinkReference = array();

    /**
     * Holds all option id/label references for certain attributes
     *
     * @var array
     */
    protected $_optionIdReference = null;

    /**
     * Modifies image binder process by adding all same-color child products (all sizes) to image binding process. Thus
     * all of them will receive the images in their galleries. Take note that this function quite intentionally avoids
     * loading product model (to avoid memory-leaking and breaking the whole process), but still tries to be true to
     * generating the SQL fetch statement with core principles. Does currently expect the value of $similarAttribute to
     * remain the same through out the life of the singleton.
     *
     * @param string $parentSku Parent product id
     * @param string $similarAttribute   Attribute that will be used to determine which children should share the images.
     * @param string $attributeOptionValue  Label of the attribute option to fetch the option ID with
     * @param bool $optionValueStoredInLabel
     *
     * @return array list of child product ids
     */
    public function getAllChildrenWithSameAttributeOption($parentSku, $similarAttribute, $attributeOptionValue, $optionValueStoredInLabel = false)
    {
        // Lazy-load the value->id references
        if (!$this->_optionIdReference) {
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $similarAttribute);
            $options = $attribute->getSource()->getAllOptions(false);

            $this->_optionIdReference = array();
            foreach ($options as $option) {
                $this->_optionIdReference[$option['value']] = $option['label'];
            }
        }

        if (!isset($this->_productSuperlinkReference[$parentSku])) {
            $productId = Mage::getSingleton('catalog/product')->getIdBySku($parentSku);

            // Fake the product object
            $collection = Mage::getSingleton('catalog/product_type_configurable')
                ->getUsedProductCollection(new Varien_Object(array('id' => $productId)));

            // We want the WHERE to remain the same, but query to be improved right away (addAttributeToSelect applied on load() rather than right away).
            // Otherwise we would end up with attribute filter (but we just want the JOIN)
            $select = $collection->getSelect();
            $where = $select->getPart(Zend_Db_Select::WHERE);
            $collection->addAttributeToFilter($similarAttribute);
            $select->setPart(Zend_Db_Select::WHERE, $where);

            // Fetch all the items and add them to our superLink reference for later usage (other images with same SKU)
            $children = Mage::getSingleton('core/resource')->getConnection('core_read')
                ->fetchAll($collection->getSelect());

            $this->_productSuperlinkReference[$parentSku] = array();
            foreach ($children as $child) {
                if ($optionValueStoredInLabel) {
                    $optionReference = $this->_optionIdReference[$child[$similarAttribute]];
                } else {
                    $optionReference = $child[$similarAttribute];
                }

                if (!isset($this->_productSuperlinkReference[$parentSku][$optionReference])) {
                    $this->_productSuperlinkReference[$parentSku][$optionReference] = array();
                }

                $this->_productSuperlinkReference[$parentSku][$optionReference][] = $child['entity_id'];
            }
        }

        if (isset($this->_productSuperlinkReference[$parentSku][$attributeOptionValue])) {
            return $this->_productSuperlinkReference[$parentSku][$attributeOptionValue];
        }

        return array();
    }
}