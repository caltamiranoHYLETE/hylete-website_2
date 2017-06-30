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

class Vaimo_MultiOptionFilter_Model_Observer
{
    /**
     * Event: controller_action_layout_render_before
     * Scope: frontend
     *
     * @param $observer
     */
    public function createFilterBlockProxies(Varien_Event_Observer $observer)
    {
        $action = Mage::app()->getFrontController()->getAction();

        if (!Mage::helper('multioptionfilter')->isFilterControllerAction($action)) {
            return;
        }

        $layerViews = Mage::helper('multioptionfilter')->getLayerBlocks();

        if (empty($layerViews)) {
            return;
        }

        $layerHelper = Mage::helper('multioptionfilter/layer');

        foreach ($layerViews as $layerView) {
            $layerHelper->proxyLayerViewChildren($layerView);
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

        if (!Mage::getStoreConfigFlag(Vaimo_MultiOptionFilter_Helper_Type::XPATH_HORIZONTAL_ENABLED)) {
            return;
        }

        Mage::helper('multioptionfilter/type')->change(
            $action->getLayout(), Vaimo_MultiOptionFilter_Helper_Type::HORIZONTAL);
    }

    /**
     * Event: controller_action_layout_render_before
     * Scope: frontend
     *
     * @param $observer
     */
    public function fixEnterpriseCategoryFilter(Varien_Event_Observer $observer)
    {
        $requestVar = Mage::getSingleton('catalog/layer_filter_category')->getRequestVar();

        if (!Mage::app()->getRequest()->getParam($requestVar)) {
            return;
        }

        $helper = Mage::helper('multioptionfilter');

        if ($helper->isEnterpriseThirdPartSearchEnabled()) {
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

