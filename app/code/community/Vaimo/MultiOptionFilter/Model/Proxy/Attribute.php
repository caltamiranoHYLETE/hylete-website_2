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

class Vaimo_MultiOptionFilter_Model_Proxy_Attribute
{
    public function create($filter, $filterResource)
    {
        $filterProxy = Mage::getModel('multioptionfilter/proxy');
        $mofLayer = Mage::getSingleton('multioptionfilter/layer');

        $resourceProxy = Mage::helper('multioptionfilter/proxy')
            ->createFilterResourceProxy($filter, $filterResource, $filterProxy);

        $shouldResetGroup = Mage::helper('core')->isModuleEnabled('Enterprise_CatalogPermissions');
        $select = $filterProxy->getLayer()->getProductCollection()->getSelect();

        $resourceProxy->setOverride('applyFilterToCollection', function($delegate, $filter, $value) use ($mofLayer) {
            $collection = $filter->getLayer()->getProductCollection();
            $selectBefore = clone $collection->getSelect();

            $delegate->applyFilterToCollection($filter, $value);
            $mofLayer->applyForFiltering($filter, $selectBefore);
        });

        $resourceProxy->setOverride('getCount', function($delegate, $filter) use ($filterProxy, $select, $shouldResetGroup) {
            $filterProxy->setDelegate($filter);

            if ($shouldResetGroup) {
                $select->reset(Zend_Db_Select::GROUP);
            }

            return $delegate->getCount($filterProxy);
        });

        return $resourceProxy;
    }
}