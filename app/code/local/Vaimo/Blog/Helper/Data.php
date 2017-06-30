<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_Blog
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Tobias Wiking
 */
class Vaimo_Blog_Helper_Data extends Mage_Core_Helper_Abstract
{
    const BLOG_PAGE_LAYOUT_NAME = 'blog_view_list';

    private $_blogRootCategoryId = null;

    protected $_isBlogPage = null;

    private function getCategory()
    {
        $currentCategory = Mage::registry('current_category');

        if (empty($currentCategory)) {
            $currentProduct = Mage::registry('current_product');
            $currentCategory = $currentProduct ? $currentProduct->getCategoryCollection()->getFirstItem() : new Varien_Object();
        }
        return $currentCategory;
    }

    public function getCategoryUrl()
    {
        return $this->getCategory()->getUrl();
    }

    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }

    public function getParentCategoryName()
    {
        $parentId = $this->getCategory()->getParentId();
        if (!is_null($parentId)) {
            $parentCategoryName = Mage::getModel('catalog/category')->load($parentId)->getName();
            return $parentCategoryName;
        }
        return '';
    }

    public function getPublishDate($product, $format)
    {
        $blogPublishDate = date("Y-m-d", strtotime($product->getBlogPublishDate()));
        $today = date("Y-m-d", Mage::getModel('core/date')->timestamp(time()));
        $yesterday = date('Y-m-d', strtotime('-1 day', Mage::getModel('core/date')->timestamp(time())));

        if ($blogPublishDate == $today) {
            return $this->__('Today');
        } else {
            if ($blogPublishDate == $yesterday) {
                return $this->__('Yesterday');
            } else {
                $date = new Zend_Date($product->getBlogPublishDate(), "yyyy-MM-dd");
                $date
                        ->setLocale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE))
                        ->setTimezone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));

                return $date->toString($format);
            }
        }
    }

    public function getCommentsConfig()
    {
        return Mage::getStoreConfig('blog/blog_settings/comments');
    }

    public function getDisqusUsernameConfig()
    {
        return Mage::getStoreConfig('blog/blog_settings/disqus_username');
    }

    public function getExcerptConfig()
    {
        return Mage::getStoreConfig('blog/blog_settings/excerpt');
    }

    public function getSummaryNbrOfCharsConfig()
    {
        return (int)Mage::getStoreConfig('blog/blog_settings/summary_number_of_characters');
    }

    public function getSummarySuffixConfig()
    {
        return Mage::getStoreConfig('blog/blog_settings/summary_suffix');
    }

    public function isRelatedProductsActive()
    {
        return Mage::getStoreConfig('blog/settings/related_products');
    }

    public function isCrosssellActive()
    {
        return Mage::getStoreConfig('blog/settings/crosssell');
    }

    public function isUpsellActive()
    {
        return Mage::getStoreConfig('blog/settings/upsell');
    }

    public function isShowPublishDateActive()
    {
        return Mage::getStoreConfig('blog/settings/show_publish_date');
    }

    /**
     * @deprecated deprecated since version  0.1.45 please use
     * isForceSortPublishDateActive or isShowPublishDateActive accordingly
     *
     * @return bool
     */
    public function isPublishDateActive()
    {
        return Mage::getStoreConfig('blog/settings/publish_date') || Mage::getStoreConfig('blog/settings/force_sort_publish_date');
    }

    public function isForceSortPublishDateActive()
    {
        return Mage::getStoreConfig('blog/settings/force_sort_publish_date');
    }

    public function isAuthorActive()
    {
        return Mage::getStoreConfig('blog/settings/author');
    }

    public function getRelatedName()
    {
        return Mage::getStoreConfig('blog/settings/related_products_rename');
    }

    public function getCrosssellName()
    {
        return Mage::getStoreConfig('blog/settings/crosssell_rename');
    }

    public function getUpsellName()
    {
        return Mage::getStoreConfig('blog/settings/upsell_rename');
    }

    public function getAttrSetConfigPath()
    {
        return 'blog/settings/attribute_set_id';
    }

    public function getTypeConfigPath()
    {
        return 'blog/settings/product_type';
    }

    public function getBlogParentCategoryIdConfig()
    {
        return (int)Mage::getStoreConfig('blog/blog_settings/blog_parent_category_id');
    }

    /**
     * @description Get the blog post's "subjects"
     * @param $product
     * @return mixed
     */
    public function getSubjects($product)
    {
        return Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSort('name', 'asc')
            ->addIdFilter($product->getCategoryIds());
    }

    public function getBlogRootCategoryId()
    {
        return $this->_blogRootCategoryId;
    }

    public function isBlogPage()
    {
        if ($this->_isBlogPage !== null) {
            return $this->_isBlogPage;
        }
        $this->_isBlogPage = false;

        $currentProduct = Mage::registry('current_product');
        $category = Mage::registry('current_category');

        if (!$category && !$currentProduct) {
            return false;
        }
        $this->_isBlogPage = false;
        /** @var Mage_Catalog_Model_Category $category */
        $category = $category ? $category : $currentProduct->getCategoryCollection()->getFirstItem();

        $pathIds = $this->_getCategoryPathIds($category);
        if (!$pathIds) {
            return false;
        }

        /** @var Mage_Catalog_Model_Resource_Category $resource */
        $resource =  Mage::getResourceModel('catalog/category');
        $storeId = Mage::app()->getStore()->getId();

        foreach ($pathIds as $categoryId) {
            $pageLayout = $resource->getAttributeRawValue($categoryId, 'page_layout', $storeId);
            if ($pageLayout === self::BLOG_PAGE_LAYOUT_NAME) {
                $this->_blogRootCategoryId = $categoryId;
                $this->_isBlogPage = true;
                break;
            }
        }

        return $this->_isBlogPage;
    }

    protected function _getCategoryPathIds($category, $popCurrentId = false)
    {
        $pathIds = $category->getData('path_ids');

        if (!$pathIds && $path = $category->getData('path')) {
            $pathIds = explode('/', $path);
            $category->setData('path_ids', $pathIds);
        }
        if (!$pathIds) {
            return false;
        }

        if ($popCurrentId) {
            // remove current ID
            array_pop($pathIds);
        }
        // slice Roots Ids
        $pathIds = array_slice($pathIds, 2);
        if (count($pathIds) == 0) {
            return null;
        }
        // reverse to that last one would be first to foreach items
        $pathIds = array_reverse($pathIds);

        return $pathIds;
    }
}
