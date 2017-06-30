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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_MultiOptionFilter_Model_Cache_Layer
{
    protected $_instanceStorage = array();

    /**
     * @param $cacheKey
     * @param $flag
     * @param $tags
     * @param Closure|array $fetcher
     * @return mixed
     */
    public function get($cacheKey, $flag, $tags, $fetcher)
    {
        $cache = Mage::getSingleton('multioptionfilter/cache');

        if (!$cache->test($cacheKey, $flag)) {
            if (is_array($fetcher)) {
                $data = call_user_func_array(array_slice($fetcher, 0, 2), array_slice($fetcher, 2));
            } else {
                $data = $fetcher();
            }

            $cache->saveSerialized($cacheKey, $data, $tags);

            $this->_instanceStorage[$cacheKey] = $data;
        } else if (!isset($this->_instanceStorage[$cacheKey])) {
            $this->_instanceStorage[$cacheKey] = $cache->loadSerialized($cacheKey);
        }

        return $this->_instanceStorage[$cacheKey];
    }
}