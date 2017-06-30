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
 * @package     Vaimo_Blog
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Tobias Wiking
 */

/** @var Vaimo_Blog_Model_Setup $this */
//Check if the default attribute set name is 'Default', otherwise use 'Standard'
$_attributeSetSkeleton = 'Default';
$entityTypeId = Mage::getModel('eav/entity')
    ->setType('catalog_product')
    ->getTypeId();

$_set = Mage::getModel('eav/entity_attribute_set')
    ->getCollection()
    ->addFieldToFilter('attribute_set_name', $_attributeSetSkeleton)
    ->setEntityTypeFilter($entityTypeId)
    ->getFirstItem()
    ->getAttributeSetId();

if (!$_set) {
    $_attributeSetSkeleton = 'Standard';
}


//Create or get attribute set
$_attrSetId = Icommerce_Eav::getAttributeSetId(Vaimo_Blog_Model_Setup::EAV_ATTR_SET);

if (empty($_attrSetId)) {
    $_attrSetId = Icommerce_Eav::createAttributeSet(Vaimo_Blog_Model_Setup::EAV_ATTR_SET, $_attributeSetSkeleton);

    //Fix sort order bug in Magento when creating new attribute set.
    Icommerce_Db::updateRow(
        'eav_attribute_group', array(
        'sort_order' => 0
    ),
        'attribute_group_name = "General" AND attribute_set_id = ?',
        array($_attrSetId)
    );

    //Save the created attribute set and product type to the config. Users has the oppurtunity to change this later.
    Mage::getModel('core/config')->saveConfig(Mage::helper('blog')->getAttrSetConfigPath(), $_attrSetId);
    Mage::getModel('core/config')->saveConfig(Mage::helper('blog')->getTypeConfigPath(), strtolower(Vaimo_Blog_Model_Setup::EAV_ATTR_SET));

    //Remove sku from blog attribute set
    $this->deleteTableRow(
        'eav/entity_attribute',
        'attribute_id',
        $this->getAttributeId('catalog_product', 'sku'),
        'attribute_set_id',
        $this->getAttributeSetId('catalog_product', Vaimo_Blog_Model_Setup::EAV_ATTR_SET)
    );

    //Remove description from blog attribute set
    $this->deleteTableRow(
        'eav/entity_attribute',
        'attribute_id',
        $this->getAttributeId('catalog_product', 'description'),
        'attribute_set_id',
        $this->getAttributeSetId('catalog_product', Vaimo_Blog_Model_Setup::EAV_ATTR_SET)
    );

    //Remove short_description from blog attribute set
    $this->deleteTableRow(
        'eav/entity_attribute',
        'attribute_id',
        $this->getAttributeId('catalog_product', 'short_description'),
        'attribute_set_id',
        $this->getAttributeSetId('catalog_product', Vaimo_Blog_Model_Setup::EAV_ATTR_SET)
    );
}

//Create attributes
if (!Icommerce_Eav::getAttributeId('blog_publish_date', 'catalog_product')) {
    $aid = Icommerce_Eav::createEavAttribute('blog_publish_date', 'catalog_product', 'special_to_date', array(
        'frontend_label' => 'Publish Date',
        'is_required' => 1,
        'input' => 'select', //frontend_input
        'default_value' => '',
        'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'apply_to' => 'blog',
        'used_in_product_listing' => true,
        'is_used_for_promo_rules' => false,
        'is_configurable'   => false,
        'is_visible_on_front' => false
    ), 100);
    if (!empty($aid)) {
        $this->_bindAttrToSet($aid, $_attrSetId, 100);
    }
}

if (!Icommerce_Eav::getAttributeId('blog_publish_datetime', 'catalog_product')) {
    $aid = Icommerce_Eav::createEavAttribute('blog_publish_datetime', 'catalog_product', 'special_to_date', array(
        'frontend_label' => 'Publish Date & Time',
        'is_required' => 0,
        'input' => 'date', //frontend_input
        'type' => 'datetime',
        'frontend_input_renderer' => 'Vaimo_Blog_Block_Adminhtml_Form_Element_Datetime',
        'backend'       => "eav/entity_attribute_backend_datetime",
        'default_value' => '',
        'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'apply_to' => 'blog',
        'used_in_product_listing' => true,
        'is_used_for_promo_rules' => false,
        'is_configurable'   => false,
        'is_visible_on_front' => false
    ), 105);
    if (!empty($aid)) {
        $this->_bindAttrToSet($aid, $_attrSetId, 105);
    }
}

if (!Icommerce_Eav::getAttributeId('blog_unpublish_datetime', 'catalog_product')) {
    $aid = Icommerce_Eav::createEavAttribute('blog_unpublish_datetime', 'catalog_product', 'special_to_date', array(
        'frontend_label' => 'Unpublish Date & Time',
        'is_required' => 0,
        'input' => 'date', //frontend_input
        'type' => 'datetime',
        'frontend_input_renderer' => 'Vaimo_Blog_Block_Adminhtml_Form_Element_Datetime',
        'backend'       => "eav/entity_attribute_backend_datetime",
        'default_value' => '',
        'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'apply_to' => 'blog',
        'used_in_product_listing' => false,
        'is_used_for_promo_rules' => false,
        'is_configurable'   => false,
        'is_visible_on_front' => false
    ), 106);
    if (!empty($aid)) {
        $this->_bindAttrToSet($aid, $_attrSetId, 105);
    }
}

if (!Icommerce_Eav::getAttributeId('blog_author', 'catalog_product')) {
    $aid = Icommerce_Eav::createEavAttribute('blog_author', 'catalog_product', 'color', array(
        'frontend_label' => 'Author',
        'is_required' => 0,
        'input' => 'select', //frontend_input
        'default_value' => '',
        'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'apply_to' => strtolower(Vaimo_Blog_Model_Setup::EAV_ATTR_SET),
        'used_in_product_listing' => true,
        'is_used_for_promo_rules' => false,
        'is_configurable'   => false,
        'is_searchable' => false,
        'is_visible_in_advanced_search' => false,
        'is_comparable' => false,
        'is_filterable' => 0,
        'is_visible_on_front' => false
    ), 110);
    if (!empty($aid)) {
        $this->_bindAttrToSet($aid, $_attrSetId, 110);
    }
}

if (!Icommerce_Eav::getAttributeId('blog_content', 'catalog_product')) {
    $aid = Icommerce_Eav::createEavAttribute('blog_content', 'catalog_product', 'short_description', array(
        'frontend_label' => 'Text',
        'is_required' => 1,
        'default_value' => '',
        'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'apply_to' => strtolower(Vaimo_Blog_Model_Setup::EAV_ATTR_SET),
        'used_in_product_listing' => true,
        'is_used_for_promo_rules' => false,
        'is_configurable'   => false,
        'is_searchable' => true,
        'is_visible_in_advanced_search' => true,
        'is_comparable' => false,
        'is_filterable' => 0,
        'is_visible_on_front' => false
    ), 120);
    if (!empty($aid)) {
        $this->_bindAttrToSet($aid, $_attrSetId, 120);
    }
}

//Create comment table
$installer = $this;
$installer->startSetup();
$tableComment = $this->getTable('blog/comment');
if (!$installer->tableExists($tableComment)) {
    $installer->run("
            CREATE TABLE IF NOT EXISTS `{$tableComment}` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `product_id` int(10) unsigned NOT NULL,
              `is_approved` smallint(5) NOT NULL,
              `name` varchar(255) NOT NULL,
              `email` varchar(255),
              `url` varchar(255),
              `comment` text NOT NULL,
              `created` DATETIME NOT NULL,
              `ip` varchar(30),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
            ");

    //Add index
    $installer->getConnection()->addKey(
        $installer->getTable('blog/comment'),
        'IDX_BLOG_PRODUCT_ID',
        'product_id'
    );

    //Add foreign key
    $installer->getConnection()->addConstraint('FK_VAIMO_BLOG_PRODUCT',
        $installer->getTable('blog/comment'), 'product_id',
        $installer->getTable('catalog/product'), 'entity_id',
        'CASCADE', 'CASCADE', true);
}

$installer->endSetup();
