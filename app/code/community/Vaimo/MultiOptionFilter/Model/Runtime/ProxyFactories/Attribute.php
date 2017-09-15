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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_MultiOptionFilter_Model_Runtime_ProxyFactories_Attribute
{
    public function create($filter, $filterResource)
    {
        $filterProxy = Mage::getSingleton('multioptionfilter/runtime_proxyFactories_filter')
            ->create($filter);

        $shouldResetGroup = Mage::helper('core')->isModuleEnabled('Enterprise_CatalogPermissions');
        $select = $filterProxy->getLayer()->getProductCollection()->getSelect();

        return Mage::helper('multioptionfilter/proxy')->createInstance($filterResource, array(
            'applyFilterToCollection' => function ($delegate, $filter, $value) {
                $collection = $filter->getLayer()->getProductCollection();

                $selectBefore = clone $collection->getSelect();

                $delegate->applyFilterToCollection($filter, $value);

                $converters = array(
                    Mage::getResourceSingleton('multioptionfilter/statement_converter_SingleToMultiple')
                );

                $queryManipulator = Mage::getResourceSingleton('multioptionfilter/query_manipulator');

                $queryManipulator->updateAddedJoins(
                    $selectBefore,
                    $collection->getSelect()->distinct(true),
                    $converters
                );
            },
            'getCount' => function ($delegate, $filter) use ($filterProxy, $select, $shouldResetGroup) {
                $filterProxy->setDelegate($filter);

                if ($shouldResetGroup) {
                    $select->reset(Zend_Db_Select::GROUP);
                }

                return $delegate->getCount($filterProxy);
            }
        ));
    }
}
