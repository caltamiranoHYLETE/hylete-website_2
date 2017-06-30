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

class Vaimo_Cms_Block_Navigate extends Vaimo_Cms_Block_Js_Lib
{
    protected $_type = self::TYPE_JQUERY;
    protected $_jsClassName = 'navigateInFrontend';

    protected function _construct()
    {
        parent::_construct();

        $app = $this->getApp();
        $factory = $this->getFactory();
        $store = $app->getStore();

        $this->addConstructorParams(array(
            'storeIdParam' => Vaimo_Cms_Model_Mode::STORE_ID_PARAMETER,
            'isStoreCodeEnabled' => $app->getHelper('vaimo_cms')->isStoreCodeEnabled(),
            'currentStoreCode' => $store->getCode(),
            'baseWebUrl' => $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
            'editModeParams' => $factory->getSingleton('vaimo_cms/mode')->getEditorParamValues()
        ));

        $this->_populateWebsiteStores();
    }

    protected function _toHtml()
    {
        if (!Mage::getStoreConfigFlag(Vaimo_Cms_Helper_Data::XPATH_CONFIG_REWRITE_URLS)) {
            return '';
        }

        return parent::_toHtml();
    }

    protected function _populateWebsiteStores()
    {
        $websiteStores = $this->getApp()->getWebsite()->getStores();
        $stores = array();

        foreach($websiteStores as $store) {
            $stores[$store->getCode()] = $store->getId();
        }

        $this->setConstructorParam('stores', $stores);
    }
}