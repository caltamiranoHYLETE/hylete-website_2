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

class Vaimo_MultiOptionFilter_Model_Observer
{
    /**
     * Event: controller_action_layout_render_before
     * Scope: frontend
     */
    public function createFilterBlockProxies()
    {
        $action = Mage::app()->getFrontController()->getAction();

        if (!Mage::helper('multioptionfilter')->isFilterControllerAction($action)) {
            return;
        }

        $layerViewInterception = Mage::getSingleton('multioptionfilter/runtime_proxyFactories_layerView');

        foreach (Mage::helper('multioptionfilter')->getLayerBlocks() as $layerView) {
            $layerViewInterception->create($layerView);
        }
    }

    /**
     * Event: controller_action_layout_load_before
     * Scope: frontend
     *
     * @param $observer
     */
    public function addLayerLayoutTypeHandles(Varien_Event_Observer $observer)
    {
        $action = $observer->getAction();

        if (!Mage::helper('multioptionfilter')->isFilterControllerAction($action)) {
            return;
        }

        if (!Mage::getStoreConfigFlag('multioptionfilter/settings/horizontal_filters')) {
            return;
        }

        Mage::helper('multioptionfilter/layout')->addUpdateHandles($action->getLayout(), array(
            'mof_horizontal',
            $action->getFullActionName() . '_mof_horizontal'
        ));
    }

    /**
     * Event: controller_action_layout_render_before
     * Scope: frontend
     */
    public function fixEnterpriseCategoryFilter()
    {
        $requestVar = Mage::getSingleton('catalog/layer_filter_category')->getRequestVar();

        if (!Mage::app()->getRequest()->getParam($requestVar)) {
            return;
        }

        $helper = Mage::helper('multioptionfilter');

        if ($helper->isEnterpriseThirdPartySearchEnabled()) {
            return;
        }

        if (!$layer = $helper->getLayerSingleton()) {
            return;
        }

        $collection = $layer->getProductCollection();

        $limitations = $collection->getLimitationFilters();

        $collection->setFlag('disable_root_category_filter', false);
        $collection->setVisibility($limitations['visibility']);
    }
}

