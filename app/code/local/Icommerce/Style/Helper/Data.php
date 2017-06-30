<?php
class Icommerce_Style_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $_isSaleableCheck = false;

    const TYPE_STYLE = 'style';
    const XPATH_USE_DEFAULT_STYLE_ID = 'icommerce_style/settings/use_default_style_id';

    public function getProductType() {
        return self::TYPE_STYLE;
    }
    public function isAssociatedWithStyleProduct($product) {
        if($product->getTypeId() == 'configurable') {
            $productId = Icommerce_Db::getDbSingleton('SELECT CPL.product_id FROM catalog_product_link AS CPL
                                                        INNER JOIN catalog_product_entity AS CPE ON CPL.product_id = CPE.entity_id
                                                        WHERE CPL.linked_product_id = ?
                                                        AND CPL.link_type_id = 1
                                                        AND CPE.type_id = "style"', $product->getId());
            $styleProduct = Mage::getModel('catalog/product')->load($productId);

            if( $styleProduct->getTypeId() != 'style' ){
                return false;
            }
            return $styleProduct;

        }

        return false;
    }
    public function getStyleProductItems($product, $extraAttributes = null ) {
        $result = array();

        if($product Instanceof Mage_Catalog_Model_Product && $product->getTypeId() == 'style') {
            $helper = Mage::helper("style/jsonStyle");

            $attributes = array('name', 'url_path', 'thumbnail');
            if (is_array($extraAttributes)) {
                $attributes = array_merge($attributes, $extraAttributes);
            }
            
            $this->_linkCollection = $product->getRelatedProductCollection()
                ->addAttributeToSelect($attributes)
                ->setPositionOrder();

            $result = array();
            foreach($this->_linkCollection as $prod) {
                $realProduct = Mage::getModel("catalog/product")->load($prod->getId());
                if ($realProduct !== false && $realProduct->getId() > 0) {
                    $realProduct->setOption($helper->getSimpleValues($prod));
                    $realProduct->setCustomOptions($helper->getAttributeIdLookupPerProduct(true, $prod->getId()));
                    /*$result[] = array('name' => $prod->getName(),
                                      'sku' => $prod->getSku(),
                                      'is_salable' => $prod->getIsSalable(),
                                      'thumbnail' => $prod->getThumbnail(),
                                      'option' => $helper->getSimpleValues($prod),
                                      'url_path' => $prod->getUrlPath(),
                                      'id' => $prod->getId(),
                                      'eton_body' => $prod->getEtonBody(),
                                      'custom_options' => $helper->getAttributeIdLookupPerProduct(true, $prod->getId()),
                                      'product' => $prod
                                    );*/
                    $result[] = $realProduct;
                }
            }
        }
        return $result;
    }

    public function getStyleProductItemsPrice($product, $websiteId, $userGroupId) {
        $productCollection = $product->getRelatedProductCollection();
        $children = $productCollection->getItems();
        if(empty($children)) {
            return 0;
        }
        $select = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(array('price' => 'catalog_product_index_price_idx'), array('price'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $userGroupId)
            ->where('entity_id in (?)', array_keys($children));
        $result = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($select);
        $totalPrice = 0;
        foreach($result as $row) {
            $totalPrice += $row['price'];
        }
        return $totalPrice;
    }

    /**
     * Define can we use "default_style_id" product attribute
     *
     * @return bool
     */
    public function canUseDefaultStyleIdAttribute()
    {
        return Mage::getStoreConfigFlag(self::XPATH_USE_DEFAULT_STYLE_ID);
    }
}
