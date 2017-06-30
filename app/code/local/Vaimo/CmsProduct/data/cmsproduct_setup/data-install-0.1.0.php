<?php
/**
 * Copyright (c) 2009-2016 Vaimo Group
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
 * @package     Vaimo_CmsProduct
 * @copyright   Copyright (c) 2009-2016 Vaimo Group
 * @author      Andreas Wickberg <andreas.wickberg@vaimo.com>
 */

/** @var Vaimo_CmsProduct_Model_Setup $installer */
$installer = $this;
$installer->startSetup();

$attributeSetName = Vaimo_CmsProduct_Model_Setup::EAV_ATTR_SET;
$entityType = Mage_Catalog_Model_Product::ENTITY;
/** @var Mage_Catalog_Model_Product_Attribute_Set_Api $api */
$api = new Mage_Catalog_Model_Product_Attribute_Set_Api();

/* check if set is already defined; skip if so */
$setId = $installer->getAttributeSet($entityType, $attributeSetName, 'attribute_set_id');
if (empty($setId)) {
    /* we want to use Default or Standard as bases for our set, even if we later will remove most attributes */
    foreach (array('Default', 'Standard') as $skeleton) {
        $skeletonId = $installer->getAttributeSetId('catalog_product', $skeleton);
        if ($skeletonId)
            break;
    }
    if (!$skeletonId) {
        throw new Exception('Couldn\'t find a default attribute set');
    }

    /* cerate the new attribute set */
    $setId = $api->create($attributeSetName, $skeletonId);

    /*
     * we now want to remove most attributes since these 'products' not really are that
     * We also would like to set defaults for some of these, but in this version the admin user must set them
     * - name       used normally
     * - status     used normally
     * - url_key    suspect that some code rely on this
     * - visability must be Catalog
     * - created_at for debugging
     * - updated_at for debugging
     * - price      must be present and set for reindex to pick up the product and display it
     *
     * In addition to these attributes the following must also be set properly:
     * - in_stock   product must be in stock
     * - websites   product must be available in the proper shop
     * - category   product should be placed in the proper category to be visible
     * - cms_block  user must pick which block to display
     * - cms_block_align    defaults to false, true will place the block at the right most position
     */

    $keep = array('name', 'status', 'url_key', 'visibility', 'created_at', 'updated_at', 'price');
    $attributes = Mage::getModel('catalog/product_attribute_api')->items($setId);
    foreach ($attributes as $attribute) {
        if (!in_array($attribute['code'], $keep)) {
            $api->attributeRemove($attribute['attribute_id'], $setId);
        }
    }

    /* add our new attributes */
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, Vaimo_CmsProduct_Model_Setup::CMS_BLOCK_CODE, array(
        'label'            => 'CMS Block',
        'input'            => 'select',
        'required'         => true,
        'unique'           => false,
        'apply_to'         => 'cmsproduct',
        'source'           => 'cmsproduct/attribute_source_cms',
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'position'         => 50,
        'group'            => 'General',
        'used_in_product_listing' => true,
        'is_configurable'   => false
    ));
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, Vaimo_CmsProduct_Model_Setup::CMS_BLOCK_ALIGN_CODE, array(
        'label'            => 'Align Right',
        'type'             => 'int',
        'input'            => 'select',
        'unique'           => false,
        'apply_to'         => 'cmsproduct',
        'source'           => 'eav/entity_attribute_source_boolean',
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'position'         => 60,
        'group'            => 'General',
        'used_in_product_listing' => true,
        'is_configurable'   => false
    ));

    /* change the price attribute to apply to our new product type */
    $old = explode(',', $installer->getAttribute('catalog_product', 'price', 'apply_to'));
    if (!in_array('cmsproduct', $old)) {
        $old[] = 'cmsproduct';
        $installer->updateAttribute('catalog_product', 'price', 'apply_to', implode(',', $old));
    }
}

$installer->endSetup();
