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

class Vaimo_Menu_Model_Observer
{
    /**
     * Observer for an event that fires before layout xml updates/parts are loaded
     *
     * @param Varien_Event_Observer $observer
     */
    public function onControllerActionLayoutLoadBefore(Varien_Event_Observer $observer)
    {
        $update = $observer->getEvent()->getLayout()->getUpdate();

        $type = Mage::getStoreConfig(Vaimo_Menu_Model_Type::XPATH_CONFIG_SELECTED_MENU_TYPE);
        $typeUpdateHandle = Vaimo_Menu_Model_Type::MENU_TYPE_LAYOUT_UPDATE_PREFIX . '_' . $type;
        $storeCode = Mage::app()->getStore()->getCode();

        $update->addHandle($typeUpdateHandle)
            ->addHandle($typeUpdateHandle . '_' . $storeCode)
            ->addHandle(Vaimo_Menu_Model_Type::MENU_TYPE_LAYOUT_UPDATE_PREFIX . '_default_' . $storeCode);
    }

    /**
     * Observer for an event that fires before layout xml parts/updates are merged
     *
     * @param Varien_Event_Observer $observer
     */
    public function onControllerActionLayoutGenerateXmlBefore(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('vaimo_menu/layout_update')->includeDbUpdatesForPackageAndTheme(
            $observer->getLayout()->getUpdate(),
            Mage_Core_Model_Design_Package::BASE_PACKAGE,
            Mage_Core_Model_Design_Package::DEFAULT_THEME
        );
    }
}