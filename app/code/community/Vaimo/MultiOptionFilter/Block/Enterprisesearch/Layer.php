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

class Vaimo_MultiOptionFilter_Block_Enterprisesearch_Layer extends Enterprise_Search_Block_Catalogsearch_Layer
{
    public function __construct()
    {
        parent::_construct();

        if (Mage::getStoreConfig('multioptionfilter/settings/enable_block_caching')) {
            $lifetime = Mage::getStoreConfig('multioptionfilter/settings/block_cache_lifetime');
            $this->setData('cache_lifetime', $lifetime ? $lifetime : 360);
        }
    }

    public function getCacheLifetime()
    {
        return parent::getCacheLifetime();
    }

    public function getCacheKeyInfo()
    {
        $request = Mage::app()->getRequest();

        $info = array(
            'Block_Search_Layer',
            Mage::app()->getStore()->getId()
        );

        $customer = Mage::helper('customer')->getCustomer();

        if ($customer) {
            $info[] = $customer->getGroupId();
        }

        if ($filters = $request->getParams()) {
            Vaimo_MultiOptionFilter_Helper_Data::makeFilterCanonical($filters);

            foreach ($filters as $attributeCode => $value) {
                if ($attributeCode=='__status__' || ($attributeCode == 'p' && $value == '1')) {
                    continue;
                }

                $info[] = "$attributeCode-$value";
            }
        }

        return $info;
    }

    public function getCacheTags()
    {
        static $stCacheTags;

        if ($stCacheTags===null) {
            $stCacheTags = parent::getCacheTags();
        }

        return $stCacheTags;
    }
}

