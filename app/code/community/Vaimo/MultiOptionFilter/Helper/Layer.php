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

class Vaimo_MultiOptionFilter_Helper_Layer extends Mage_Core_Helper_Abstract
{
    public function proxyLayerViewChildren(Mage_Core_Block_Abstract $layerView)
    {
        $layout = $layerView->getLayout();

        $filterBlocks = $layerView->getChild();

        foreach ($filterBlocks as $filter) {
            $name = $filter->getBlockAlias();

            if ($filter instanceof Mage_Catalog_Block_Layer_Filter_Abstract) {
                $proxy = $layout->createBlock('multioptionfilter/layer_filter_proxy');
                $layerView->unsetChild($name);

                $proxy->setDelegate($filter);
                $proxy->setBlockAlias($name);

                $showSelectedOptionsInName = $layerView->getShowSelectedOptionsCountInName();
                $proxy->setShowSelectedOptionsCountInName($showSelectedOptionsInName);

                $layerView->setChild($name, $proxy);
            }

            if ($name == 'layer_state' && !$layerView->getShowState()) {
                $filter->setTemplate('');
            }
        }
    }
}