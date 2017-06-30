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

class Vaimo_MultiOptionFilter_Helper_Proxy extends Mage_Core_Helper_Abstract
{
    public function createFilterResourceProxy($filter, $resource, $filterProxy)
    {
        $mofLayer = Mage::getSingleton('multioptionfilter/layer');

        $selectManipulator = function($select) use ($mofLayer) {
            $mofLayer->applyForCount($select);

            return (string)$select;
        };

        $layer = $filter->getLayer();
        $collection = $layer->getProductCollection();

        $selectProxy = Mage::getModel('multioptionfilter/proxy')
            ->setDelegate(clone $collection->getSelect())
            ->setOverride('__toString', $selectManipulator);

        $collectionProxy = Mage::getModel('multioptionfilter/proxy')
            ->setDelegate($collection)
            ->setOverride('getSelect', $selectProxy);

        if ($preparedSelect = $collection->getCatalogPreparedSelect()) {
            $preparedSelectProxy = Mage::getModel('multioptionfilter/proxy')
                ->setDelegate(clone $preparedSelect)
                ->setOverride('__toString', $selectManipulator);

            $collectionProxy->setOverride('getCatalogPreparedSelect', $preparedSelectProxy);
        }

        $layerProxy = Mage::getModel('multioptionfilter/proxy')
            ->setDelegate($layer)
            ->setOverride('getProductCollection', $collectionProxy);

        $filterProxy->setDelegate($filter)
            ->setOverride('getLayer', $layerProxy);

        return Mage::getModel('multioptionfilter/proxy')
            ->setDelegate($resource);
    }
}