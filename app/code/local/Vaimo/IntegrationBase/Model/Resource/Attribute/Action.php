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
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Resource_Attribute_Action extends Mage_Catalog_Model_Resource_Product_Action
{
    protected $_entityType;

    /**
     * Set the entity type of the attribute that we're going to update
     *
     * @param $attributeTypeCode
     */
    public function setEntityType($attributeTypeCode)
    {
        $this->_entityType = $attributeTypeCode;
    }

    /**
     * Fetch an attribute of certain entity type
     *
     * @param int|string $attributeLookupValue
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttribute($attributeLookupValue)
    {
        if ((is_numeric($attributeLookupValue) && !isset($this->_attributesById[$attributeLookupValue]))
            || !is_numeric($attributeLookupValue) && !isset($this->_attributesByCode[$attributeLookupValue])) {
            $attribute = Mage::getModel('eav/config')->getAttribute($this->_entityType, $attributeLookupValue);
            $this->_attributesByCode[$attribute->getAttributeCode()] = $attribute;
            $this->_attributesById[$attribute->getId()] = $attribute;
        }

        return is_numeric($attributeLookupValue) ? $this->_attributesById[$attributeLookupValue] : $this->_attributesByCode[$attributeLookupValue];
    }

    /**
     * Delete an attribute of certain entity type
     *
     * @param $entityIds
     * @param $attributeValues
     * @param $storeId
     */
    public function deleteAttributes($entityIds, $attributeValues, $storeId)
    {
        $object = new Varien_Object();
        foreach ($entityIds as $entityId) {
            foreach ($attributeValues as $attributeCode => $attributeValue) {
                $attribute = $this->getAttribute($attributeCode);

                // I don't want to add value_id to the deletion query - and unfortunately you need it when dealing with
                // global scope attributes. So we just switch every attribute model to scope = store, when we delete so
                // we could delete by attribute_code, entity_type, store_id, etc. The trade-off here is that when Magento
                // Changes the way attribute scope works (starts to store it under another attribute or something) this code
                // does not work any more. So we check that scope was changed - if not - throw an exception.
                $originalScope = $attribute->getData('is_global');
                $attribute->setData('is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE);

                if (!$attribute->isScopeStore()) {
                    Mage::throwException('Temporary attribute scope switch failed');
                }

                $object->setData(array(
                    'id' => $entityId,
                    'entity_type_id' =>  $attribute->getEntityTypeId(),
                    'store_id' => $storeId
                ));

                $info = array(array(
                    'attribute_id' => $attribute->getId()
                ));

                $this->_deleteAttributes($object, $attribute->getBackend()->getTable(), $info);

                // Restore the original scope
                $attribute->setData('is_global', $originalScope);
            }
        }
    }
}