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
 * @package     Vaimo_Module
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 */

class Vaimo_Blog_Block_Posts_Latest extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    const BLOG_DEFAULT_LIMIT = 5;

    protected function _construct()
    {
        $this->setTemplate('vaimo/blog/block-latest-list.phtml');
        parent::_construct();
    }

    public function getBlogCollection()
    {
        $productType = Mage::getStoreConfig("blog/settings/product_type");
        /** @var Vaimo_Blog_Helper_Data $helperBlog */
        $helperBlog = $this->helper('blog');
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_collection');

        $collection
            ->setStoreId($this->getStoreId())
            ->addStoreFilter()
            ->addAttributeToFilter('status', '1')
            ->addAttributeToFilter('type_id', $productType)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect('blog_content')
            ->addAttributeToSelect('date_written');
        $sortBy = 'date_written';
        if ($helperBlog->isForceSortPublishDateActive() || $helperBlog->isShowPublishDateActive()) {
            $collection->addAttributeToSelect('blog_publish_date');
            $sortBy = 'blog_publish_date';
        }
        if ($helperBlog->isAuthorActive()) {
            $collection->addAttributeToSelect('blog_author');
        }
        $collection
            ->setOrder($sortBy, Varien_Data_Collection::SORT_ORDER_DESC)
            ->setPageSize($this->getLimit())
        ;
        return $collection;
    }

    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->_getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }

    public function getLimit()
    {
        if ($this->hasData('products_count')) {
            return $this->_getData('products_count');
        }
        return self::BLOG_DEFAULT_LIMIT;
    }
}
