<?php
/**
 * HiConversion
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * [http://opensource.org/licenses/MIT]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category Hic
 * @package Hic_Integration
 * @Copyright © 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

/**
 * Integration container which should cache as long as cart and customer data hasn't changed
 *
 * @category Hic
 * @package Integration
 * @author HiConversion <support@hiconversion.com>
 */
class Hic_Integration_Model_Container_Session
    extends Enterprise_PageCache_Model_Container_Abstract
{
    const CACHE_TAG_PREFIX = 'HICONVERSION_INTEGRATION_';

    /**
     * Get identifier from cookies
     *
     * @return string
     */
    public static function getCacheId()
    {
        $cookieCart = Enterprise_PageCache_Model_Cookie::COOKIE_CART;
        $cookieCustomer = Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER;
        return md5(Hic_Integration_Model_Container_Session::CACHE_TAG_PREFIX
            . (array_key_exists($cookieCart, $_COOKIE)
                ? $_COOKIE[$cookieCart] : '')
            . (array_key_exists($cookieCustomer, $_COOKIE)
                ? $_COOKIE[$cookieCustomer] : ''));
    }
    

    /**
     * Returns Cache ID
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return Hic_Integration_Model_Container_Session::getCacheId();
    }

    /**
     * Render block content
     *
     * @return mixed
     */
    protected function _renderBlock()
    {
        $blockClass = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');
        $block = new $blockClass;
        $block->setTemplate($template);
        return $block->toHtml();
    }
}