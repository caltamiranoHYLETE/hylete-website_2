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

class Vaimo_AppApi_Helper_Data extends Mage_Core_Helper_Abstract
{
    const APP_API_DETAIL_LEVEL_FULL     = 'full';
    const APP_API_DETAIL_LEVEL_DEFAULT  = 'default';
    const APP_API_DETAIL_LEVEL_SMART    = 'smart';
    const APP_API_DETAIL_LEVEL_MINMIAL  = 'minimal';

    public function getStoreId($websiteId = null, $storeId = null)
    {
        $res = $storeId;
        if ($websiteId) {
            if (!$storeId) {
                $res =  Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
            }
        }
        if (!$res) {
            $res = Mage::app()->getStore()->getId();
        }
        return $res;
    }

    public function getCategories($websiteId = null, $storeId = null, $categoryId = null)
    {
        if ($websiteId) {
            if (!$storeId) {
                $storeId =  Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
            }
        }
        if (!$categoryId) {
            if ($storeId) {
                $categoryId = Mage::getSingleton('catalog/url')->getStoreRootCategory($storeId)->getId();
            }
        }
        return Mage::getModel('catalog/category_api')->level($websiteId, $storeId, $categoryId);
    }
    
    public function getCategoryTree($websiteId = null, $storeId = null, $categoryId = null)
    {
        if ($websiteId) {
            if (!$storeId) {
                $storeId =  Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
            }
        }
        if (!$categoryId) {
            if ($storeId) {
                $categoryId = Mage::getSingleton('catalog/url')->getStoreRootCategory($storeId)->getId();
            }
        }
        $tree = Mage::getModel('catalog/category_api')->tree($categoryId, $storeId);
        if (isset($tree['children'])) {
            return $tree['children'];
        } else {
            return array();
        }
    }

    public function getCategoryDetails($categoryArr, $storeId, $detailLevel)
    {
        $category = NULL;
        switch ($detailLevel) {
            case self::APP_API_DETAIL_LEVEL_FULL:
                $category = Mage::getModel('catalog/category')
                    ->setStoreId($storeId)
                    ->load($categoryArr['category_id']);
                if (!$category || !$category->getId()) {
                    return null;
                }
                $res = new Varien_Object($category->getData());
                break;
            case self::APP_API_DETAIL_LEVEL_SMART:
                $category = Mage::getModel('catalog/category')
                    ->setStoreId($storeId)
                    ->load($categoryArr['category_id']);
                if (!$category || !$category->getId()) {
                    return null;
                }
                $res = new Varien_Object(array(
                    'entity_id' => $category->getId(),
                    'name' => $category->getName(),
                    'parent_id' => $category->getParentId(),
                    'description' => $category->getDescription(),
                    'description' => $category->getDescription(),
                    'meta_description' => $category->getMetaDescription(),
                    'meta_title' => $category->getMetaTitle(),
                    'path' => $category->getPath(),
                    'display_mode' => $category->getDisplayMode(),
                    'url_key' => $category->getUrlKey(),
                    'url_path' => $category->getUrlPath(),
                ));
                if (isset($categoryArr['url'])) {
                    $res->setUrl($categoryArr['url']);
                }
                break;
            case self::APP_API_DETAIL_LEVEL_MINMIAL:
                $res = new Varien_Object(array(
                    'entity_id' => $categoryArr['category_id'],
                    'name' => $categoryArr['name'],
                    'parent_id' => $categoryArr['parent_id'],

                ));
                break;
            default:
                $res = new Varien_Object($categoryArr);
                break;
        }
        $res->setData('api_record_type', 'category');

        if (isset($categoryArr['children']) && $categoryArr['children']) {
            $res->setData('children', $categoryArr['children']);
        }
        if ($category) {
            $res->setCategory($category);
        }
        return $res;
    }

    public function getCategoryMenuDetails($categoryArr, $storeId)
    {
        $res = $this->getCategoryDetails($categoryArr, $storeId, self::APP_API_DETAIL_LEVEL_SMART);
        return $res;
    }

    public function dispatchUpdateEventArray($eventCode, $arr, $additionalParams = array())
    {
        $obj = new Varien_Object();
        $obj->setData('list', $arr);
        Mage::dispatchEvent( $eventCode, array_merge(array('object' => $obj), $additionalParams) );
        return (array)$obj->getList();
    }

    public function dispatchUpdateEventObject($eventCode, $obj, $additionalParams = array())
    {
        Mage::dispatchEvent( $eventCode, array_merge(array('object' => $obj), $additionalParams) );
        return (array)$obj->getData();
    }

    protected function _getImageUrls($mediaGallery, $entityId)
    {
        $imageUrls = array();
        if (isset($mediaGallery[$entityId])) {
            foreach ($mediaGallery[$entityId] as $image) {
                $imageUrls[] = Mage::getSingleton('catalog/product_media_config')->getMediaUrl($image['_media_image']);
            }
        }
        if (sizeof($imageUrls)==0) {
            $imageUrls[] = Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg');
        }
        return $imageUrls;
    }

    public function getCategoryProducts($category)
    {
        $productCollection = $category
            ->getProductCollection()
            ->addAttributeToSort('position')
            //->addAttributeToFilter('is_in_stock', 1)
        ;
        return $productCollection;
    }

    protected function _getProductImages($product)
    {
        $mediaGallery = $product->getMediaGalleryImages();
        $imageUrls = array();
        if ($mediaGallery) {
            foreach ($mediaGallery as $item) {
                $imageUrls[] = $item->getUrl();
            }
        }
        if (sizeof($imageUrls)==0) {
            $imageUrls[] = Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg');
        }
        return $imageUrls;
    }

    protected function _getSuperAttributes($confAttributes)
    {
        $res = array();

        foreach ($confAttributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $arr = array(
                'attribute_id'   => $productAttribute->getId(),
                'attribute_code' => $productAttribute->getAttributeCode(),
                'label'          => $attribute->getLabel(),
                'frontend_label' => $productAttribute->getFrontend()->getLabel(),
                'store_label'    => $productAttribute->getStoreLabel(),
            );
            $res[] = $arr;
        }
        return $res;
    }

    protected function _getProductChildren($simpleProducts, $confAttributes)
    {
        $res = array();
        foreach ($simpleProducts as $simple) {
            if ($simple->getIsSalable()) {
                $arr = array(
                    'entity_id' => $simple->getId(),
                    'sku' => $simple->getSku(),
                    'name' => $simple->getName(),
                );

                $stockItem = $simple->getStockItem();
                if ($stockItem) {
                    $arr['qty'] = $stockItem->getQty();
                }
                foreach ($confAttributes as $attribute) {
                    $productAttribute = $attribute->getProductAttribute();
                    $attributeCode = $productAttribute->getAttributeCode();
                    $arr[$attributeCode] = $simple->getData($attributeCode);
                    $options = $productAttribute->getSource()->getAllOptions(false);
                    if (isset($options[$simple->getData($attributeCode)])) {
                        if (isset($options[$simple->getData($attributeCode)]['label'])) {
                            $arr[$attributeCode . '-id'] =$arr[$attributeCode];
                            $arr[$attributeCode] = $options[$simple->getData($attributeCode)]['label'];
                        }
                    }

                }
                $arr['image_urls'] = $this->_getProductImages($simple);
                $res[] = $arr;
            }
        }
        return $res;
    }

    protected function _getConfigurableProductArrays($product, &$confAttributes, &$simpleProducts)
    {
        if ($product->isConfigurable()) {
            $confAttributes = $product->getTypeInstance()->getConfigurableAttributes($product);
            $simpleProducts = $product->getTypeInstance()->getUsedProducts(NULL, $product);
        }
    }

    public function getCategoryProductListDetails($productArr, $storeId, $mediaGallery, $detailLevel)
    {
        $confAttributes = NULL;
        $simpleProducts = NULL;
        $product = NULL;

        switch ($detailLevel) {
            case self::APP_API_DETAIL_LEVEL_FULL:
                $product = Mage::getModel('catalog/product')
                    ->setStoreId($storeId)
                    ->load($productArr['entity_id']);
                if (!$product || !$product->getId()) {
                    return null;
                }
                $this->_getConfigurableProductArrays($product, $confAttributes, $simpleProducts);
                $arr = $product->getData();
                $arr['image_urls'] = $this->_getProductImages($product);
                break;
            case self::APP_API_DETAIL_LEVEL_SMART:
                $product = Mage::getModel('catalog/product')
                    ->setStoreId($storeId)
                    ->load($productArr['entity_id']);
                if (!$product || !$product->getId()) {
                    return null;
                }
                $this->_getConfigurableProductArrays($product, $confAttributes, $simpleProducts);
                $arr = array(
                    'entity_id' => $productArr['entity_id'],
                    'sku' => $productArr['sku'],
                    'name' => $productArr['name'],
                    'price' => $productArr['price'],
                    'final_price' => $productArr['final_price'],
                    'special_price' => $productArr['special_price'],
                    'tax_percent' => $productArr['tax_percent'],
                    'short_description' => $productArr['short_description'],
                );
                $arr['image_urls'] = $this->_getProductImages($product);
                break;
            case self::APP_API_DETAIL_LEVEL_MINMIAL:
                $arr = array(
                    'entity_id' => $productArr['entity_id'],
                    'sku' => $productArr['sku'],
                    'name' => $productArr['name'],
                    'price' => $productArr['price'],
                    'final_price' => $productArr['final_price'],
                    'special_price' => $productArr['special_price'],
                    'tax_percent' => $productArr['tax_percent'],
                    'short_description' => $productArr['short_description'],
                );
                $arr['image_urls'] = $this->_getImageUrls($mediaGallery, $productArr['entity_id']);
                break;
            default:
                $arr = $productArr;
                $arr['image_urls'] = $this->_getImageUrls($mediaGallery, $productArr['entity_id']);
                break;
        }
        if (isset($productArr['children']) && $productArr['children']) {
            $arr['children'] = $productArr['children'];
        }
        if ($confAttributes && $simpleProducts) {
            $arr['super_attributes'] = $this->_getSuperAttributes($confAttributes);
            $arr['product_children'] = $this->_getProductChildren($simpleProducts, $confAttributes);
        }
        $arr['api_record_type'] = 'product';

        $res = new Varien_Object($arr);

        if ($product) {
            $res->setProduct($product);
        }
        return $res;
    }

    public function getProductDetails($product, $detailLevel)
    {
        $confAttributes = NULL;
        $simpleProducts = NULL;
        $relatedIds = NULL;
        $upsellIds = NULL;
        $crossellIds = NULL;

        switch ($detailLevel) {
            case self::APP_API_DETAIL_LEVEL_FULL:
                $this->_getConfigurableProductArrays($product, $confAttributes, $simpleProducts);
                $relatedIds = $product->getRelatedProductIds();
                $upsellIds = $product->getUpSellProductIds();
                $crossellIds = $product->getCrossSellProductIds();
                $arr = $product->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_SMART:
                $this->_getConfigurableProductArrays($product, $confAttributes, $simpleProducts);
                $relatedIds = $product->getRelatedProductIds();
                $upsellIds = $product->getUpSellProductIds();
                $crossellIds = $product->getCrossSellProductIds();
                $arr = array(
                    'entity_id' => $product->getId(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'final_price' => $product->getFinalPrice(),
                    'special_price' => $product->getSpecialPrice(),
                    'tax_percent' => $product->getTaxPercent(),
                    'short_description' => $product->getShortDescription(),
                );
                break;
            case self::APP_API_DETAIL_LEVEL_MINMIAL:
                $arr = array(
                    'entity_id' => $product->getId(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'final_price' => $product->getFinalPrice(),
                    'special_price' => $product->getSpecialPrice(),
                    'tax_percent' => $product->getTaxPercent(),
                    'short_description' => $product->getShortDescription(),
                );
                break;
            default:
                $arr = $product->getData();
                break;
        }
        if ($confAttributes && $simpleProducts) {
            $arr['super_attributes'] = $this->_getSuperAttributes($confAttributes);
            $arr['product_children'] = $this->_getProductChildren($simpleProducts, $confAttributes);
        }
        if ($relatedIds) {
            $arr['related_product_ids'] = $relatedIds;
        }
        if ($upsellIds) {
            $arr['upsell_product_ids'] = $upsellIds;
        }
        if ($crossellIds) {
            $arr['crossell_product_ids'] = $crossellIds;
        }
        $arr['image_urls'] = $this->_getProductImages($product);
        $arr['api_record_type'] = 'product';

        $res = new Varien_Object($arr);
        return $res;
    }

    public function getCustomerDetails($customer, $detailLevel)
    {
        // TODO: Handle detail level

        /*switch ($detailLevel) {
            case self::APP_API_DETAIL_LEVEL_FULL:
                $arr = $customer->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_SMART:
                $arr = $customer->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_MINMIAL:
                $arr = $customer->getData();
                break;
            default:
                $arr = $customer->getData();
                break;
        }*/

        $arr = array(
            'entity_id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'middlename' => $customer->getMiddlename(),
            'lastname' => $customer->getLastname(),
            'suffix' => $customer->getSuffix(),
        );

        if ($detailLevel != self::APP_API_DETAIL_LEVEL_MINMIAL) {
            $arr['group_id'] = $customer->getGroupId();
            $arr['store_id'] = $customer->getStoreId();
            $arr['created_at'] = $customer->getCreatedAt();
            $arr['updated_at'] = $customer->getUpdatedAt();
            $arr['is_active'] = $customer->getIsActive();
        }

        $arr['api_record_type'] = 'customer';

        $res = new Varien_Object($arr);
        return $res;
    }

    public function getAddressDetails($address, $detailLevel)
    {
        // TODO: Handle detail level

        /*switch ($detailLevel) {
            case self::APP_API_DETAIL_LEVEL_FULL:
                $arr = $address->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_SMART:
                $arr = $address->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_MINMIAL:
                $arr = $address->getData();
                break;
            default:
                $arr = $address->getData();
                break;
        }*/

        $arr = $address->getData();

        $arr['api_record_type'] = 'address';

        $res = new Varien_Object($arr);
        return $res;
    }

    public function getWishlistDetails($item, $detailLevel)
    {
        // TODO: Handle detail level

        /*switch ($detailLevel) {
            case self::APP_API_DETAIL_LEVEL_FULL:
                $arr = $item->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_SMART:
                $arr = $item->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_MINMIAL:
                $arr = $item->getData();
                break;
            default:
                $arr = $item->getData();
                break;
        }*/

        $arr = array(
            'entity_id' => $item->getWishlistItemId(),
            'wishlist_id' => $item->getWishlistId(),
            'product_id' => $item->getProductId(),
            'store_id' => $item->getStoreId(),
            'description' => $item->getDescription(),
            'qty' => $item->getQty(),
        );

        $arr['api_record_type'] = 'wishlist';

        $res = new Varien_Object($arr);
        return $res;
    }

    public function getOrderDetails($order, $detailLevel)
    {
        // TODO: Handle detail level

        /*switch ($detailLevel) {
            case self::APP_API_DETAIL_LEVEL_FULL:
                $arr = $order->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_SMART:
                $arr = $order->getData();
                break;
            case self::APP_API_DETAIL_LEVEL_MINMIAL:
                $arr = $order->getData();
                break;
            default:
                $arr = $order->getData();
                break;
        }*/

        $arr = $order->getData();

        $arr['api_record_type'] = 'order';

        $res = new Varien_Object($arr);
        return $res;
    }

    public function getStoreLocatorCountries()
    {
        try {
            $res = Mage::helper('storelocator')->getCountries(false);
        } catch (Exception $e) {
            $res = array();
        }
        return $res;
    }
    
    public function getStoreLocatorStores()
    {
        try {
            $res = Mage::helper('storelocator')->getAllStores();
        } catch (Exception $e) {
            $res = null;
        }
        return $res;
    }
    
    public function isBrowserAnApp()
    {
        $checkout = Mage::getSingleton('checkout/session');
        if ($checkout->getAppBrowseFlag()) {
            return true;
        } else {
            return false;
        }
    }
}
