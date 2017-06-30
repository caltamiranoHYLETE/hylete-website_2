Quick Start
===========

The module contains multiple menu types that are ready to use in production. These can be selected by the administrator
in :doc:`administration`.

.. _quick-start-included-menu-types:

Included menu types
-------------------

==================================================== ===================================================================
Name                                                 Description
==================================================== ===================================================================
Compatibility mode                                   Simple menu functionality without most of the advanced features
Single level in drop-down                            Single level in a drop-down (no extra drop-downs)
Single level in drop-down (columns)                  Single level in a drop-down presented as columns
Multilevel in multiple drop-downs                    Multiple levels, each in a separate drop-downs
Nested levels in drop-down                           Multiple levels in a drop-down
Nested levels drop-down (columns on first level)     Multiple levels in a drop-down where first sub-level has columns
Nested levels drop-down (columns on multiple levels) Multiple levels in a drop-down where all sub-levels have columns
Nested levels drop-down (columns + image)            Nested levels drop-down (columns on first level) with images
==================================================== ===================================================================

Note that all columns can be user-defined (with breakpoints) in Category Management.

Only compatibility mode has support for infinite display levels. Other menu type definitions Enforce their own rules
in case rendered levels configuration is set higher than menu type allows.

See :ref:`advanced-menu-type-definition`.

All menu types (except comaptibility mode) support built-in menu areas: Main Footer

.. _quick-start-cache:

Cache
-----

The module implements it's own cache type for allowing the user invalidate only the data that is directly associated with
menu rendering. Those cache records are all tagged with VAIMO_MENU tag. To invalidate menu and to make sure that
the latest data is used - invalidate the cache from System >> Cache Management in admin.

See :ref:`advanced-cache`.

Rendering options
-----------------

The menu rendering can be customized with following block actions

=========================================== ==================================================================================
Name                                                 Description
=========================================== ==================================================================================
setStartLevel                                        Define from what level the categories should be rendered
setDisplayLevels                                     Define how many levels after the start level should be rendered
setOnlySkipIfInCurrentPath                           Render children for only those levels that contain items from active category path. For others - render only first item.
setCustomRootFromActiveItemsAncestorAtLevel          Render only that menu branch (and from defined level) that matches active category path. StartLevel will be considered to be counted from this level.
setCustomRootId                                      Render only that menu branch matching specified category id (must exist under store's category tree).
=========================================== ==================================================================================

Note that all levels used here are relative to actual levels of the items in the back-end category tree. So start level '2' would refer
to items that come after the root category (root itself being level-1 category).

Customise an existing menu type
-------------------------------

After you have installed the module there can be specific requirements to customise the menu, both when it
comes to the appearance and behaviour. Because of the extensibility of the module, customising an existing menu type
is a matter of adding layout XML and copying or creating templates.

You can customise an existing menu type in multiple ways. The first thing you can do is to change the behaviour
of the menu by overriding the menu layout XML. By doing this you can set different settings for the menu.

(Note that vaimo_menu/container is only needed if the menu-type needs to be configurable from System >> Admin)

.. code-block:: xml
   :emphasize-lines: 10-14

    <layout version="0.1.0">
        <default>
            <reference name="head">
                <action method="addItem"><type>skin_css</type><name>css/vaimo_menu.css</name></action>
                <action method="addItem"><type>skin_js</type><name>js/vaimo_menu.js</name></action>
            </reference>
            <reference name="top.menu">
                <action method="unsetChild"><name>catalog.topnav</name></action>
                <block type="vaimo_menu/container" name="catalog.topnav" template="vaimo/menu/top.phtml">
                    <action method="setMenuType"><type helper="vaimo_menu/getTopMenuType"/></action>
                    <action method="setStartLevel"><start_level>2</start_level></action>
                    <action method="setDisplayLevels"><display_levels>4</display_levels></action>
                    <action method="setExtraAttributes"><extra_attributes/></action>
                    <action method="setItemTemplate"><item_template>vaimo/menu/top_item.phtml</item_template></action>
                </block>
            </reference>
        </default>
    </layout>


.. note::

    The `<action method="setMenuType"><type helper="vaimo_menu/getTopMenuType"/></action>` action will select the menu
    type configured in :doc:`administration`. You can explicitly set the menu type here if you want to override it.


The second thing you can do is to override the included templates or create new ones. There are two templates to care
about, the top template and the item template. The top template is the rendering template that wraps around the menu
and the item template is the template used for rendering items. To override the default templates, copy these files
to your own module::

    vaimo/menu/top.phtml
    vaimo/menu/top_item.phtml

To create new templates you will have to override the layout XML and set the `template` argument to the new top template
and use the `setItemTemplate` method in the layout XML to refer to the new item template.

Set the top template to `vaimo/menu/my_top.phtml`:

.. code-block:: xml

    <block type="vaimo_menu/container" name="catalog.topnav" template="vaimo/menu/my_top.phtml">

Set the item template to `vaimo/menu/my_top_item.phtml`:

.. code-block:: xml

    <action method="setItemTemplate"><item_template>vaimo/menu/my_top_item.phtml</item_template></action>

You can also override the CSS and Javascript files by copying the files from the module to your own module or by
overriding the layout XML.

To learn about creating new menu types and more continue to :doc:`advanced`.
