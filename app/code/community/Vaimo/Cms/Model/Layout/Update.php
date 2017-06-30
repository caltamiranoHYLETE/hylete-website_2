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

class Vaimo_Cms_Model_Layout_Update extends Vaimo_Cms_Model_Abstract
{
    const CACHE_TAG = 'VAIMO_LAYOUT_DB_UPDATE';
    const LAYOUT_CACHE_FLAG = 'layout';

    protected $_cacheTags = array(
        Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG,
        self::CACHE_TAG
    );

    protected $_widgetInfoFetcher = false;

    protected function _getTagName($name)
    {
        return self::CACHE_TAG . '_' . $name;
    }

    protected function _getTags($update)
    {
        $tags = $this->_cacheTags;

        foreach ($update->getHandles() as $handle) {
            if (strtoupper($handle) === $handle) {
                $tags[] = $this->_getTagName($handle);
            }
        }

        if ($action = $this->getApp()->getFrontController()->getAction()) {
            $fullActionName = $action->getFullActionName();

            if (trim($fullActionName, '_')) {
                $tags[] = $this->_getTagName($fullActionName);
            }
        }

        return $tags;
    }

    public function includeDbUpdatesForPackageAndTheme($update, $package, $theme)
    {
        $newUpdates = $this->_getUpdatesForPackageAndTheme($update, $package, $theme);

        foreach ($newUpdates as $_update) {
            $update->addUpdate($_update);
        }
    }

    protected function _getUpdatesForPackageAndTheme($update, $package, $theme)
    {
        $cacheKey = self::CACHE_TAG . '_' . $package . '_' . $theme . '_' . $update->getCacheId();
        $cache = $this->getFactory()->getHelper('vaimo_cms/cache');

        $updateBefore = $update->asArray();

        if (!$this->_shouldReload($cacheKey)) {
            $updates = $cache->loadSerialized($cacheKey);

            $updates = $this->_diffLayoutUpdates($updates, $updateBefore);
        } else {
            $updates = $this->getFactory()->getModel('vaimo_cms/layout_db_update')
                ->getDbUpdatesForPackageAndTheme($update, $package, $theme);

            $updates = $this->_diffLayoutUpdates($updates, $updateBefore);

            $cache->saveSerialized($cacheKey, $updates, $this->_getTags($update));
        }

        return $updates;
    }

    protected function _shouldReload($cacheKey)
    {
        $cache = $this->getFactory()->getHelper('vaimo_cms/cache');

        return !$cache->test($cacheKey, self::LAYOUT_CACHE_FLAG);
    }

    protected function _cleanup($items)
    {
        $tags = '<reference><block>';

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        $items = array_map(function($text) use ($tags) {
            if(is_array($tags) AND count($tags) > 0) {
                $text = preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            } else {
                $text = preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
            }

            return trim(str_replace(array(' ', "\n"), '', $text));
        }, $items);

        return $items;
    }

    protected function _diffLayoutUpdates($updatesA, $updatesB)
    {
        $diff = array_diff_key(array_flip($this->_cleanup($updatesA)), array_flip($this->_cleanup($updatesB)));

        return array_intersect_key($updatesA, array_flip($diff));
    }

    public function getWidgetInstanceLayoutUpdates($update)
    {
        $factory = $this->getFactory();
        $cacheLayer = $factory->getHelper('vaimo_cms/cache_layer');
        $cacheKey = 'VCMS_LAYOUT_DB_UPDATES_WIDGET_INFO_' . $update->getCacheId();

        if (!$this->_widgetInfoFetcher) {
            $this->_widgetInfoFetcher = function() use ($factory, $update) {
                return $factory->getModel('vaimo_cms/layout_db_update')->getDbUpdatesByNameInLayout($update);
            };
        }

        return $cacheLayer->getData($cacheKey, self::LAYOUT_CACHE_FLAG, $this->_getTags($update), $this->_widgetInfoFetcher);
    }
}