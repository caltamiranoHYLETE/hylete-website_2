<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_MultiOptionFilter_Model_Layer_Aggregation
{
    const CATEGORY_OPTIONS_CACHE_LIFETIME = 3600;
    const CACHE_TAG = 'VAIMO_MOF';
    const CACHE_FLAG_NAME = 'vaimo_multioptionfilter';

    public function getCacheData($key)
    {
        if (!Mage::app()->useCache(self::CACHE_FLAG_NAME)) {
            return null;
        }

        if ($result = Mage::app()->loadCache($key)) {
            return Zend_Json::decode($result);
        }

        return null;
    }

    public function saveCacheData($data, $key, $tags)
    {
        if (!Mage::app()->useCache(self::CACHE_FLAG_NAME)) {
            return null;
        }

        $tags[] = self::CACHE_TAG;

        return Mage::app()->saveCache(
            Zend_Json::encode($data), $key, $tags, self::CATEGORY_OPTIONS_CACHE_LIFETIME);
    }
}