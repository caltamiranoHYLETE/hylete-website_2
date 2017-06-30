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

class Vaimo_Menu_Model_Adminhtml_Observer
{
    const DECORATOR_BLOCK_NAME = 'vm.tree.decorator';

    /**
     * Observer for an event that fires before layout blocks are instantiated in admin
     *
     * @param Varien_Event_Observer $observer
     */
    public function onAdminControllerActionLayoutLoadBefore(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();

        if ($event->getAction()->getRequest()->getParam('section') == 'vaimo_menu') {
            $update = $event->getLayout()->getUpdate();
            $update->addHandle('system_config_edit_vaimo_menu');
        }
    }

    /**
     * Observer for an event that fires when sub-menu in admin category tree node is opened (via AJAX)
     *
     * @param Varien_Event_Observer $observer
     */
    public function onControllerActionPostdispatchAdminhtmlCatalogCategoryCategoriesJson(Varien_Event_Observer $observer)
    {
        $response = $observer->getEvent()->getControllerAction()->getResponse();
        $tree = Zend_Json_Decoder::decode($response->getBody());

        $decorator = Mage::helper('vaimo_menu')->getDecoratorWithPredefinedMap();
        $decorator->applyToTree($tree);

        $response->setBody(json_encode($tree));
    }

    /**
     * Observer for an event that fires just before Category Edit form is rendered to HTML
     *
     * @param Varien_Event_Observer $observer
     */
    public function onControllerActionLayoutRenderBeforeAdminhtmlCatalogCategoryEdit(Varien_Event_Observer $observer)
    {
        if ($treeBlock = Mage::app()->getLayout()->getBlock('category.tree')) {
            $tree = json_decode($treeBlock->getTreeJson(), true);

            $storeId = $treeBlock->getCategoryCollection()->getStoreId();
            $ids = Mage::getModel('vaimo_menu/adminhtml_category_tree_analyzer')->getAllTreeNodeIds($tree);
            $decorator = Mage::helper('vaimo_menu')->getDecoratorWithPredefinedMap();
            $decorator->initiateForCategories($ids, self::DECORATOR_BLOCK_NAME, $storeId);
        }
    }

    /**
     * Observer for an event that fires when an item in the category tree is updated
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCategoryPrepareAjaxResponse(Varien_Event_Observer $observer)
    {
        $category = Mage::registry('current_category');

        if ($category->hasEntityId()) {
            $response = $observer->getResponse();
            $decorator = Mage::helper('vaimo_menu')->getDecoratorWithPredefinedMap();
            $decorator = $decorator->initiateForCategory($category, self::DECORATOR_BLOCK_NAME);
            $response['content'] .= $decorator->toHtml();
        }
    }

    /**
     * Observer for the event that fires when the store scope is changed in manage categories view
     *
     * @param Varien_Event_Observer $observer
     */
    public function onControllerActionPostdispatchAdminhtmlCatalogCategoryTree(Varien_Event_Observer $observer)
    {
        $response = $observer->getEvent()->getControllerAction()->getResponse();
        $responseJson = Zend_Json_Decoder::decode($response->getBody());

        $decorator = Mage::helper('vaimo_menu')->getDecoratorWithPredefinedMap();
        $decorator->applyToTree($responseJson['data']);

        $response->setBody(json_encode($responseJson));
    }
}