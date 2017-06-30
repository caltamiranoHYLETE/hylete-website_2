<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
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
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */
$installer = $this;
$installer->startSetup();
$catalogSetup = new Mage_Catalog_Model_Resource_Setup('core_setup');
//Create Back It Attributes
$catalogSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'back_it_goal', array(
    'label' => 'Back It Goal',
    'note' => 'How many pre-orders do you need to back this 100%',
    'apply_to' => 'configurable',
    'type' => 'text',
    'input' => 'text',
    'frontend_class' => 'validate-digits',
    'required' => false,
    'unique' => false,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'position' => 50
));
$catalogSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'back_it_end_datetime', array(
    'label' => 'Back It End Datetime',
    'apply_to' => 'configurable',
    'type' => 'text',
    'input' => 'date',
    'frontend_class' => 'validate-date',
    'required' => false,
    'unique' => false,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'position' => 50
));
$catalogSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'back_it_original_stock', array(
    'label' => 'Back It Max Stock',
    'note' => 'The original stock for this simple product. Should also be the maximum number of pre-orders/backers for this simple products',
    'apply_to' => 'simple',
    'type' => 'text',
    'input' => 'text',
    'frontend_class' => 'validate-digits',
    'required' => false,
    'unique' => false,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'position' => 50
));
//Add new attributes to all attribute sets
$attributes = array(
    $catalogSetup->getAttribute('catalog_product', 'back_it_goal'),
    $catalogSetup->getAttribute('catalog_product', 'back_it_end_datetime'),
    $catalogSetup->getAttribute('catalog_product', 'back_it_original_stock')
);
$allAttributeSetIds = $catalogSetup->getAllAttributeSetIds('catalog_product');
foreach ($allAttributeSetIds as $attributeSetId) {
    //try block is to check whether “Pre-Order / Shipping Delays / Coming Soon” attribute group exists.
    //If it doesn’t, attribute will be added in default attribute group.
    try {
        $attributeGroup = $catalogSetup->getAttributeGroup('catalog_product', $attributeSetId, 'Pre-Order / Shipping Delays / Coming Soon');
    } catch (Exception $e) {
        $attributeGroup = $catalogSetup->getDefaultAttributeGroupId('catalog/product', $attributeSetId);
    }
    foreach ($attributes as $attribute) {
        $catalogSetup->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroup['attribute_group_id'], $attribute['attribute_id']);
    }
}
$installer->endSetup();