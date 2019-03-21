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
 * Integration observer model
 *
 * @category Hic
 * @package Integration
 * @author HiConversion <support@hiconversion.com>
 */
class Hic_Integration_Model_Observer
{
    
    protected static $_clearCache = false;

    /**
     * Is Enabled Full Page Cache
     *
     * @var bool
     */
    protected $_isEnabled;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_isEnabled = Mage::app()->useCache('full_page');
    }

    /**
     * Check if full page cache is enabled
     *
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->_isEnabled;
    }

    /**
     * Intercept 'core_block_abstract_tohtml_after' event response
     * to inject block at top of head
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function interceptResponse(Varien_Event_Observer $observer)
    {
        if ($observer->getBlock()->getNameInLayout() == 'head') {
            $html = $observer->getTransport()->getHtml();
            $layout = Mage::getSingleton('core/layout');
            $tagSession = $layout
                ->createBlock('core/template', 'hic.integration.tag.session')
                ->setTemplate('hic/headSession.phtml')
                ->toHtml();
            $tagPage = $layout
                ->createBlock('core/template', 'hic.integration.tag.page')
                ->setTemplate('hic/headPage.phtml')
                ->toHtml();
            $tagNever = $layout
                ->createBlock('core/template', 'hic.integration.tag.never')
                ->setTemplate('hic/headNever.phtml')
                ->toHtml();
            $tagAlways = $layout
                ->createBlock('core/template', 'hic.integration.tag')
                ->setTemplate('hic/headAlways.phtml')
                ->toHtml();
           
            $observer->getTransport()->setHtml($tagSession . $tagPage . $tagNever . $tagAlways . $html);
        }
        
        return $this;
    }


    /**
     * Clear placeholder cache for Session Container
     *
     * @return $this
     */
    public function flushCache()
    {
       
        if (!$this->isCacheEnabled()) {
            return $this;
        }
        if (self::$_clearCache == false) {
            $cacheId = Hic_Integration_Model_Container_Session::getCacheId();
            Enterprise_PageCache_Model_Cache::getCacheInstance()
                ->remove($cacheId);
            self::$_clearCache = true;
        }
        return $this;
    }
}
