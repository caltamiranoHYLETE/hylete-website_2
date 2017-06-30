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
 */
class Vaimo_Blog_Model_Observer
{
    public function addHandles($observer)
    {
        $category = Mage::registry('current_category');
        if (!$category || !($category instanceof Mage_Catalog_Model_Category)) {
            return $this;
        }

        $subCategoriesInheritLayout = Mage::getStoreConfig('blog/blog_settings/subcategories_inherits_blog_listing') != 0;

        if ($category->getPageLayout() == 'blog_view_list' || ($subCategoriesInheritLayout && Mage::helper('blog')->isBlogPage())) {
            $update = Mage::getSingleton('core/layout')->getUpdate();
            $update->addHandle('blog_view_list');
        }
        return $this;
    }

    public function editTabs($observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block->getType() != 'adminhtml/catalog_product_edit_tabs' || $block->getProduct()->getTypeId() != 'blog') {
            return $this;
        }
        /** @var Vaimo_Blog_Helper_Data $helper */
        $helper = Mage::helper('blog');

        if (!$helper->isRelatedProductsActive()) {
            $block->removeTab('related');
        } elseif ($helper->getRelatedName() != '') {
            $block->setTabData('related', 'label', $helper->getRelatedName());
            $block->setTabData('related', 'title', $helper->getRelatedName());
        }

        if (!$helper->isCrosssellActive()) {
            $block->removeTab('crosssell');
        } elseif ($helper->getCrosssellName() != '') {
            $block->setTabData('crosssell', 'label', $helper->getCrosssellName());
            $block->setTabData('crosssell', 'title', $helper->getCrosssellName());
        }

        if (!$helper->isUpsellActive()) {
            $block->removeTab('upsell');
        } elseif ($helper->getUpsellName() != '') {
            $block->setTabData('upsell', 'label', $helper->getUpsellName());
            $block->setTabData('upsell', 'title', $helper->getUpsellName());
        }

        //$block->removeTab('inventory');
        $block->removeTab('reviews');
        $block->removeTab('tags');
        $block->removeTab('customers_tags');
        $block->removeTab('customer_options');

        $attrSetId = $block->getProduct()->getAttributeSetId();

        $tabsIds = Icommerce_Db::getRows('SELECT attribute_group_id FROM eav_attribute_group WHERE attribute_set_id = ? AND (attribute_group_name = ? OR attribute_group_name = ?)', array($attrSetId, 'Extras', 'Gift Options'));

        foreach ($tabsIds as $tabId) {
            $block->removeTab('group_' . $tabId['attribute_group_id']);
        }
    }

    public function productCollection($observer)
    {
        $category = Mage::registry('current_category');
        if (!$category || !($category instanceof Mage_Catalog_Model_Category) || $category->getPageLayout() != 'blog_view_list') {
            return $this;
        }

        if (Mage::helper('blog')->isForceSortPublishDateActive()) {
            $collection = $observer->getCollection();
            $collection->setOrder('blog_publish_date', 'desc');
        }
    }

    /**
     * Auto-generate SKUs for blog product
     * Since 1.13.0.1 the collection soemtimes won't load. Debugging showed that the products are not being added to the catalog_product_price_index table
     * which is used to load them, because they saved as with manage_stock false, so they won't be indexed. I added the manage stock and stock flag here as well /Giorgos
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function onCatalogProductSaveBefore(Varien_Event_Observer $observer)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        if ($product->getTypeId() == 'blog') {

            $hasSku = false;
            $hasStock = false;

            if ($product->getSku()) {
                $hasSku = true;
            }

            $stockData = $product->getData('stock_data');
            if (isset($stockData['manage_stock']) && $stockData['manage_stock'] && isset($stockData['is_in_stock']) && $stockData['is_in_stock']) {
            	$hasStock = true;
            }

            if ($hasStock && $hasSku) {
                return $this;
            }

            if (!$hasSku) {
                $date = new DateTime('now');
                $datetime = $date->format('YmdHis');
                $maxId = Icommerce_Db::getDbRead()->fetchOne('SELECT MAX(entity_id)+1 FROM catalog_product_entity');
                $rndNumber = rand(1, 10);
                $sku = sprintf('%s_%s_%s_%s', $product->getTypeId(), $rndNumber, $maxId, $datetime);

                $product->setSku($sku);
            }

            if (!$hasStock) {
                $stockData['manage_stock'] = 1;
            	$stockData['is_in_stock'] = 1;
            	$product->setData('stock_data', $stockData);
            }
        }

        return $this;
    }

    /**
     * Set active menu to blog if needed
     *
     * Check if we are on add new or edit blog post page,
     * and set active menu to blog if we are
     *
     */
    function onProductAddOrEdit() {
        $_product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('id'));
        $productType = Mage::getStoreConfig("blog/settings/product_type");
        $productTypeId = $_product->getTypeId();
        if($productTypeId == $productType || Mage::app()->getRequest()->getParam('type')==$productType) {
            // mark "Blog" button as active
            Mage::getSingleton('core/layout')->getBlock('menu')->setActive('blog/blog_posts');
        }
    }

}
