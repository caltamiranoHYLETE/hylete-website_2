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
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 * @author      Tobias Åström
 */

class Vaimo_AppApi_Model_Search extends Vaimo_AppApi_Model_Abstract
{
    public function listProducts($term, $detailLevel, $websiteId = 0, $storeId = null)
    {
        $selectedStoreId = $this->_getHelper()->getStoreId($websiteId, $storeId);
        $query = Mage::getModel('catalogsearch/query')->setQueryText($term)->prepare();
        Mage::getResourceModel('catalogsearch/fulltext')->prepareResult(
            Mage::getModel('catalogsearch/fulltext'),
            $term,
            $query
        );

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->getSelect()->joinInner(
            array('search_result' => $collection->getTable('catalogsearch/result')),
            $collection->getConnection()->quoteInto(
                'search_result.product_id=e.entity_id AND search_result.query_id=?',
                $query->getId()
            ),
            array('relevance' => 'relevance')
        );

        $productDetails = $this->_prepareProducts($collection, $detailLevel, $selectedStoreId);

        $res = $this->_getHelper()->dispatchUpdateEventArray('app_api_list_search_products', $productDetails, array('products' => $collection));

        return $res;
    }

    protected function _prepareProducts($productCollection, $detailLevel, $selectedStoreId = null)
    {
        $res = array();

        $productCollection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->setStoreId($selectedStoreId);

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($productCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($productCollection);

        foreach ($productCollection as $product) {
            $prodObj = $this->_getHelper()->getProductDetails($product, $detailLevel);

            if (!$prodObj) {
                continue;
            }

            $prodArr = $this->_getHelper()->dispatchUpdateEventObject('app_api_list_search_product_record', $prodObj, array('product' => $product));

            $res[] = $prodArr;
        }

        return $res;
    }
}