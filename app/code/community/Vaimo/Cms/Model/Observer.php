<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Model_Observer extends Vaimo_Cms_Model_Abstract
{
    /**
     * Event: controller_front_init_before
     * Area: global
     *
     * Note that we can't detect admin store at this point because routers are not initiated yet. We have no idea which
     * controller we're running against at this point. Nor do we know which area we're in.
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareForEditMode(Varien_Event_Observer $observer)
    {
        $factory = $this->getFactory();
        $action = $observer->getFront();

        if (!$factory->getModel('vaimo_cms/mode')->getEditModeStateForAction($action)) {
            return;
        }

        $factory->getHelper('vaimo_cms')->setNoCacheHeadersForAction($action);
        $factory->getHelper('vaimo_cms/admin_session')->instantiateInIsolation();

        if (!$factory->getSingleton('admin/session')->isLoggedIn()) {
            return;
        }

        $storeId = $action->getRequest()->getParam(Vaimo_Cms_Model_Mode::STORE_ID_PARAMETER);

        $factory->getHelper('vaimo_cms/emulation')->start($storeId);
    }

    /**
     * Event: controller_action_predispatch
     * Area: frontend
     *
     * Note that at this point we have already instantiated admin session in it's own name-space and can use normal
     * admin session model to fetch the information about admin. We now also know in which name-space we're executing
     * at and can access the actual matched controller that is responsible for rendering the page.
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateEditModeEnabledDisabledState(Varien_Event_Observer $observer)
    {
        $factory = $this->getFactory();
        $action = $observer->getControllerAction();

        $mode = $factory->getModel('vaimo_cms/mode');
        $adminSession = $factory->getSingleton('admin/session');

        if (!$mode->getEditModeStateForAction($action) || !$adminSession->isLoggedIn()) {
            return;
        }

        $mode->enableEditMode();
    }

    /**
     * Event: controller_action_layout_load_before
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function addExtraLayoutHandles(Varien_Event_Observer $observer)
    {
        $action = $observer->getAction();
        $update = $observer->getLayout()->getUpdate();

        switch($action->getFullActionName()) {
            case Vaimo_Cms_Helper_Data::CMS_HOME_PAGE_CONTROLLER_ACTION:
                $rootCategoryIdForStore = $this->getApp()->getStore()->getRootCategoryId();
                $update->addHandle('CATEGORY_' . $rootCategoryIdForStore);

                break;

            case Vaimo_Cms_Helper_Data::CMS_PAGE_CONTROLLER_ACTION:
                $cmsPageId = $action->getRequest()->getParam('page_id');
                $update->addHandle('CMSPAGE_' . $cmsPageId);

                break;
        }
    }

    /**
     * Event: controller_action_layout_generate_blocks_after
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareContentPageLayout(Varien_Event_Observer $observer)
    {
        $factory = $this->getFactory();

        switch ($observer->getAction()->getFullActionName()) {
            case Vaimo_Cms_Helper_Data::CMS_HOME_PAGE_CONTROLLER_ACTION:
                $rootCategoryIdForStore = $this->getApp()->getStore()->getRootCategoryId();
                $category = $factory->getModel('catalog/category')->load($rootCategoryIdForStore);

                break;
            case Vaimo_Cms_Helper_Data::CATEGORY_VIEW_CONTROLLER_ACTION:
                $category = Mage::registry('current_category');

                break;
            default:
                return;
        }

        if ($factory->getHelper('vaimo_cms')->isCmsPage($category)) {
            $factory->getHelper('vaimo_cms/layout')->removeNonWidgetBlocksFromContainers($observer->getLayout(), false);
        }
    }

    /**
     * Event: controller_action_layout_generate_blocks_after
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function addStructureBlocks(Varien_Event_Observer $observer)
    {
        $action = $observer->getAction();
        $layout = $action->getLayout();

        $factory = $this->getFactory();
        $app = $this->getApp();

        $structureHelper = $factory->getHelper('vaimo_cms/structure');

        $handle = $structureHelper->getCurrentLayoutHandle($action->getFullActionName());
        $storeId = $app->getStore()->getId();

        $page = $factory->getModel('vaimo_cms/page', array(
            'handle' => $handle,
            'store' => (int)$storeId
        ));

        $factory->getHelper('vaimo_cms/page')->createStructureBlocks($page, $layout);
    }

    /**
     * Event: controller_action_layout_generate_xml_before
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function addBaseDefaultLayoutDbUpdates(Varien_Event_Observer $observer)
    {
        $factory = $this->getFactory();

        $factory->getModel('vaimo_cms/layout_update')->includeDbUpdatesForPackageAndTheme(
            $observer->getLayout()->getUpdate(),
            Mage_Core_Model_Design_Package::BASE_PACKAGE,
            Mage_Core_Model_Design_Package::DEFAULT_THEME
        );
    }

    /**
     * Event: vaimo_cms_add_fpc_clean_handle
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function addPageCacheCleanHandle(Varien_Event_Observer $observer)
    {
        if (Mage::getEdition() != Mage::EDITION_ENTERPRISE) {
            return;
        }

        $factory = $this->getFactory();

        $handle = $factory->getHelper('vaimo_cms/structure')
            ->getCurrentLayoutHandle($observer->getAction()->getFullActionName());

        $factory->getSingleton('enterprise_pagecache/processor')
            ->addRequestTag($handle);
    }
}