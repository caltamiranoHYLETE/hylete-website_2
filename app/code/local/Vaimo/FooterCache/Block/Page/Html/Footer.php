<?php
/**
 * Copyright (c) 2009-2012 Vaimo AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
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
 * @package     Vaimo_FooterCache
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 */
class Vaimo_FooterCache_Block_Page_Html_Footer extends Mage_Page_Block_Html_Footer
{
    protected function _construct()
    {
        if (Mage::registry('footer_cache_disabled')) {
            return;
        }

    	/**
    	 * 1 cache key for the start page and 0 cache key for all other pages.
         * Each store has a separate set of these values.
    	 */
        $cacheKey = 0;
        if (Mage::helper("core/url")->getCurrentUrl() == Mage::getBaseUrl()) {
            $cacheKey = 1;
        }

        $loggedIn = 0;
        if ($this->helper('customer')->isLoggedIn()) {
            $loggedIn = 1;
        }

        /**
         * @var $key string Cache key.
         *
         * Format: a_b_c_d
         *   - a: 1 - Start page; 0 - Not start page
         *   - b: Store Id
         *   - c: 1 - Logged in; 0 - Not logged in
         *   - d: 1 - Secure (https) page; 0 Not secure (http) page
         */
        $key = $cacheKey . '_' . Mage::app()->getStore()->getId() . '_' . $loggedIn . '_' . (int)Mage::app()->getStore()->isCurrentlySecure();

        $lifeTime = Mage::getStoreConfig('Vaimo_FooterCache/settings/lifetime');

        if (empty($lifeTime)) {
            $lifeTime = null;
        }

        $this->addData(array(
            'cache_lifetime' => $lifeTime,
            'cache_tags'        => array("vaimo_footercache"),
            'cache_key'			=> $key
        ));

        Mage::dispatchEvent('vaimo_footercache_generate_cachekey', array('footer_block' => $this));
    }
}