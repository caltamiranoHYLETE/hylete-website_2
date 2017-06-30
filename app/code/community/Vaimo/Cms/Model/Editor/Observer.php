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

class Vaimo_Cms_Model_Editor_Observer extends Vaimo_Cms_Model_Abstract
{
    /**
     * Event: core_block_abstract_to_html_after
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateHtmlOutputMarkup(Varien_Event_Observer $observer)
    {
        $transport = $observer->getTransport();
        $factory = $this->getFactory();

        /* @var $updater Vaimo_Cms_Model_Markup_Updater */
        $updater = $factory->getSingleton('vaimo_cms/markup_updater');

        $block = $observer->getBlock();
        $html = $transport->getHtml();

        $isPartialRender = $this->getApp()->getFrontController()->getAction()->getFlag('', 'no-renderLayout');

        $html = $updater->process($block, $html, $isPartialRender);

        $transport->setHtml($html);
    }

    /**
     * Event: controller_action_layout_load_before
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function handleContentUpdates(Varien_Event_Observer $observer)
    {
        $action = $observer->getAction();
        $request = $action->getRequest();

        $helper = $this->getFactory()
            ->getHelper('vaimo_cms/editor');

        $router = $helper->getRouterForAction($action->getFullActionName());

        if (!$helper->shouldProcessRequest($action)) {
            return;
        }

        $errorCode = 400;

        try {
            $result = $router->process($request->getParams());
        } catch (Vaimo_Cms_Exception $e) {
            $errorCode = 200;

            $result = array(
                'error' => $e->getMessage()
            );

            Mage::logException($e);
        } catch (Exception $e) {
            $errorCode = 500;

            if (Mage::getIsDeveloperMode()) {
                $message = $e->getMessage();
                $trace = $e->getTraceAsString();
            } else {
                $message = 'Internal error encountered while processing editor command';
                $trace = '';
            }

            $result = array_filter(array(
                'error' => $message,
                'trace' => $trace
            ));

            Mage::logException($e);
        }

        if (!$result) {
            return;
        }

        $action->setFlag('', 'no-renderLayout', true);

        if (isset($result['error'])) {
            $action->getResponse()
                ->setHttpResponseCode($errorCode)
                ->setBody(Vaimo_Cms_Json_Encoder::encode($result));
        }
    }

    /**
     * Event: controller_action_postdispatch
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function generateContentUpdateResponse(Varien_Event_Observer $observer)
    {
        $action = $observer->getControllerAction();
        $request = $action->getRequest();
        $response = $action->getResponse();

        $helper = $this->getFactory()
            ->getHelper('vaimo_cms/editor');

        if (!$helper->shouldProcessResponse($action)) {
            return;
        }

        $resultBody = Zend_Json_Decoder::decode($response->getBody());

        if ($response->getHttpResponseCode() == 200 && isset($resultBody['error'])) {
            return;
        }


        if ($response->getHttpResponseCode() == 400) {
            return;
        }

        if ($response->getHttpResponseCode() == 500) {
            return;
        }

        $router = $helper->getRouterForAction($action->getFullActionName());

        if (!$routerResponse = $router->getResponse($request->getParams())) {
            return;
        }

        $encodedResponse = Vaimo_Cms_Json_Encoder::encode($routerResponse);

        $response->setBody($encodedResponse);
    }

    /**
     * Event: controller_action_layout_load_before
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function addRevisionHandle(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfigFlag(Vaimo_Cms_Helper_Data::XPATH_CONFIG_STAGING_ENABLED)) {
            return;
        }

        $factory = $this->getFactory();
        $action = $observer->getAction();

        $fullActionName = $action->getFullActionName();

        $handle = $factory->getHelper('vaimo_cms/structure')->getCurrentLayoutHandle($fullActionName);

        $revisionHandle = $factory->getHelper('vaimo_cms/page')
            ->getRevisionHandle($handle, Vaimo_Cms_Helper_Page::DRAFT);

        $update = $observer->getLayout()->getUpdate();
        $update->addHandle($revisionHandle);
    }

    /**
     * Event: controller_action_layout_load_before
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function addEditModeHandle(Varien_Event_Observer $observer)
    {
        if ($this->getApp()->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $observer->getLayout()->getUpdate()->addHandle(Vaimo_Cms_Model_Mode::EDIT_MODE_HANDLE);
    }

    /**
     * Event: controller_action_layout_generate_blocks_after
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function addRevisionStructureBlocks(Varien_Event_Observer $observer)
    {
        $factory = $this->getFactory();
        $action = $observer->getAction();

        $fullActionName = $action->getFullActionName();

        $handle = $factory->getHelper('vaimo_cms/structure')->getCurrentLayoutHandle($fullActionName);

        $storeId = $this->getApp()->getStore()->getId();

        $page = $factory->getModel('vaimo_cms/page', array(
            'handle' => $handle,
            'store' => $storeId,
        ));

        $factory->getHelper('vaimo_cms/page')->createStructureBlocks(
            $page,
            $action->getLayout(),
            Vaimo_Cms_Helper_Page::DRAFT
        );
    }

    /**
     * Event: catalog_category_load_after
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function activateCategory(Varien_Event_Observer $observer)
    {
        $category = $observer->getDataObject();

        if (!Mage::registry(Vaimo_Cms_Helper_Data::REGISTRY_CATEGORY_ACTIVATION)) {
            return;
        }

        if (!$category->getId()) {
            return;
        }

        if ($category->getIsActive()) {
            return;
        }

        $category->setVaimoCmsOldIsActive($category->getIsActive());

        $category->setIsActive(true);
    }

    /**
     * Event: catalog_category_save_before
     * Area: frontend
     *
     * @param Varien_Event_Observer $observer
     */
    public function restoreCategoryState(Varien_Event_Observer $observer)
    {
        $category = $observer->getDataObject();

        if ($category->hasVaimoCmsOldIsActive()) {
            $category->setIsActive($category->getVaimoCmsOldIsActive());
            $category->unsVaimoCmsOldIsActive();
        }
    }

    /**
     * Event: catalog_controller_category_init_before
     * Area: frontend
     */
    public function enableCategoryActivation()
    {
        Mage::unregister(Vaimo_Cms_Helper_Data::REGISTRY_CATEGORY_ACTIVATION);
        Mage::register(Vaimo_Cms_Helper_Data::REGISTRY_CATEGORY_ACTIVATION, true);
    }

    /**
     * Event: catalog_controller_category_init_after
     * Area: frontend
     */
    public function disableCategoryActivation()
    {
        $category = Mage::registry('current_category');

        if ($category && $category->hasVaimoCmsOldIsActive()) {
            $category->setIsActive($category->getVaimoCmsOldIsActive());
            $category->unsVaimoCmsOldIsActive();
        }

        Mage::unregister(Vaimo_Cms_Helper_Data::REGISTRY_CATEGORY_ACTIVATION);
    }
}