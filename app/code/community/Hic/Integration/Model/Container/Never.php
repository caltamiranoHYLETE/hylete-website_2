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
 * Integration container which should never cache 
 *
 * @category Hic
 * @package Integration
 * @author HiConversion <support@hiconversion.com>
 */
class Hic_Integration_Model_Container_Never
    extends Enterprise_PageCache_Model_Container_Abstract
{

    /**
     * Returns Cache ID
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return $this->_placeholder->getAttribute('cache_id');
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
    
    /**
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param null $lifetime
     * @return bool|Enterprise_PageCache_Model_Container_Abstract
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }
}