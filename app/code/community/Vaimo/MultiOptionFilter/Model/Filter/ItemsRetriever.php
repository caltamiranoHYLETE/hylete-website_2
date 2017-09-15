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

class Vaimo_MultiOptionFilter_Model_Filter_ItemsRetriever
{
    /**
     * @var Vaimo_MultiOptionFilter_Helper_Layer
     */
    protected $_layerHelper;

    /**
     * @var Vaimo_MultiOptionFilter_Helper_Filter
     */
    protected $_filterHelper;

    /**
     * @var Vaimo_MultiOptionFilter_Model_Layer_ProductCollectionUtils
     */
    protected $_productCollectionUtils;

    public function __construct()
    {
        $this->_layerHelper = Mage::helper('multioptionfilter/layer');
        $this->_filterHelper = Mage::helper('multioptionfilter/filter');
        $this->_productCollectionUtils = Mage::getSingleton('multioptionfilter/layer_productCollectionUtils');
    }

    public function execute($block, $requestCode)
    {
        /** @var Mage_Catalog_Model_Layer $fullLayer */
        $fullLayer = $block->getLayer();

        $partialBlock = $this->_filterHelper->getFilterBlockClone($block);

        /** @var Mage_Catalog_Model_Layer $partialLayer */
        $partialLayer = $partialBlock->getLayer();

        $request = Mage::app()->getRequest();

        foreach ($this->_layerHelper->getAppliedFilters($fullLayer, [$requestCode]) as $filter) {
            $filter->setLayer($partialLayer);
            $filter->apply($request, $partialBlock);
            $filter->setLayer($fullLayer);
        }

        $partialBlock->getLayer()->apply();

        $this->_productCollectionUtils->synchronize(
            $fullLayer->getProductCollection(),
            $partialLayer->getProductCollection()
        );

        return $partialBlock->init()->getItems();
    }
}
