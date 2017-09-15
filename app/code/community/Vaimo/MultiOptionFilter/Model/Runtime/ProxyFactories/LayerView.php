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

class Vaimo_MultiOptionFilter_Model_Runtime_ProxyFactories_LayerView
{
    public function create($layerView)
    {
        /** @var Mage_Core_Model_Layout $layout */
        $layout = $layerView->getLayout();

        $showSelectedOptionsInName = $layerView->getShowSelectedOptionsCountInName();

        $interceptorTypes = new Varien_Object(array(
            'default' => 'multioptionfilter/runtime_interceptors_filter'
        ));

        Mage::dispatchEvent('vaimo_multioptionfilter_collect_interceptors', array(
            'interceptor_types' => $interceptorTypes
        ));

        $defaultInterceptorType = $interceptorTypes->getData('default');

        foreach ($layerView->getChild() as $filter) {
            $alias = $filter->getBlockAlias();
            $filter->setShowSelectedOptionsCountInName($showSelectedOptionsInName);

            if ($filter instanceof Mage_Catalog_Block_Layer_Filter_Abstract) {
                $interceptorType = $interceptorTypes->getData($alias);

                if ($interceptorType === null) {
                    $interceptorType = $defaultInterceptorType;
                }

                if (!$interceptorType) {
                    continue;
                }

                $interceptor = Mage::getModel($interceptorType);

                /** @var Mage_Core_Model_Layout $proxy */
                $proxy = Mage::getModel('multioptionfilter/runtime_proxies_blockProxy');

                $proxy->setDelegate($filter);
                $proxy->setInterceptor($interceptor);
                $proxy->setBlockAlias($alias);

                $nameInLayout = $filter->getNameInLayout() . '.mof.proxy';

                $proxy->setNameInLayout($nameInLayout);
                $layout->setBlock($nameInLayout, $proxy);

                $layerView->unsetChild($alias);
                $layerView->setChild($alias, $proxy);
            }

            if ($alias == 'layer_state' && !$layerView->getShowState()) {
                $filter->setTemplate('');
            }
        }

        return $layerView;
    }
}