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

class Vaimo_Cms_Helper_Cache extends Mage_Core_Model_Cache
{
    protected $_factory;
    protected $_app;

    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->_factory = isset($args['factory']) ? $args['factory'] : Mage::getModel('vaimo_cms/core_factory');

        $this->_app = isset($args['app']) ? $args['app'] : Mage::app();
    }

    public function getFactory()
    {
        return $this->_factory;
    }

    public function getApp()
    {
        return $this->_app;
    }

    public function test($id, $cacheFlag)
    {
        $app = $this->getApp();

        return $app->getCache()->test($this->_id($id)) && $app->useCache($cacheFlag);
    }

    public function loadSerialized($cacheKey)
    {
        if ($serializedData = $this->getApp()->loadCache($cacheKey)) {
            return unserialize($serializedData);
        }

        return false;
    }

    public function saveSerialized($cacheKey, $data, $tags)
    {
        $this->getApp()->saveCache(serialize($data), $cacheKey, $tags);
    }

    public function cleanTags(array $tags)
    {
        $this->getApp()->cleanCache($tags);
    }

    public function cleanTagsForPage(array $tags)
    {
        if (Mage::getEdition() != Mage::EDITION_ENTERPRISE) {
            return;
        }

        Enterprise_PageCache_Model_Cache::getCacheInstance()->clean($tags);
    }
}