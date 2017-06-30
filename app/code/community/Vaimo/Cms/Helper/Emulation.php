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

class Vaimo_Cms_Helper_Emulation extends Vaimo_Cms_Helper_Abstract
{
    /**
     * @var array
     */
    protected $_baseUrlConfigKeys = array(
        'web/unsecure/base_url',
        'web/secure/base_url',
        'web/unsecure/base_link_url',
        'web/secure/base_link_url'
    );

    public function start($storeReference)
    {
        $app = $this->getApp();
        $factory = $this->getFactory();

        /** @var Vaimo_Cms_Helper_Editor $editorHelper */
        $editorHelper = $factory->getHelper('vaimo_cms/editor');

        $storeId = $editorHelper->resolveStoreIdFromReference($storeReference);

        $currentUrl = $factory->getHelper('vaimo_cms/url')->standardize(
            htmlspecialchars_decode($factory->getHelper('core/url')->getCurrentUrl())
        );

        $editorUrl = $editorHelper->getEditorUrlForStoreFromOrigin($currentUrl, $storeId);

        if ($currentUrl != $editorUrl) {
            $editorHelper->redirectAndStopExecution($editorUrl);
            return;
        }

        Mage::register(
            'vaimo_cms_exit_current_url',
            $editorHelper->getEditorExitUrlForStoreFromOrigin($editorUrl, $storeId)
        );

        $factory->getSingleton('core/app_emulation')
            ->startEnvironmentEmulation((int)$storeId, 'frontend');

        $currentStore = $this->getApp()->getStore();

        foreach (array_fill_keys($this->_baseUrlConfigKeys, $this->getAdminStoreBaseUrl()) as $path => $value) {
            $currentStore->setConfig($path, $value);
        }

        $factory->getSingleton('core/design_package')
            ->setStore($app->getStore($storeId));
    }

    public function getAdminStoreBaseUrl()
    {
        $store = $this->getApp()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        return $baseUrl;
    }

    public function getEmulatedStoreBaseLinkUrl($storeId)
    {
        $app = $this->getApp();
        $factory = $this->getFactory();

        $baseLinkUrl = $this->getAdminStoreBaseUrl();

        if ($factory->getHelper('vaimo_cms')->isStoreCodeEnabled()) {
            $baseLinkUrl = $baseLinkUrl . $app->getStore($storeId)->getCode() . '/';
        }

        return $baseLinkUrl;
    }
}