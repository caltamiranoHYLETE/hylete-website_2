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

class Vaimo_Cms_Helper_Editor extends Vaimo_Cms_Helper_Abstract
{
    const EDITOR_ADMIN_CONTROLLER_ROOT = 'adminhtml/vaimocms';

    public function getRouterForAction($action)
    {
        $factory = $this->getFactory();

        $models = array(
            $factory->getSingleton('vaimo_cms/structure_editor'),
            $factory->getSingleton('vaimo_cms/widget_editor_cloner'),
            $factory->getSingleton('vaimo_cms/widget_editor'),
            $factory->getSingleton('vaimo_cms/wysiwyg_editor_cloner'),
            $factory->getSingleton('vaimo_cms/wysiwyg_editor'),
            $factory->getSingleton('vaimo_cms/page_editor'),
            $factory->getSingleton('vaimo_cms/editor_heartbeat')
        );

        $router = $this->getFactory()->getSingleton('vaimo_cms/router', array(
            'editors' => $models
        ));

        $router->init($action);

        return $router;
    }

    public function shouldProcessRequest($action)
    {
        $request = $action->getRequest();

        if (!$request->isXmlHttpRequest()) {
            return false;
        }

        if (!$request->isPost()) {
            return false;
        }

        return true;
    }

    public function shouldProcessResponse($action)
    {
        $request = $action->getRequest();

        if (!$action->getFlag('', 'no-renderLayout')) {
            return false;
        }

        if (!$request->isXmlHttpRequest()) {
            return false;
        }

        return true;
    }

    public function getActionUri($action)
    {
        $factory = $this->getFactory();
        $currentUrl = $factory->getHelper('vaimo_cms')->getCurrentUrl();

        $uriModel = Zend_Uri_Http::fromString($currentUrl);

        $uriModel->setQuery(null);

        $uriModel->addReplaceQueryParameters(array_merge(
            array(Vaimo_Cms_Model_Router::CONTENT_UPDATE_ACTION_PARAM => $action),
            $factory->getSingleton('vaimo_cms/mode')->getEditorParamValues()
        ));

        return $uriModel->getUri();
    }

    public function getEditorParamNames()
    {
        return array(
            Vaimo_Cms_Model_Router::CONTENT_UPDATE_ACTION_PARAM,
            Vaimo_Cms_Model_Mode::EDIT_PARAMETER,
            Vaimo_Cms_Model_Mode::STORE_ID_PARAMETER
        );
    }

    public function getAdminActionUri($path, array $params)
    {
        $factory = $this->getFactory();

        $adminSessionHelper = $factory->getHelper('vaimo_cms/admin_session');

        $uriModel = $factory->getModel('adminhtml/url')
            ->setQueryParams($params)
            ->setRouteParams(array('_store' => 'admin'), true);

        $keys = array(Vaimo_Cms_Helper_Session::FORM_KEY_PARAMETER);

        return $adminSessionHelper->executeWithAdminSessionData($keys, function() use ($uriModel, $path) {
            return $uriModel->getUrl(Vaimo_Cms_Helper_Editor::EDITOR_ADMIN_CONTROLLER_ROOT . '_' . $path);
        });
    }

    public function stripBaseUrl($url)
    {
        $app = $this->getApp();

        $baseUrls = array();
        foreach ($app->getStores() as $store) {
            foreach (array(true, false) as $isSecure) {
                $baseUrls[] = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, $isSecure);
            }
        }

        foreach ($baseUrls as $baseUrl) {
            if (strstr($url, $baseUrl) === false) {
                continue;
            }

            return str_replace($baseUrl, '', $url);
        }

        return $url;
    }

    public function getEditorExitUrlForStoreFromOrigin($url, $storeId)
    {
        $app = $this->getApp();
        $factory = $this->getFactory();

        /** @var Vaimo_Cms_Helper_Url $urlHelper */
        $urlHelper = $factory->getHelper('vaimo_cms/url');

        $store = $app->getStore($storeId);
        $isSecure = $store->isCurrentlySecure();

        $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, $isSecure);

        list($path, $query, $fragment) = $urlHelper->decomposeUrl($url);

        $relativePath = $this->stripBaseUrl($path);

        return $urlHelper->composeUrl(
            $relativePath != $path ? $baseUrl . $relativePath : $relativePath,
            $urlHelper->modifyQuery($query, array(Vaimo_Cms_Model_Mode::STORE_ID_PARAMETER => $storeId)),
            $fragment
        );
    }

    public function getEditorUrlForStoreFromOrigin($url, $storeId)
    {
        $factory = $this->getFactory();

        $urlHelper = $factory->getHelper('vaimo_cms/url');

        $adminStore = $this->getApp()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $editModeBaseUrl = $adminStore->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        list($path, $query, $fragment) = $urlHelper->decomposeUrl($url);

        $relativePath = $this->stripBaseUrl($path);

        if ($relativePath == $path) {
            return $url;
        }

        if (Mage::getStoreConfigFlag('web/url/use_store')) {
            $storeCodes = array_map(function($store) {
                return $store->getCode();
            }, $this->getApp()->getStores());

            $pathParts = explode('/', $relativePath);

            if (in_array(array_shift($pathParts), $storeCodes)) {
                array_unshift($pathParts, $storeCodes[$storeId]);
                $relativePath = implode('/', $pathParts);
            }
        }

        return $urlHelper->composeUrl(
            $editModeBaseUrl . $relativePath,
            $urlHelper->modifyQuery($query, array(Vaimo_Cms_Model_Mode::STORE_ID_PARAMETER => $storeId)),
            $fragment
        );
    }

    public function redirectAndStopExecution($url)
    {
        $this->getApp()->getResponse()
            ->setRedirect($url)
            ->sendResponse();

        exit;
    }

    public function resolveStoreIdFromReference($storeReference)
    {
        $stores = $this->getApp()->getStores();

        $storeCodes = array();
        foreach ($stores as $id => $store) {
            $storeCodes[$store->getCode()] = $id;
        }

        if (isset($storeCodes[$storeReference]) && !isset($stores[$storeReference])) {
            return $storeCodes[$storeReference];
        }

        return $storeReference;
    }
}