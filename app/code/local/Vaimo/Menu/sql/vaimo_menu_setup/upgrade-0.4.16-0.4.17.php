<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

$this->startSetup();

/**
 * Moving already existing item under 'Menu' tab for better attribute grouping (Note: this attribute is created by
 * std. Magento).
 */
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'include_in_menu', array(
    'type'                       => 'int',
    'label'                      => 'Include in Navigation Menu',
    'input'                      => 'select',
    'source'                     => 'eav/entity_attribute_source_boolean',
    'default'                    => '1',
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Menu',
    'sort_order'                 => 10,
));

/**
 * Create an attribute that would indicate that the category entity will act as column breakpoint in multi-column
 * sub-menu setups (next item in same menu-level will be the first item of the new column).
 */
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'column_breakpoint', array(
    'type'                       => 'int',
    'label'                      => 'List Breakpoint',
    'note'                       => 'Break the category list into new column/row after this category',
    'input'                      => 'select',
    'source'                     => 'eav/entity_attribute_source_boolean',
    'required'                   => false,
    'default'                    => 0,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Menu',
    'sort_order'                 => 20,
));

/**
 * Create secondary image attribute that would be used for menu if set
 */
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'menu_image', array(
    'label'                      => 'Menu Image',
    'note'                       => 'Image that is shown for the menu item of current category (placement based on template)',
    'input'                      => 'image',
    'backend'                    => 'catalog/category_attribute_backend_image',
    'required'                   => false,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Menu',
    'sort_order'                 => 30,
));

/**
 * Create group parameter that allows sub-sections for menu tree to be created (per menu level)
 */
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'menu_group', array(
    'label'                      => 'Menu Group',
    'note'                       => 'Indicates in which visual group the menu item will be presented',
    'input'                      => 'select',
    'default'                    => Vaimo_Menu_Model_Group::DEFAULT_GROUP,
    'source'                     => 'vaimo_menu/entity_attribute_source_menu_group',
    'backend'                    => 'eav/entity_attribute_backend_array',
    'required'                   => false,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Menu',
    'sort_order'                 => 40,
));

/**
 * Link to specific Magento widget instance
 */
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'menu_widget', array(
    'type'                       => 'int',
    'label'                      => 'Widget',
    'note'                       => 'The widget will be accessible as block on category item rendering in menu',
    'input'                      => 'widget',
    'frontend'                   => 'vaimo_menu/entity_attribute_frontend_widget',
    'backend'                    => 'vaimo_menu/entity_attribute_backend_widget',
    'required'                   => false,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'                      => 'Menu',
    'sort_order'                 => 50,
));

$this->updateAttribute(
    'catalog_category',
    'menu_widget',
    'frontend_input_renderer',
    'vaimo_menu/adminhtml_catalog_category_attribute_widget'
);

$this->endSetup();