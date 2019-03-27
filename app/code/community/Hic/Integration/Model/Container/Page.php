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
 * @Copyright � 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

/**
 * Integration container which should cache as long as page hasn't changed
 *
 * @category Hic
 * @package Integration
 * @author HiConversion <support@hiconversion.com>
 */
class Hic_Integration_Model_Container_Page
    extends Enterprise_PageCache_Model_Container_Abstract
{
    const CACHE_TAG_PREFIX = 'HICONVERSION_INTEGRATION_';

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        if ($this->_placeholder->getAttribute('category_id') 
            || $this->_placeholder->getAttribute('product_id')) {
            $cacheSubKey = '_' . $this->_placeholder->getAttribute('category_id') 
                . '_' . $this->_placeholder->getAttribute('product_id');
        } else if (method_exists($this, '_getRequestId')) {
            $cacheSubKey = $this->_getRequestId();
        } else {
            $cacheSubKey = !$this->_processor ? null : $this->_processor->getRequestId();
        }

        return md5(Hic_Integration_Model_Container_Page::CACHE_TAG_PREFIX 
            . $cacheSubKey);
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