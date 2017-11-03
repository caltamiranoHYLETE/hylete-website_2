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

class Vaimo_Cms_Model_Adminhtml_Observer extends Vaimo_Cms_Model_Abstract
{
    const CATEGORY_EDIT_ACTION = 'adminhtml_catalog_category_edit';
    const DECORATOR_BLOCK_NAME = 'cms.tree.decorator';

    /**
     * Event: adminhtml_block_html_before
     * Area: adminhtml
     *
     * @param Varien_Event_Observer $observer
     */
    public function addCategoryManagerToolbar(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();

        if ($block->getNameInLayout() == 'tabs') {
            $action = $this->getApp()->getFrontController()->getAction();

            if ($action->getFullActionName() == self::CATEGORY_EDIT_ACTION) {
                $toolbar = $block->getChild(Vaimo_Cms_Helper_Data::TOOLBAR_NAME);

                if (!$toolbar) {
                    return;
                }

                echo $toolbar->toHtml();
            }
        }
    }

    /**
     * Event: store_save_after
     * Area: adminhtml
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateDefaultStructureStores(Varien_Event_Observer $observer)
    {
        $store = $observer->getStore();

        if ($store->getOrigData() !== null) {
            return;
        }

        $factory = $this->getFactory();

        $cmsStore = $factory->getModel('vaimo_cms/store', array(
            'store' => $store->getId()
        ));

        $handles = array();
        foreach ($cmsStore->getStructures() as $structure) {
            if ($structure->getScope() == Vaimo_Cms_Model_Fallback_Scope::STORE) {
                continue;
            }

            $handles[] = $structure->getHandle();
            $structure->setHasDataChanges(true);
        }

        if (!$handles) {
            return;
        }

        $cmsStore->save();

        $factory->getSingleton('vaimo_cms/cache')
            ->clean($handles);
    }

    /**
     * Event: catalog_category_save_after
     * Area: adminhtml
     *
     * @param Varien_Event_Observer $observer
     */
    public function synchronizeCategoryWithCmsPage(Varien_Event_Observer $observer)
    {
        $category = $observer->getCategory();

        if (!$category->getPageLayout()) {
            return;
        }

        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE);

        $page = $this->getFactory()->getModel('cms/page')
            ->load($pageId);

        if ($page->hasData() && $category->getLevel() <= 1) {
            $page->setRootTemplate($category->getPageLayout());
            $page->save();
        }
    }

    /**
     * Event: controller_action_layout_generate_blocks_after
     * Area: adminhtml
     *
     * @param Varien_Event_Observer $observer
     */
    public function addEditFormUpdates(Varien_Event_Observer $observer)
    {
        $action = $this->getApp()->getFrontController()->getAction();

        if ($action->getFullActionName() == self::CATEGORY_EDIT_ACTION) {
            $this->getFactory()->getSingleton('vaimo_cms/adminhtml_category_editor')
                ->addEditFormUpdates($observer->getLayout());
        }
    }

    /**
     * Event: core_block_abstract_prepare_layout_after
     * Area: adminhtml
     *
     * @param Varien_Event_Observer $observer
     */
    public function addEditPreviewButton(Varien_Event_Observer $observer)
    {
        if (!$block = $observer->getBlock()) {
            return;
        }

        if ($block->getType() != 'adminhtml/catalog_category_edit_form') {
            return;
        }

        $factory = $this->getFactory();
        $category = $block->getCategory();

        /** @var Vaimo_Cms_Helper_Data $helper */
        $helper = $factory->getHelper('vaimo_cms');

        $storeId = $this->getApp()->getRequest()->getParam('store');

        if ($storeId === null) {
            $storeId = $helper->getFirstStoreForCategory($category);
        }

        $isCurrentStoreCategory = $helper->isCurrentCategoryInCurrentRootCategory($category, $storeId);

        if ($category->getId() && $isCurrentStoreCategory) {
            $frontendUrl = $factory->getModel('vaimo_cms/mode')->getEditModeUrl($storeId, $category);

            $block->addAdditionalButton('edit_preview', array(
                'label'     => $helper->__('Edit & Preview'),
                'title'     => $helper->__('Edit & Preview'),
                'onclick'   => "window.open('{$frontendUrl}', 'edit_preview')",
                'type'      => 'button',
                'id'        => 'edit_preview_button',
                'class'     => 'success'
            ));
        }
    }

    /**
     * Event: core_block_abstract_prepare_layout_after
     * Area: adminhtml
     *
     * @param Varien_Event_Observer $observer
     */
    public function addCloneButton(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();

        if (!$block || $block->getType() !== 'adminhtml/catalog_category_edit_form') {
            return;
        }

        $category = $block->getCategory();

        if (!$category->getId() || !$category->getIsActive()) {
            return;
        }

        $storeId = $this->getApp()->getRequest()->getParam('store');

        $helper = $this->getFactory()->getHelper('vaimo_cms');

        if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID) {
            $storeId = $helper->getDefaultStoreId();
        }

        if (!$helper->isCurrentCategoryInCurrentRootCategory($category, $storeId)) {
            return;
        }

        $cloneUrl = $this->getFactory()->getHelper('vaimo_cms/editor')
            ->getAdminActionUri('category/clone', array(
                'id' => $category->getId(),
                'store' => $storeId,
                'isAjax' => true
            ));

        $action = "$('category_edit_form').setAttribute('action', '%s'); categorySubmit('%s', true);";

        $block->addAdditionalButton('clone', array(
            'label'     => $this->getFactory()->getHelper('vaimo_cms')->__('Clone page to this store'),
            'onclick'   => sprintf($action, $cloneUrl, $cloneUrl)
        ));
    }

    /**
     * Event: category_prepare_ajax_response
     * Area: adminhtml
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateCategoryIcon(Varien_Event_Observer $observer)
    {
        $category = Mage::registry('current_category');

        if (!$category->hasEntityId()) {
            return;
        }

        $decorator = $this->getApp()->getLayout()->getBlock(self::DECORATOR_BLOCK_NAME);

        $response = $observer->getResponse();
        $response['content'] .= $decorator->toHtml();
    }

    /**
     * Event: vaimo_cms_widget_open_edit_form_before
     * Area: adminhtml
     *
     * @param Varien_Event_Observer $observer
     */
    public function switchPageIdWhenClonedStructurePresent(Varien_Event_Observer $observer)
    {
        $params = $observer->getParams();

        if ($params->hasConfiguration()) {
            return;
        }

        if (!$params->hasStructureId() || !$params->hasStore()) {
            return;
        }

        $factory = $this->getFactory();
        $structure = $factory->getModel('vaimo_cms/structure')
            ->load($params->getStructureId());

        $revision = $factory->getModel('vaimo_cms/revision', array(
            'handle' => $structure->getHandle(),
            'store' => $params->getStore()
        ));

        $structure = $revision->getStructureForReference($structure->getBlockReference());

        if ($widget = $structure->getItem($params->getPageId(), 'clone_of')) {
            $params->setPageId($widget['widget_page_id']);
        }
    }
}
