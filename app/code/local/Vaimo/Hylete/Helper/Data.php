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
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */
class Vaimo_Hylete_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_productCmsAttributes = array('cms_1', 'cms_2', 'cms_3', 'cms_4');

    /**
     * @param  Mage_Catalog_Model_Product $product
     *
     * @return bool|string
     */
    public function getSizeGuideHtml($product)
    {
        $result = false;

        if ($sizeGuideBlockId = $product->getSizeGuide()) {
            /** @var Mage_Cms_Model_Block $block */
            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($sizeGuideBlockId);

            $helper = Mage::helper('cms');
            $processor = $helper->getBlockTemplateProcessor();

            $result = $processor->filter($block->getContent());
        }

        return $result;
    }


    public function getConfigurableSuperAttribute($product)
    {
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
            $productAttribute = $productAttributeOptions[0];

            return $productAttribute;
        }
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $salable
     *
     * @return array
     */
    public function getSimples($product, $salable = 'all')
    {
        $simples = array();

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            foreach ($product->getTypeInstance(true)->getUsedProducts(null, $product) as $simple) {
                $return = false;
                if ($salable == 'salable' && $simple->isSalable()) {
                    $return = true;

                } elseif ($salable == 'nonsalable' && !$simple->isSalable()) {
                    $return = true;

                } elseif ($salable == 'all') {
                    $return = true;
                }

                if ($return) {
                    $simples[] = $simple;
                }
            }
        }

        return $simples;
    }


    public function getCategoryAttributeLabel($attribute_code, $value)
    {
        $opt = array();
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_category', $attribute_code);
        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
            foreach ($options as $o) {
                $opt[$o['value']] = $o['label'];
            }
        }

        if (isset($opt[$value])) {
            return strtolower($opt[$value]);
        }
    }

    public function getCategory()
    {
        $currentCategory = Mage::registry('current_category');

        if (!isset($currentCategory)) {
            $currentProduct = Mage::registry('current_product');
            $currentCategory = $currentProduct
                    ->getCategoryCollection()
                    ->getFirstItem();
        }
        return $currentCategory;
    }

    /**
     * Returns most viewed products in $category, limited by $productCount
     * @param Mage_Catalog_Model_Category $category
     * @param int $productCount
     * @return Mage_Reports_Model_Resource_Product_Collection
     */
    public function getMostViewedProducts($category,$productCount) {
        $storeId = Mage::app()->getStore()->getId();
        $mostViewed = Mage::getResourceModel('reports/product_collection')
                ->setStoreId($storeId)
                ->addStoreFilter($storeId)
                ->addViewsCount()
                ->setPageSize($productCount)
                ->addCategoryFilter($category)
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('small_image')
                ->addAttributeToFilter('type_id', 'blog')
                ->addUrlRewrite($category->getId());
        return $mostViewed;
    }

    /**
     * @param string $inputString
     * @param string $splitText
     * @return string
     */
    public function splitAndFlipString($inputString,$splitText){
        $wordPosition = strpos($inputString, $splitText);
        return substr($inputString,$wordPosition).substr($inputString,0,$wordPosition);
    }

    /**
     * Returns processed cms blocks collection or empty array in case when no block were assigned to product
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array|Mage_Cms_Model_Resource_Block_Collection
     */
    public function getProductTabsCmsBlocks($product)
    {
        $result = array();

        $storeId = Mage::app()->getStore()->getId();
        /** @var Mage_Cms_Helper_Data $helper */
        $helper = Mage::helper('cms');
        /** @var Varien_Filter_Template $processor */
        $processor = $helper->getBlockTemplateProcessor();

        $cmsIds = array_filter(array_map(function ($e) use ($product) {
            return $product->getData($e);
        }, $this->_productCmsAttributes));

        if (!empty($cmsIds)) {
            $blocks = Mage::getResourceModel('cms/block_collection')
                ->addStoreFilter($storeId)
                ->addFieldToFilter('main_table.block_id', array('in' => $cmsIds));

            foreach ($blocks as $block) {
                $html = $processor->filter($block->getContent());
                $block->setContent($html);
            }

            $result = $blocks;
        }

        return $result;
    }

    /**
     * Returns data array for configurable grid view module
     *
     * @param Mage_Catalog_Model_Product array $associative_products
     * @param array $allowAttributes
     *
     * @return array
     */
    public function getConfigurableModuleDataArray($associative_products,$allowAttributes)
    {

        $assc_product_data = array();
        $labels = array();
        $options = array();
        $helper = Mage::helper('configurablegridview');

        foreach ($associative_products as $assc_products) {

            $productAttributes = $allowAttributes;

            if($assc_products->getStatus() == 1) {

                if($helper->getShowOutStock()) {
                    $stock = number_format(Mage::getModel('cataloginventory/stock_item')->loadByProduct($assc_products)->getQty());

                    $assc_product_data[$assc_products->getId()]['info'] = array('price' => 0, 'qty' => $stock, 'prod_id'=>$assc_products->getId());

                    foreach ($productAttributes as $attribute) {

                        $_attributePrice = $attribute->getPrices();

                        $labels[$attribute->getLabel()] = $attribute->getLabel();

                        $value = $assc_products->getResource()->getAttribute($attribute->getProductAttribute()->getAttributeCode())->getFrontend()->getValue($assc_products);
                        $options[$value] = $value;
                        $att_array = array('code' => $attribute->getProductAttribute()->getAttributeCode(), 'label' => $attribute->getLabel(), 'value' => $value, 'attribute_id' => $attribute->getAttributeId());

                        foreach($_attributePrice as $optionVal){
                            if($optionVal['label'] == $value){
                                $att_array['option_id'] = $optionVal['value_index'];
                                $att_array['pricing_value'] = $optionVal['pricing_value'];
                                $att_array['is_percent'] = $optionVal['is_percent'];

                            }
                        }

                        $assc_product_data[$assc_products->getId()]['attributes'][] = $att_array;
                    }
                } else {

                    if($assc_products->isSaleable()) {
                        $stock = number_format(Mage::getModel('cataloginventory/stock_item')->loadByProduct($assc_products)->getQty());

                        $assc_product_data[$assc_products->getId()]['info'] = array('price' => 0, 'qty' => $stock, 'prod_id'=>$assc_products->getId());

                        foreach ($productAttributes as $attribute) {

                            $_attributePrice = $attribute->getPrices();

                            $labels[$attribute->getLabel()] = $attribute->getLabel();

                            $value = $assc_products->getResource()->getAttribute($attribute->getProductAttribute()->getAttributeCode())->getFrontend()->getValue($assc_products);
                            $options[$value] = $value;
                            $att_array = array('code' => $attribute->getProductAttribute()->getAttributeCode(), 'label' => $attribute->getLabel(), 'value' => $value, 'attribute_id' => $attribute->getAttributeId());

                            foreach($_attributePrice as $optionVal){
                                if($optionVal['label'] == $value){
                                    $att_array['option_id'] = $optionVal['value_index'];
                                    $att_array['pricing_value'] = $optionVal['pricing_value'];
                                    $att_array['is_percent'] = $optionVal['is_percent'];

                                }
                            }
                            $assc_product_data[$assc_products->getId()]['attributes'][] = $att_array;
                        }
                    }
                }
            }
        }


        $configurable_products = array('num_attributes' => count($allowAttributes), 'products' => $assc_product_data, 'labels' => $labels, 'options' => $options);

        return $configurable_products;
    }

    /**
     * Retrieve value from setting to enable or disable header cart dropdown
     *
     * @return bool
     */
    public function showHeaderCartDropDown()
    {
        return Mage::getStoreConfigFlag('vaimo_hylete/header_cart_dropdown/enable');
    }

    /**
     * Retrieve value from setting to enable or disable extended sign up form
     *
     * @return bool
     */
    public function useExtendedSignupWidget()
    {
        return Mage::getStoreConfigFlag('vaimo_hylete/signup_form_extended/enable');
    }

    public function getSelectedProductsCollection(Icommerce_SelectedProducts_Block_Collection $block)
    {
        $collectionAttributes = array(
            'entity_id',
            'sku',
            'image',
            'name',
            'msrp',
            'special_price',
            'special_from_date',
            'special_to_date',
            'special_price_label',
            'multipack_offer',
        );

        return $block->getCollection('all', $collectionGetAmount = 3, $collectionDescSort = true, $collectionAttributes);
    }
}
