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
 * @package     Vaimo_CustomCss
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
class Vaimo_CustomCss_Block_Css extends Mage_Core_Block_Abstract
{
    protected $_storeId;

    protected function _construct()
    {
        $this->addData(array(
            'cache_lifetime' => 3600,
            'cache_tags'     => array(Vaimo_CustomCss_Model_Customcss::CACHE_TAG),
            'cache_key'      => $this->_getCacheKey(),
        ));
    }

    protected function _toHtml()
    {
        $html = '';

        $collection = Mage::getModel('customcss/customcss')->getCollection()
            ->addStoreFilter($this->_getStoreId())
            ->addFieldToFilter('is_active', 1);

        foreach ($collection as $item) {
            $dir =  Mage::getBaseUrl('media') . $this->helper('customcss')->getCssDir() .DS;
            $url = sprintf('%s%s?v=%s', $dir, $item->getFilename(), $item->getVersionHash());

            /**
             * @Todo: Add media attributes as last parameter, that you should be able to configure in Admin.
             */
            $html .= sprintf('<link rel="stylesheet" type="text/css" href="%s"%s />', $url, '');
        }

        return $html;
    }

    protected function _getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->_storeId = Mage::app()->getStore()->getId();
        }

        return $this->_storeId;
    }

    protected function _getScheme()
    {
        return $this->getRequest()->getScheme();
    }

    protected function _getCacheKey()
    {
        return $this->_getStoreId() . '_' . $this->_getScheme();
    }
}