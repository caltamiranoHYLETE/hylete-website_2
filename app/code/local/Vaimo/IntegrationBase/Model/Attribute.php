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

class Vaimo_IntegrationBase_Model_Attribute extends Vaimo_IntegrationBase_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/attribute');
    }

    /**
     * Load fast attribute import data by different parameters
     *
     * @param $entityId
     * @param $entityType
     * @param $attributeCode
     * @param int $storeId
     * @return $this
     */
    public function loadByCode($entityId, $entityType, $attributeCode, $storeId = 0)
    {
        $this->_getResource()->loadByKeys($this, array(
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'attribute_code' => $attributeCode,
            'store_id' => $storeId
        ));

        return $this;
    }

    /**
     * Load record by lookup_field and lookup_value
     *
     * @param string $lookupField
     * @param string $lookupValue
     * @param string $entityType
     * @param string $attributeCode
     * @param int $storeId
     * @return $this
     */
    public function loadByLookup($lookupField, $lookupValue, $entityType, $attributeCode, $storeId = 0)
    {
        $this->_getResource()->loadByKeys($this, array(
            'lookup_field' => $lookupField,
            'lookup_value' => $lookupValue,
            'entity_type' => $entityType,
            'attribute_code' => $attributeCode,
            'store_id' => $storeId
        ));

        return $this;
    }

    public function updateValue($lookupId, $entityType, $attributeCode, $value, $storeId = 0)
    {
        $this->loadByCode($lookupId, $entityType, $attributeCode, $storeId);

        if (!$this->getId() || $value != $this->getAttributeValue()) {
            $this->setEntityId($lookupId)
                ->setEntityType($entityType)
                ->setAttributeCode($attributeCode)
                ->setStoreId($storeId)
                ->setAttributeValue($value)
                ->setRowStatus(Vaimo_IntegrationBase_Helper_Data::ROW_STATUS_IMPORT)
                ->save();
        }
    }
}