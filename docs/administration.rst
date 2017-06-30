Administration
==============

Module Configuration
--------------------

.. code-block:: none

    Magento Admin >> System >> Configuration >> Vaimo Modules >> Menu

General settings that affect some very global way of the way menu works.

================================= ====== ===============================================================================
Setting                           Scope  Description
================================= ====== ===============================================================================
Top Menu Type                     Store  Select which menu type to use for the store
Invalidate Cache On Category Save Store  Refers to category save from admin (when disabled: <br /> manual invalidation needed)
================================= ====== ===============================================================================

See :ref:`quick-start-included-menu-types`.

Notes on previous version of the module
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The module configuration used to have templates and attributes configured via System Configuration, but this has
now been changed to be configured via layout.xml

See :ref:`advanced-configuring-menu-for-specific-store`.


Cache Settings
--------------

.. code-block:: none

    Magento Admin >> System >> Cache Management

Determines the state of menu cache.

================================= ====== ===============================================================================
Setting                           Scope  Description
================================= ====== ===============================================================================
Layouts                           Global Involved in widget usage and presence (database layout updates)
Menu Structure                    Global Cached menu structure, urls and widget information
================================= ====== ===============================================================================

See :ref:`quick-start-cache`.

URL settings
------------

.. code-block:: none

    System >> Configuration >> Web

If one desires to remove index.php from category URLs, they should switch "Use Web Server Rewrites" to "Yes". Otherwise
Magento will render index.php as part of LINK type of URL (which Category URL is - a link).

Notes on having SID (Session ID) in URL
+++++++++++++++++++++++++++++++++++++++

Category data will be cached - this also includes URLs that will get the session ID stripped from it. In case SID is
included in the URLs, those pre-generated URLs will not be used and will be regenerated on page render.

It's also useful to know that SIDs stored in block cache will always be replaced correctly with current visitors session id.

**Block cache - however - is capable of replacing SID with placeholder and restoring it later on.**

Category Settings
-----------------

.. code-block:: none

    Catalog >> Manage Categories >> Category view 'Menu' tab.

============================ ====== ==================================================================================
Setting                      Scope  Description
============================ ====== ==================================================================================
Include in Navigation Menu   Store  Indicates whether the category (and it's sub-items will be presented in menu)
List Breakpoint              Store  Indicates when the column-based menu will break into a new column
Menu Image                   Store  Image especially meant to be shown in menu (overrides the use of category image)
Menu Group                   Store  Indicates in which group on each level the item is shown (default being: main)
Widget                       Store  Allows user to define a widget that will be shown next to menu item children
============================ ====== ==================================================================================

Some of the options are also assisted by changes to the category tree (most notably: breakpoints and groups).

Include in Navigation Menu
~~~~~~~~~~~~~~~~~~~~~~~~~

This status makes the category appear with bold letters in the category tree.

Note that this is the same standard option that usually is presented under the 'General' tab, but moved here to group
items that relate to navigation a bit better.

Menu Image
~~~~~~~~~~

The category image will be either the main category image (that is defined on the General tab) or an image specific
to the menu item. That one is defined on the 'Menu' tab. If both are set, image that is configured on 'Menu' tab
will be used.

List Breakpoint
~~~~~~~~~~~~~~~

There are certain menu types that can present their items on certain level in multiple rows or columns. This is controlled
by flagging certain categories as being the last ones in their row/column which makes the menu module to render next item
in new column or row.

Note that the breakpoints are enabled in the menu type configuration - so if breakpoints are not allowed, they are
successfully ignored when rendering the menu.

Each breakpoint is represented by a distinct underline in the category items tree.

Menu Group
~~~~~~~~~~

There are two different ways one can use menu group setting for a category - the way the items are shown depends on the
implementation on the template itself.

Different (other than 'main') group applied to:

1. Category that has children - will list only the children as group members on the menu level of the parent category
2. Category that do not have children - will list each item as group members on the menu level of the exact children

Each group (other than 'main') is represented by a distinct background color in the category items tree.

See :ref:`advanced-using-groups-in-templates`.

