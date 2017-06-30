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

class Vaimo_Cms_Model_Mode extends Vaimo_Cms_Model_Abstract
{
    const EDIT_PARAMETER = 'edit';
    const OBSERVER_FLAG = 'edit';

    const STORE_ID_PARAMETER = 'store_id';

    const ENABLE = '1';
    const DISABLE = '0';

    const EDIT_MODE_HANDLE = 'cms_edit_mode';

    public function enableEditMode()
    {
        $this->getFactory()->getHelper('vaimo_cms/event')->setTypeForObservers(self::OBSERVER_FLAG,
            Vaimo_Cms_Helper_Event::TYPE_ENABLED);

        /**
         * These flags are for modules that have implementation that interferes with the way CMS module works and
         * that would require too much rework to make cms safe. So we set certain flags that will make the certain
         * features for the modules to be disabled while in edit mode. Used ONLY for backwards compatibility with
         * older modules. The registry key should NOT be used to detect if the edit mode is enabled - use observer-based
         * activation instead (see how Editor/Observer.php methods are registered in config.xml). One can also rely on
         * layout update to be there (cms_edit_mode)
         */
        Mage::unregister('mof_disable_ajax');
        Mage::unregister('footer_cache_disabled');
        Mage::unregister('vaimo_cms_edit_mode');

        Mage::register('mof_disable_ajax', true);
        Mage::register('footer_cache_disabled', true);
        Mage::register('vaimo_cms_edit_mode', true);
    }

    public function getEditModeStateForAction($action)
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $action->getRequest();

        if ($request->getParam(self::EDIT_PARAMETER) != self::ENABLE || !$request->getParam(self::STORE_ID_PARAMETER)) {
            return false;
        }

        $stores = $this->getApp()->getStores();

        $storeId = $this->getFactory()->getHelper('vaimo_cms/editor')
            ->resolveStoreIdFromReference($request->getParam(self::STORE_ID_PARAMETER));

        if (!isset($stores[$storeId])) {
            return false;
        }

        return true;
    }

    /**
     * Return url with edit mode parameter included
     *
     * @param $storeId
     * @param $category
     * @return string
     */
    public function getEditModeUrl($storeId, $category)
    {
        $factory = $this->getFactory();

        if ($category->getLevel() > 1) {
            $editModeUrl = $this->makeEmulatedStoreCategoryUrl($category->getUrl(), $storeId);
        } else {
            $editModeUrl = $factory->getHelper('vaimo_cms/emulation')->getEmulatedStoreBaseLinkUrl($storeId);
        }

        $editModeUrl = $this->_setEditModeParameter($editModeUrl, Vaimo_Cms_Model_Mode::ENABLE);
        $editModeUrl = $this->_setStoreCodeParameter($editModeUrl, $storeId);

        return $editModeUrl;
    }

    public function makeEmulatedStoreCategoryUrl($categoryUrl, $storeId)
    {
        $helper = $this->getFactory()->getHelper('vaimo_cms/emulation');

        $categoryUrl = str_replace('index.php/admin/', '', $categoryUrl);
        $categoryUrl = str_replace('index.php/', '', $categoryUrl);
        $categoryUrl = str_replace($helper->getAdminStoreBaseUrl(), $helper->getEmulatedStoreBaseLinkUrl($storeId), $categoryUrl);

        return $categoryUrl;
    }

    /**
     * Return url to disabled edit mode
     *
     * @return string
     */
    public function getDisableEditModeUrl()
    {
        return $this->_setEditModeParameter(
            Mage::registry('vaimo_cms_exit_current_url') ?: $this->getFactory()->getHelper('core/url')->getCurrentUrl(),
            Vaimo_Cms_Model_Mode::DISABLE
        );
    }

    protected function _setStoreCodeParameter($uri, $storeCode)
    {
        $uri = Zend_Uri_Http::fromString($uri);
        $uri->addReplaceQueryParameters(array(
            Vaimo_Cms_Model_Mode::STORE_ID_PARAMETER => $storeCode
        ));

        return $uri->getUri();
    }

    protected function _setEditModeParameter($uri, $value)
    {
        $uri = Zend_Uri_Http::fromString($uri);

        $uri->addReplaceQueryParameters(array(
                Vaimo_Cms_Model_Mode::EDIT_PARAMETER => $value
        ));

        return $uri->getUri();
    }

    public function getEditorParamValues()
    {
        return array(
            Vaimo_Cms_Model_Mode::EDIT_PARAMETER => Vaimo_Cms_Model_Mode::ENABLE,
            Vaimo_Cms_Model_Mode::STORE_ID_PARAMETER => $this->getApp()->getStore()->getId()
        );
    }
}