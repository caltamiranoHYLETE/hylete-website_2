<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Kjell Holmqvist
 */

class Vaimo_AppApi_Model_Category extends Vaimo_AppApi_Model_Abstract
{

    /**
     * First level of categories include appropriate level of detail, all details levels require a category load
     * Having detailed level on all levels became way to slow, so I only do the detail selection on the first level
     *
     * @param $categories
     * @param $detailLevel
     *
     * @return array
     */
    protected function _prepareCategories($categories, $storeId, $detailLevel)
    {
        $res = array();

        foreach ($categories as $category) {

            if (isset($category['is_active']) && $category['is_active']==0) {
                continue;
            }

            $catObj = $this->_getHelper()->getCategoryDetails($category, $storeId, $detailLevel);

            if (!$catObj) {
                continue;
            }

            $catArr = $this->_getHelper()->dispatchUpdateEventObject( 'app_api_list_category_record', $catObj, array('category' => $category) );

            $res[] = $catArr;
        }

        return $res;
    }

    public function listCategories($websiteId, $storeId, $categoryId, $detailLevel, $treeFlag)
    {
        if ($treeFlag) {
            $categories = $this->_getHelper()->getCategoryTree($websiteId, $storeId, $categoryId);
        } else {
            $categories = $this->_getHelper()->getCategories($websiteId, $storeId, $categoryId);
        }

        $selectedStoreId = $this->_getHelper()->getStoreId($websiteId, $storeId);
        $categoryDetails = $this->_prepareCategories($categories, $selectedStoreId, $detailLevel);

        $res = $this->_getHelper()->dispatchUpdateEventArray( 'app_api_list_category', $categoryDetails, array('categories' => $categories) );

        return $res;
    }

    /**
     * Prepare products media gallery
     *
     * @param  array $productIds
     * @return array
     */
    protected function _prepareMediaGallery(array $productIds)
    {
        if (empty($productIds)) {
            return array();
        }
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $select = $read->select()
                ->from(
                        array('mg' => $resource->getTableName('catalog/product_attribute_media_gallery')),
                        array(
                            'mg.entity_id', 'mg.attribute_id', 'filename' => 'mg.value', 'mgv.label',
                            'mgv.position', 'mgv.disabled'
                        )
                )
                ->joinLeft(
                        array('mgv' => $resource->getTableName('catalog/product_attribute_media_gallery_value')),
                        '(mg.value_id = mgv.value_id AND mgv.store_id = 0)',
                        array()
                )
                ->where('entity_id IN(?)', $productIds);

        $rowMediaGallery = array();
        $stmt = $read->query($select);
        while ($mediaRow = $stmt->fetch()) {
            $rowMediaGallery[$mediaRow['entity_id']][] = array(
                '_media_attribute_id'   => $mediaRow['attribute_id'],
                '_media_image'          => $mediaRow['filename'],
                '_media_lable'          => $mediaRow['label'],
                '_media_position'       => $mediaRow['position'],
                '_media_is_disabled'    => $mediaRow['disabled']
            );
        }

        return $rowMediaGallery;
    }

    public function _prepareProducts($category, $productCollection, $selectedStoreId, $detailLevel)
    {
        $res = array();

        $productCollection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($category->getId());

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($productCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($productCollection);
        
        $mediaGallery = $this->_prepareMediaGallery($productCollection->getAllIds());

        foreach ($productCollection as $product) {
            $prodObj = $this->_getHelper()->getCategoryProductListDetails($product, $selectedStoreId, $mediaGallery, $detailLevel);

            if (!$prodObj) {
                continue;
            }

            $prodArr = $this->_getHelper()->dispatchUpdateEventObject( 'app_api_list_category_product_record', $prodObj, array('product' => $product) );

            $res[] = $prodArr;
        }
        return $res;
    }


    public function listProducts($websiteId, $storeId, $categoryId, $detailLevel)
    {
        $selectedStoreId = $this->_getHelper()->getStoreId($websiteId, $storeId);

        $category = Mage::getModel('catalog/category')
            ->setStoreId($selectedStoreId)
            ->load($categoryId);

        if (!$category->getId()) {
            return 'Wrong category ID';
        }

        $products = $this->_getHelper()->getCategoryProducts($category);
        
        $productDetails = $this->_prepareProducts($category, $products, $selectedStoreId, $detailLevel);

        $res = $this->_getHelper()->dispatchUpdateEventArray( 'app_api_list_category_products', $productDetails, array('products' => $products) );

        return $res;
    }

}
