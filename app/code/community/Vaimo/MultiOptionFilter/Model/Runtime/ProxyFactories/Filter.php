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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_MultiOptionFilter_Model_Runtime_ProxyFactories_Filter
{
    public function create($filter)
    {
        $helper = Mage::getResourceHelper('multioptionfilter');
        $proxyHelper = Mage::helper('multioptionfilter/proxy');
        $queryManipulator = Mage::getResourceSingleton('multioptionfilter/query_manipulator');

        $mofOverride = function ($select) use ($queryManipulator) {
            return (string)$queryManipulator->setCountsToDistinctMode($select);
        };

        $layer = $filter->getLayer();
        $collection = $layer->getProductCollection();

        $overrides = array(
            'getSelect' => $helper->createInterceptedClone($collection->getSelect(), $mofOverride),
            'getCatalogPreparedSelect' => $helper->createInterceptedClone(
                $collection->getCatalogPreparedSelect(),
                $mofOverride
            )
        );

        $layerProxy = $proxyHelper->createInstance($layer, array(
            'getProductCollection' => $proxyHelper->createInstance($collection, array_filter($overrides))
        ));

        return Mage::helper('multioptionfilter/proxy')->createInstance($filter, array(
            'getLayer' => $layerProxy
        ));
    }
}
