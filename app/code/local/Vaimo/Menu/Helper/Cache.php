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
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @comment     Helper to extend the cache implementation already present in app()
 */

class Vaimo_Menu_Helper_Cache extends Mage_Core_Model_Cache
{
    const CACHE_LIFETIME = 14400;
    const XPATH_CONFIG_CLEAN_ON_CATEGORY_SAVE = 'vaimo_menu/settings/cache_clean_on_category_save';

    public function test($id, $cacheFlag = Vaimo_Menu_Model_Navigation::CACHE_FLAG_NAME)
    {
        return Mage::app()->getCache()->test($this->_id($id)) && Mage::app()->useCache($cacheFlag);
    }

    public function getApp()
    {
        return Mage::app();
    }

    public function getDataCacheTags()
    {
        $tags = array(
            Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG,
            Mage_Core_Model_Store_Group::CACHE_TAG,
            Vaimo_Menu_Model_Navigation::CACHE_TAG
        );

        if (Mage::getStoreConfigFlag(Vaimo_Menu_Helper_Cache::XPATH_CONFIG_CLEAN_ON_CATEGORY_SAVE)) {
            $tags[] = Mage_Catalog_Model_Category::CACHE_TAG;
        }

        return $tags;
    }

    public function loadSerialized($cacheKey)
    {
        if ($serializedData = $this->getApp()->loadCache($cacheKey)) {
            return unserialize($serializedData);
        }

        return false;
    }

    public function saveSerialized($cacheKey, array $data, $tags)
    {
        $this->getApp()->saveCache(serialize($data), $cacheKey, $tags);
    }
}