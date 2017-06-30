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

require_once('Mage/Adminhtml/controllers/Catalog/CategoryController.php');

class Vaimo_Cms_Adminhtml_Vaimocms_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
{
    /** @var Vaimo_Cms_Model_Core_Factory */
    protected $_factory;

    /** @var Mage_Core_Model_App */
    protected $_app;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        $this->_factory = isset($invokeArgs['factory']) ?
            $invokeArgs['factory'] : Mage::getModel('vaimo_cms/core_factory');

        $this->_app = isset($invokeArgs['app']) ?
            $invokeArgs['app'] : Mage::app();
    }

    /**
     * @return false|Vaimo_Cms_Model_Core_Factory
     */
    public function getFactory()
    {
        return $this->_factory;
    }

    /**
     * @return false|Mage_Core_Model_App
     */
    public function getApp()
    {
        return $this->_app;
    }

    public function cloneAction()
    {
        $factory = $this->_factory;

        $categoryId = $this->getRequest()->getParam('id');
        $targetStoreId = $this->getRequest()->getParam('store');

        if ($targetStoreId == Mage_Core_Model_App::ADMIN_STORE_ID) {
            $this->_error('Could not clone the page data because no store was selected. Please select a store in the store view drop-down',
                $categoryId);
            return;
        }

        $targetStore = $this->_app->getStore($targetStoreId);

        $handle = $factory->getHelper('vaimo_cms/structure')
            ->getHandleForAction(Vaimo_Cms_Helper_Data::CATEGORY_VIEW_CONTROLLER_ACTION);

        $handle .= $categoryId;

        $targetPage = $factory->getModel('vaimo_cms/page', array(
            'handle' => $handle,
            'store' => $targetStoreId
        ));

        if ($targetPage->hasStoreSpecificContent()) {
            $this->_error("Could not clone to the '{$targetStore->getName()}' store because it already contains page data",
                $categoryId);
            return;
        }

        $sourceStoreId = $targetStore->getGroup()->getDefaultStoreId();

        $sourcePage = $factory->getModel('vaimo_cms/page', array(
            'handle' => $handle,
            'store' => $sourceStoreId
        ));

        $_sourceStore = $this->_app->getStore($sourceStoreId);

        if (!$sourcePage->hasStoreSpecificContent()) {
            $store = $this->_app->getDefaultStoreView();
            $sourceStoreId = $store->getId();

            $sourcePage = $factory->getModel('vaimo_cms/page', array(
                'handle' => $handle,
                'store' => $store->getId()
            ));
        }

        $sourceStore = $this->_app->getStore($sourceStoreId);

        if (!$sourcePage->hasStoreSpecificContent()) {
            $this->_error("Cloning skipped: both '{$_sourceStore->getName()}' and '{$sourceStore->getName()}' have no page data", $categoryId);
            return;
        }

        $clonedPage = $this->getFactory()->getHelper('vaimo_cms/page')->createDraftForStore($sourcePage, $targetStoreId);

        $clonedPage->save();

        $this->getApp()->cleanCache(array(
            Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG
        ));

        $this->_success("Page data cloned from '{$sourceStore->getName()}' to the '{$targetStore->getName()}' store", $categoryId);
    }

    protected function _success($message, $categoryId)
    {
        $factory = $this->getFactory();

        $factory->getSingleton('adminhtml/session')->addSuccess(
            $this->getFactory()->getHelper('catalog')->__($message)
        );

        $this->_returnToEdit($categoryId);
    }

    protected function _error($message, $categoryId)
    {
        $factory = $this->getFactory();

        $factory->getSingleton('adminhtml/session')->addError(
            $factory->getHelper('catalog')->__($message)
        );
        $this->_returnToEdit($categoryId);
    }

    protected function _returnToEdit($categoryId)
    {
        $url = $this->getUrl('*/catalog_category/edit', array('_current' => true, 'id' => $categoryId));

        $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, true);</script>'
        );
    }
}