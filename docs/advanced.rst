Advanced
========

Menu types
----------

Each menu type in the module is represented by a simple definition of how the menu behaves on **each level** of the menu. The
configuration is formatted as an array and is later accessible as Varien_Object data (which means that each key is accessible
with standard get function).

Note that all the menu types that come with the module are actually based on BreakPoint type - this is so that the user
would have maximal control over the placement of groups, widgets, etc on each menu template. On some simple cases,
one can create their own menu type that is based on the Base class (which will result in simples HTML output).

========== =========================================================== ======================================================================
Key        Description                                                 Options
========== =========================================================== ======================================================================
direction  Determines the direction for listed items                   DIRECTION_HORIZONTAL, DIRECTION_VERTICAL
children   Determines how the child items are presented                ITEMS_HORIZONTAL_SLIDE, ITEMS_VERTICAL_SLIDE, ITEMS_NESTED, ITEMS_NONE
break_type Determines how the item breakpoint is handled               COLUMNS, ROWS
========== =========================================================== ======================================================================

Note that all options are represented by constants in Vaimo_Menu_Model_Type.

Direction
+++++++++

Indicates in which direction the items on current level flow.

Children
++++++++

If last configured level has this set, the configuration of the last level will also be used for all levels that
come after it. Omitting (or using ITEMS_NONE) this key from last level will not render those levels at all.

Break type
++++++++++

Implementation for break types is kept in the `Vaimo_Menu_Block_Navigation_Type_Breakpoints` class.

Menu type container
-------------------

This is a helper block that is only used for the purpose of making main menu type configurable via system configuration.
Menu block is actually a stand-alone entity that one can add to layout directly (which is exactly the case with vertical
navigation block).

.. _advanced-menu-type-definition:

Menu type definition
--------------------

Menu types are all represented by a block in the layout. This means that although we talk about menu types, in layout
this all resolves to using different block type.

Each of the blocks that is responsible for rendering certain type of menu has type-configuration similar to this defined
in its protected instance variables:

.. code-block:: php
    :emphasize-lines: 2,7

    array(
        0 => array(
            'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
            'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE,
        ),
        1 => array(
            'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL
        )
    )

Note that each array key here represents one level in the menu and the sub-array keys represent the directives for how
the menu (or next level) should be rendered.

Also note that if a level does not have a directive defined for how to render children, the next level will NOT be rendered. So in
current case - the menu will only render two levels.

.. code-block:: php
    :emphasize-lines: 8

    array(
        0 => array(
            'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
            'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE,
        ),
        1 => array(
            'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL,
            'children' => Vaimo_Menu_Model_Type::ITEMS_HORIZONTAL_SLIDE,
        )
    )

In case last level has children rendering directive defined, that levels configuration will be used for all coming levels.
This means that even level 100 will be rendered using directives from array key '1'.

Create a new menu type
----------------------

To create a new menu type you will start by creating a block that inherits from `Vaimo_Menu_Block_Navigation_Type_Base` or
in some cases from `Vaimo_Menu_Block_Navigation_Type_Breakpoints` (if columns are allowed for specific menu type).

It doesn't matter where you place the class but an recommendation is to put it in the `Block/Navigation/Type/` folder.

In the created block you can specify the config that the menu type should use by setting the `_typeConfig`
variable in the constructor.

============ ======================================================================
Variable     Description
============ ======================================================================
_typeClasses Additional CSS classes that be added to the wrapper of the menu output
_typeConfig  Menu behaviour/render directives per level
============ ======================================================================

Example (from the simple drop-down menu)::

Please note that one can create their own menu types the same way.

.. code-block:: php
    :emphasize-lines: 4,8

    <?php
    class Vaimo_Menu_Block_Navigation_Type_SimpleDropDown extends Vaimo_Menu_Block_Navigation_Type_Base
    {
        protected $_typeClasses = array(
            'menu-simple-dropdown'
        );

        protected $_typeConfig = array(
            0 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_HORIZONTAL,
                'children' => Vaimo_Menu_Model_Type::ITEMS_VERTICAL_SLIDE,
            ),
            1 => array(
                'direction' => Vaimo_Menu_Model_Type::DIRECTION_VERTICAL
            )
        );
    }

Note that $_typeClasses will be merged together into space-separated list and will be used on the navigation/menu wrapper
element - so that end-users could apply custom styling.

You can also override any method of the parent class to customise the functionality.

To register the new menu type you will have to add the type to the `config.xml` file in your module. By doing this the
menu will be selectable in the :doc:`administration`.

.. code-block:: xml
    :emphasize-lines: 4-7

    <config>
        <frontend>
            <vaimo_menu>
                <my_menu>
                    <label>My menu</label>
                    <type>my_module/navigation_type_MyMenu</type>
                </my_menu>
            </my_menu>
        </frontend>
    </config>

Menu component blocks
---------------------

There is yet another concept in vaimo/menu that allows certain extensions/components to be added for certain levels of menu.
Basically in layout they will look like child blocks for menu block, but in reality they will only be added on certain levels
of the menu.

An example of this is the menu.image which is implemented as a child block of 'menu.item.after.link' and has very light-weight
configuration in `Vaimo_Menu_Block_Navigation_Component_Parent`. Note that the name of the component refers to the fact that
this component will only render in case the menu item has children. This is determined by the function getShouldRender
`getShouldRender` that every menu component block should implement.

Note that by default the component has been set to render on level -1, which means that it will never render, in the standard
vaimo/menu setup, the component level for menu image is determined by the menu type block (in this case, by `Vaimo_Menu_Block_Navigation_Type_SimpleNestedDropDownImageColumns`).

So in short: the component will render if menu item level matches with component level and getShouldRender returns true.

For example, if you would like menu item to render even when menu item has no children, this simple layout update will
make image show on any level-item that has menu image defined.

.. code-block:: xml
    :emphasize-lines: 2,4

    <reference name="menu.item.after.link">
        <action method="unsetChild"><name>menu.item.image</name></action>
        <block type="core/template" name="menu.item.image" template="vaimo/menu/menu_image.phtml">
            <action method="setShouldRender"><bool>1</bool></action>
        </block>
    </reference>

In this case we just replace the component block that was there before and replace it with simple core/template block
that has shouldRender set to TRUE no questions asked.

Menu templates
--------------

There are three types of templates in the vaimo/menu module that all share the same fallback which will mean that it's
enough to define only one template, but one has to take into account the fact that this will result all the menu rendering
components to use the same output pattern, which might not give desired result.

All of the methods described below are block actions, which means that they are configurable via layout.

====================== ===================================================================== ======================================================================
Method                 Description                                                           Remarks
====================== ===================================================================== ======================================================================
setItemTemplate        Used to output single menu item and wrapper for it's children
setBreakpointTemplate  Used to output a column or row in single menu level                   If this template is not defined, template set with setItemTemplate is used.
setGroupTemplate       Used to output certain menu group                                     Allows the user to output 'footer' in a differently than default items
setGroupItemTemplate   Used to define special template for each item in certain menu group   By default the group items will use the default item template.
====================== ===================================================================== ======================================================================

Note that one can define different template for each menu group and level.

Menu type specific layout updates
---------------------------------

To update the layout of a specific menu type you can use the `vaimo_menu_types.xml` layout file. The menu types in this
file will be merged with the global layout depending on what menu type is selected in the :doc:`administration`.

Example of how to remove a javascript file from the header of the Simple Drop-down menu type:

.. code-block:: xml
   :emphasize-lines: 3-5

    <layout version="0.1.0">
        <vaimomenu_type_simple_dropdown>
            <reference name="head">
                <action method="removeItem"><type>js</type><name>varien/menu.js</name></action>
            </reference>
        </vaimomenu_type_simple_dropdown>
    </layout>

The element name should always start with `vaimomenu_type_` and end with the menu type element name from `config.xml`.

A good example of how menu-type based layout updates could be utilized can be seen within the module (see menu with image type).

You can also update the layout only on a store view by appending the store view code to the element name:

.. code-block:: xml
   :emphasize-lines: 2,6

        <layout version="0.1.0">
            <vaimomenu_type_simple_dropdown_store1>
                <reference name="head">
                    <action method="removeItem"><type>js</type><name>varien/menu.js</name></action>
                </reference>
            </vaimomenu_type_simple_dropdown_store1>
        </layout>

.. _advanced-configuring-menu-for-specific-store:

Configuring menu for specific store
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

One additional way of manipulating layout/menu block is to do it per-store (which will apply for any type selected in
store that has the code `store1`:

.. code-block:: xml
   :emphasize-lines: 2,6

        <layout version="0.1.0">
            <vaimomenu_type_default_store1>
                <reference name="head">
                    <action method="removeItem"><type>js</type><name>varien/menu.js</name></action>
                </reference>
            </vaimomenu_type_default_store1>
        </layout>

Menu groups
-----------

Each level of the menu can be divided into multiple visual areas/groups - which will give the developer the power to
display items in single level in different places in the template.

Adding new groups
~~~~~~~~~~~~~~~~~

The standard module uses this for footer functionality, but one can quite easily extend it by adding extra
types in config.xml (adding them under 'default' menu type) after which they will end up being visible in admin under
Catalog >> Manage Categories >> Menu tab.

.. code-block:: xml
   :emphasize-lines: 3,4
            <default>
                <groups>
                    <main><label>Main</label></main>
                    <footer><label>Footer</label></footer>
                    <my_group><label>Group</label></my_group>
                </groups>
            </default>

Note that default group is 'main' and that should not be removed as it will make the menu return nothing at all.

.. _advanced-using-groups-in-templates:

Using groups in templates
~~~~~~~~~~~~~~~~~~~~~~~~~

There are two ways one can configure groups and later use then in templates (Note that we use group 'footer' for the sake
of clarity and to give developer a good reference to the actual code in the module, but it could be named anything).

1. Parent category node with group
2. Child category node without children with group

The way those are processed on templates is determined by the developer who uses one of the two methods that come with
the module.

.. code-block:: html
   :emphasize-lines: 2,5

    <!-- 1. Will render items from the same level, so if some items under certain category are flagged as footer, they will be listed-->
    <?php echo $this->renderGroup('footer') ?>

    <!-- 2. Will render items that are under category item that has the flag 'footer'. All that kind of categories will be collected and output under each other -->
    <?php echo $this->renderGroupChildren('footer') ?>

    <!-- This will render children directly (without group wrapper) if there are no other groups in this category level, but will use groups if there are -->
    <?php echo $this->renderChildren('main') ?>

These kind of groups can have different wrapper templates (items themselves have normal menu item template) which are configured in layout.xml

.. code-block:: xml
   :emphasize-lines: 4

        <reference name="top.menu">
            <action method="unsetChild"><name>catalog.topnav</name></action>
            <reference name="catalog.topnav">
                <action method="setGroupTemplate"><file>vaimo/menu/my_group.phtml</file><group>footer</group></action>
            </block>
        </reference>

One can also do this per level

.. code-block:: xml
   :emphasize-lines: 4

        <reference name="top.menu">
            <action method="unsetChild"><name>catalog.topnav</name></action>
            <reference name="catalog.topnav">
                <action method="setGroupTemplate"><file>vaimo/menu/my_group.phtml</file><group>footer</group><level>2</level></action>
            </block>
        </reference>

Menu widgets
------------

Widgets for this module have been implemented in the context of standard Magento CMS >> Widgets, so every widget defined
on a category image will actually end up creating a widget instance in CMS >> Widgets -- note that one can then configure
the widget either via Manage Category page or from Manage Widget form in CMS >> Widgets.

The reason for using this approach is to make defined widgets very visible and accessible via database queries (rather
than using CMS directives to define them).

Although this guide gives full guidance how one can add their own widget attribute, note that the module already comes
with one widget defined per category, which can be used as a guidance when adding extra widgets.

NOTE: In default implementation, menu item widget is supposed to show next to its sub-items. It will not show if there
are no sub-items in place. Note that this can be changed by editing menu templates (by using 'getWidgetHtml' in a different
place).

Creating new widget attribute
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Widget attribute is typically an attribute that has type 'widget' and is part of the normal EAV framework. The value is
stored as integer as this attribute will only server as a reference to Widget Instances (which refer to records visible
in CMS >> Widgets).

To create new attribute, one can create new update script (data update).

.. code-block:: php

    $this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'menu_widget', array(
        'type'                       => 'int',
        'label'                      => 'Widget',
        'input'                      => 'widget',
        'frontend'                   => 'vaimo_menu/entity_attribute_frontend_widget',
        'backend'                    => 'vaimo_menu/entity_attribute_backend_widget',
        'sort_order'                 => 50,
        'required'                   => false,
        'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'group'                      => 'Menu',
        'note'                       => 'The widget will be accessible as block on category item rendering in menu'
    ));

    $this->updateAttribute(
        'catalog_category',
        'menu_widget',
        'frontend_input_renderer',
        'vaimo_menu/adminhtml_catalog_category_attribute_widget'
    );

Linking the attribute to certain layout handle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that we have our attribute at place, we also have to inform Magento about the target/reference block that is supposed
to render the widget that is referred via the attribute we created. This is done via block label attribute.

Note that standard Magento CMS >> Widget will allow you to add widgets into all blocks that have <label> parameter defined
in layout.xml - if there is no label defined for a block, it will not show up as an option. We take advantage of this fact
and extend it to have awareness of our widget reference attributes.

.. code-block:: xml
   :emphasize-lines: 5

    <layout version="0.1.0">
        <default>
            <reference name="top.menu">
                <block type="vaimo_menu/navigation" name="catalog.topnav" template="vaimo/menu/top.phtml">
                    <label attributes="menu_widget">Top Menu</label>
                </block>
            </reference>
        </default>
    </layout>

Note that one attribute can be linked to multiple blocks - so you can have two different menu blocks that both will get
the configured widget and can use it.

After making the change mentioned above, a category that has widget set, will trigger Widget Instance creation for
all the blocks that have the attribute link. In this case 'catalog.topnav' will receive configured widget as it's child
when layout blocks are generated.

In short, what happens here:

1. block is made available as CMS >> Widget target (by adding <label>)
2. block as a widget container is declared as a target for any widget that gets saved for widget attribute 'menu_widget'

Using widget in templates
~~~~~~~~~~~~~~~~~~~~~~~~~

As most of the menu is configured via re-using same block, one has to avoid calling getChildrenHtml directly and has
to result filtering out the correct child blocks that have to be rendered for each level/item. For this, a new method
was introduced (which is part of the Base menu type). To render a widget from a specific widget attribute:

.. code-block:: phtml
   :emphasize-lines: 4, 6

    <li class="<?php echo $this->getItemHierarchyClass() ?>">
        <ul class="group-items">
            <?php echo $this->renderChildren() ?>
            <?php if ($this->hasWidget('menu_widget')): ?>
                <li class="<?php echo $this->getItemPlacementClass(true) ?> widget-column">
                    <?php echo $this->getWidgetHtml('menu_widget') ?>
                </li>
            <?php endif ?>
        </ul>
    </li>

Note that it's possible to call getWidgetHtml from any template. From group, breakpoint and from item template.

Add extra attributes to the menu
--------------------------------

If you need to read specific category attributes that are not standard you can add them to the layout file like this:

.. code-block:: xml

    <action method="setExtraAttributes"><extra_attributes>attribute1,attribute2</extra_attributes></action>

Events
------

Events fired by the module

================================= ========================================================= =============================================================================
Event                             Origin                                                    Remarks
================================= ========================================================= =============================================================================
vaimo_menu_load_categories_before app/code/local/Vaimo/Menu/Model/Catalog/Category/Tree.php Access to category collection/select. Access to sorting order.
vaimo_menu_load_categories_after  app/code/local/Vaimo/Menu/Model/Catalog/Category/Tree.php Access to already loaded items (before the tree structure is generated).
================================= ========================================================= =============================================================================

Custom template on specific level
---------------------------------

By standard, all levels use same template, if one wants to design some menu level differently, they can use following
approach via layout.xml

.. code-block:: xml
    :emphasize-lines: 4

        <layout version="0.1.0">
            <vaimomenu_type_default_store1>
                <reference name="catalog.topnav">
                    <action method="setItemTemplate"><file>custom_item.phtml</file><level>2</level></action>
                </reference>
            </vaimomenu_type_default_store1>
        </layout>

.. _advanced-cache:

Custom secondary menus
----------------------

By default, only single tree is rendered using store root category. It is possible to render multiple menus, for example
secondary navigation starting from different part of the tree. For this custom root category id can be specified in the
layout xml:

.. code-block:: xml

    <action method="setCustomRootId"><category>23</category></action>

Note that this category must still exist under store root category tree. If this custom root category shouldn't show in
the main navigation menu or exist in breadcrumbs, it can be set inactive from Magento admin.

Cache
-----

The menu data is cached in two places, one of them being on the category data fetching level and the other caching
happens when menu tree is rendered for a certain menu block type. Both of these cache records will have a specific
cache tag added to them: VAIMO_MENU which the store owner can invalidate via Magento admin.

Both caches use class instance cache as well - this means that deserialized data from the cache is kept in the
instance which will mean that even when cache is updated - as long as the instance remains in the current Magento
instantiation, the last cached data will not be available.

The same category data cache will be used by all menu blocks, but all menu type blocks will have their own cache
that is related to their menu type code defined in the protected attribute.

Different cache levels implemented by the module (with reasoning):

====================== =========================================================================================================================
Cache                  Description
====================== =========================================================================================================================
Collection cache       Full cache of all the categories available for building the menu in a form of a flat list (used by all menu types)
Structure cache        Cache of the tree-structure that will be used to render the menu (leaving room for setting items as active on render, etc)
Block cache            Normal Magento block cache. The whole block output is cached
====================== =========================================================================================================================

Invalidation
~~~~~~~~~~~~

The underlying category tree cache will be updated on following cases:

* Exclusive invalidation is called on the cache model
* New attribute is added to the category query

Note that cache is invalidated on every category save by default. This can be disabled from the module system configuration
if needed as the behaviour might cause unnecessary load on production machines.

Lifetime
~~~~~~~~

The amount of time the data is cached by default is 2 hours. This can be extended though via block actions.

====================== =========================================================================================================================
Method                 Description
====================== =========================================================================================================================
setDataCacheLifetime   Indicates how often the underlying tree data that is uses to generate the tree gets updated. Used by renderMenu function.
setBlockCacheLifetime  Sets the cache time and activates the block caching for the menu block. Used by toHtml function.
====================== =========================================================================================================================

Please note that by default block cache is disabled and gets enabled only when block cache lifetime is set. Also note
that although tree data is cached, the menu html is generated on each load (which means that html will always be up
to date and affected by navigation state).

Problems & Solutions
--------------------

Known problems and limitations
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When switching to this module:

* It will conflict with Icommerce_VertNav in your layout, just remove those references.
* Remove all other menu modules (or at least inactivate them).