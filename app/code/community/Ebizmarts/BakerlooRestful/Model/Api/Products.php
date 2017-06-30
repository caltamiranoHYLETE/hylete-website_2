<?php

class Ebizmarts_BakerlooRestful_Model_Api_Products extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    private $_useOR = false;

    private $_productCollection = null;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pos_api_product';

    protected $_model = "catalog/product";

    const IMAGES_CONF_PATH = 'default/bakerloorestful/product/imagesizes';

    public function getPageSize() {

        $limit = intval($this->_getQueryParameter('limit'));
        if((!is_null($this->getFilterByName('category_id')) or $this->isOnlineSearch()) and $limit)
            return $limit;

        return parent::getSafePageSize();
    }

    /**
     * Applying array of filters to collection
     *
     * @param array $filters
     * @param bool $useOR
     */
    public function _applyFilters($filters, $useOR = false) {
        /**
         * This is just for online catalog; we remove the filter and apply the top level category
         * filter to the collection and Magento does the rest.
         *
         * In older versions we asked our API consumers to send all the CategoryIds -parent and childs- when
         * the parent category is_anchor, with this new change we just need the top level category.
         * This change solves the sort by position issue as well.
         */
        $filterByName = $this->getFilterByName('category_id');
        $filterPosition = $this->getFilterByName('category_id', null, true);
        if(!is_null($filterPosition))
            unset($filters[$filterPosition]);

        $filterByNameStock = $this->getFilterByName('is_in_stock');
        $filterPositionStock = $this->getFilterByName('is_in_stock', null, true);
        if(!is_null($filterPositionStock))
            unset($filters[$filterPositionStock]);

        parent::_applyFilters($filters, $this->_useOR);

        if(!is_null($filterByName)) {
            $categoryId = $this->returnFirstValueForFilter(array($filterByName), 'category_id');
            $category = Mage::getModel('catalog/category')->load($categoryId);

            if($category->getId())
                $this->_sortByPosition($this->_productCollection, $category);
        }

        if(!is_null($filterByNameStock)) {

            $stockFilters = $this->explodeFilter($filterByNameStock);

            if(array_key_exists(2, $stockFilters) and !empty($stockFilters[2])) {
                $isInStock = implode(',', $stockFilters[2]);

                $this->_productCollection
                    ->getSelect()
                    ->where('cisi.is_in_stock in ('.$isInStock.') OR cisi.manage_stock = 0', null, Varien_Db_Select::TYPE_CONDITION);

//                $this->_productCollection
//                    ->getSelect()
//                    ->orWhere('cisi.manage_stock=0', null, Varien_Db_Select::TYPE_CONDITION);
            }

        }

    }

    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get() {

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        $search = $this->_getQueryParameter('search');
        if(!is_null($search)) {

            $config = (string)Mage::helper('bakerloo_restful')->config('catalog/product_code', $this->getStoreId());
            $searchAttributes = Mage::helper('bakerloo_restful')->getBarcodeConfig($config);

            foreach($searchAttributes as $attrCode) {
                if(!empty($attrCode)) //default empty attr is sometimes selected in config
                    $this->_getCollection()->joinAttribute($attrCode, 'catalog_product/' . $attrCode, 'entity_id', null, 'left');
            }

            array_push($searchAttributes, 'sku');
            array_push($searchAttributes, 'name');
            array_unshift($searchAttributes, 'entity_id');

            $searchFilters = $this->assembleFiltersForSearch($search, $searchAttributes);

            if( array_key_exists('filters', $this->parameters) )
                $this->parameters['filters'] = array_merge($searchFilters, $this->parameters['filters']);
            else
                $this->parameters['filters'] = $searchFilters;


            $this->_getCollection()->addFieldToFilter('status', array('eq' => 1));
        }

        if($this->isOnlineSearch() or !is_null($search))
            $this->_useOR = true;

        return parent::get();
    }

    private function isOnlineSearch() {
        return !is_null($this->_getQueryParameter('online_search'));
    }

    /**
     * Use since from external table instead of catalog_product table.
     *
     * @param $collection
     * @param $page
     * @param null $since
     * @return $this
     */
    public function _beforePaginateCollection($collection, $page, $since = null) {

        if("catalog/product" == $this->_model) {
            return parent::_beforePaginateCollection($collection, $page, $since);
        }

        if(!$this->_productCollection)
            $this->_productCollection = $collection;

        $this->_productCollection->addFieldToFilter('store_id',
                array(
                    array('eq'   => $this->getStoreId()),
                    array('null' => true),
                ));

        return $this;
    }

    protected function _getIndexId() {
        if($this->_model == "bakerloo_restful/catalogtrash")
            return 'product_id';

        return parent::_getIndexId();
    }

    /**
     * @param $search
     * @param array $attributes
     * @return array
     */
    public function assembleFiltersForSearch($search, array $attributes) {

        $ret = array();

        if(!empty($search) and !empty($attributes)) {

            $search = filter_var($search, FILTER_SANITIZE_STRING);

            $attributesCount = count($attributes);

            for($i=0; $i < $attributesCount; $i++) {
                if($attributes[$i] == 'entity_id') {
                    if(is_numeric($search))
                        array_push($ret, $attributes[$i] . ',eq,' . $search);
                }
                else
                    array_push($ret, $attributes[$i] . ',like,%' . $search . '%');
            }

        }

        return $ret;

    }

    protected function _getCollection() {

        if("catalog/product" != $this->_model) {
            return parent::_getCollection();
        }

        if(is_null($this->_productCollection))
            $this->_productCollection = Mage::getModel($this->_model)->getCollection();
        else
            return $this->_productCollection;

        $this->_productCollection
                ->addAttributeToSelect('*')
                ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner')
                ->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        $store = $this->getStore();
        if($store->getId())
            $this->_productCollection->addStoreFilter($store);

        if(!is_null($this->getFilterByName('is_in_stock'))) {
            $this->_productCollection->getSelect()
                ->joinLeft(
                array('cisi' => $this->_productCollection->getTable('cataloginventory/stock_item')),
                'cisi.stock_id = 1 AND cisi.product_id = e.entity_id',
                array()
            );
        }

        return $this->_productCollection;
    }

    protected function _sortByPosition($collection, $category) {
        $collection->addCategoryFilter($category);
        $collection->addAttributeToSort('position');
    }

    public function _createDataObject($id = null, $data = null) {

        if(is_object($data) and ($data instanceof Ebizmarts_BakerlooRestful_Model_Catalogtrash)) {
            return $data->getData();
        }

        $since = $this->_getQueryParameter('since');

        Varien_Profiler::start('POS::' . __METHOD__);

        $result  = array();

        $product = Mage::getModel($this->_model)->setStoreId($this->getStoreId())->load($id);

        /**
         * @TODO: Performance improvement to be tested.
         * if(is_null($data))
            $product = Mage::getModel($this->_model)->setStoreId($this->getStoreId())->load($id);
        else
            $product = $data;
        */

        if($product->getId()) {

            //If data is null, no need to go to DB to fetch images
            if(is_null($data)) {
                $gallery = $product->getMediaGalleryImages();
            }
            else {
                $gallery = Mage::getModel('catalog/product')
                            ->setStoreId($this->getStoreId())
                            ->load($product->getId())
                            ->getMediaGalleryImages();
            }

            //Main image, some customers only have one image and use it as "exclude"
            if (($product->getImage() != 'no_selection') and $product->getImage()) {
                $mainImage = array(
                                   'file'     => $product->getImage(),
                                   'position' => 0,//@ToDo
                                   'label'    => $product->getData('image_label'),
                                   'url'      => Mage::getSingleton('catalog/product_media_config')->getMediaUrl($product->getImage()),
                );

                $gallery->addItem(new Varien_Object($mainImage));
            }

            //Images
            $galleryUrls = array();
            if(!is_null($gallery) and $gallery->getSize()) {

                $thumbnail  = $product->getThumbnail();
                $smallImage = $product->getSmallImage();
                $baseImage  = $product->getImage();

                foreach ($gallery as $_image) {

                    //If image is disabled do not use
                    if((int)$_image->getDisabled() === 1) {
                        continue;
                    }

                    $_imageData = array();

                    $_imageData['position'] = (int)$_image->getPosition();

                    $_imageData['is_base']      = ($_image->getFile() == $baseImage ? 1 : 0);
                    $_imageData['is_small']     = ($_image->getFile() == $smallImage ? 1 : 0);
                    $_imageData['is_thumbnail'] = ($_image->getFile() == $thumbnail ? 1 : 0);

                    $_imageData['large']    = $_image->getUrl();
                    $_imageData['label']    = $_image->getLabel();

                    $imagesConf = Mage::getConfig()->getNode(self::IMAGES_CONF_PATH)->asArray();

                    foreach ($imagesConf as $code => $size) {

                        $_size  = explode('x', $size);
                        $width  = $_size[0];
                        $height = $_size[1];

                        $thumb = Mage::helper('bakerloo_restful')->getResizedImageUrl($product->getId(), $this->getStoreId(), $_image->getFile(), (int)$width, (int)$height);
                        $_imageData[$code] = (string)$thumb;
                    }

                    $galleryUrls[]   = $_imageData;

                    $_imageData = null;
                    $thumb      = null;

                    unset($_imageData);
                    unset($thumb);
                }
            }

            $result['images']                  = $galleryUrls;
            $result['description']             = (string) $product->getDescription();
            $result['short_description']       = (string) $product->getShortDescription();
            $result['use_description']         = (string) Mage::helper('bakerloo_restful')->config('catalog/description', $this->getStoreId());
            $result['last_update']             = $product->getUpdatedAt();
            $result['name']                    = $product->getName();
            $result['price']                   = $this->_getProductPrice($product);
            $result['product_id']              = (int) $product->getId();
            $result['sku']                     = $product->getSku();
            $result['barcode']                 = (string) Mage::helper('bakerloo_restful')->getProductBarcode($product->getId(), $this->getStoreId());
            $result['special_price']           = (float) $product->getSpecialPrice();
            $result['special_price_from_date'] = (string) $product->getSpecialFromDate();
            $result['special_price_to_date']   = (string) $product->getSpecialToDate();
            $result['store_id']                = (int) $product->getStoreId();
            $result['tax_class']               = (int) $product->getTaxClassId();
            $result['visibility']              = (int) $product->getVisibility(); //1- Not visible individually; 2- Catalog; 3- Search; 4-Catalog, Search
            $result['status']                  = (int) $product->getStatus(); //1- Enabled; 2- Disabled
            $result['type']                    = $product->getTypeId();
            $result['categories']              = $this->_getCategories($product);
            $result['tier_pricing']            = $this->_getTierPrice($product);
            $result['group_pricing']           = $this->_getGroupPrice($product);

            //Adding cross sell, up sell and related products
            $this->_addRelatedProductsData($product, $result);

            //configurable details
            $associatedProductsArray = array();
            $attributeOptions        = array();

            if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {

                $attributeConfig = $this->getAttributesConfig($product);

                //attributes
                $attributesData = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                foreach ($attributesData as $productAttribute) {
                    $attributeValues = array();
                    foreach ($productAttribute['values'] as $attribute) {
                        $attributeValues[] = array(
                                                   'label'         => (string)$attribute['label'],
                                                   'value_index'   => (int)$attribute['value_index'],
                                                   'pricing_value' => (float)$attribute['pricing_value'],
                                                   'is_percent'    => (int)$attribute['is_percent']
                                                  );
                    }

                    //Attribute config for dependencies
                    $config = array();
                    if(isset($attributeConfig[$productAttribute['attribute_code']]['options'])) {
                        $config = $attributeConfig[$productAttribute['attribute_code']]['options'];
                    }

                    if( !empty($config) ) { //Avoid attributes without options (Configurables without children)
                        $attributeOptions[] = array(
                                                    'attribute_code'  => $productAttribute['attribute_code'],
                                                    'attribute_label' => $productAttribute['label'],
                                                    'values'          => $attributeValues,
                                                    'config'          => $config
                        );
                    }
                }

                unset($attributeConfig);

            }
            else if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {

                $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);

                foreach($associatedProducts as $_child) {
                    $associatedProductsArray []= (int)$_child->getId();
                }

            }
            else if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $product->getTypeInstance(true)->setStoreFilter($this->getStoreId(), $product);

                $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);

                $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );

                $optionsArray = $optionCollection->appendSelections($selectionCollection, false, false);

                $bundleAttributeOptions = array();
                $selected               = array();

                foreach ($optionsArray as $_option) {
                    if (!$_option->getSelections()) {
                        continue;
                    }

                    $option = array (
                        'id'         => (int)$_option->getOptionId(),
                        'title'      => $_option->getTitle(),
                        'type'       => (string)$_option->getType(),
                        'required'   => (int)$_option->getRequired(),
                        'position'   => (int)$_option->getPosition(),
                        'selections' => array()
                    );

                    $selectionCount = count($_option->getSelections());

                    foreach ($_option->getSelections() as $_selection) {
                        $_qty = !($_selection->getSelectionQty()*1)?'1':$_selection->getSelectionQty()*1;
                        $selection = array (
                            'id'           => (int)$_selection->getSelectionId(),
                            'qty'          => ($_qty * 1),
                            'canChangeQty' => (int)$_selection->getSelectionCanChangeQty(),
                            'price'        => Mage::helper('core')->currency($_selection->getFinalPrice(), false, false),
                            'priceValue'   => Mage::helper('core')->currency($_selection->getSelectionPriceValue(), false, false),
                            'priceType'    => $_selection->getSelectionPriceType(),
                            'tierPrice'    => $_selection->getTierPrice(),
                            'name'         => $_selection->getName(),
                            'product_id'   => (int)$_selection->getId(),
                            'position'     => (int)$_selection->getPosition(),
                            'is_default'   => (int)$_selection->getIsDefault(),
                        );
                        /*$responseObject = new Varien_Object();
                        $args = array('response_object'=>$responseObject, 'selection'=>$_selection);
                        Mage::dispatchEvent('bundle_product_view_config', $args);
                        if (is_array($responseObject->getAdditionalOptions())) {
                            foreach ($responseObject->getAdditionalOptions() as $o=>$v) {
                                $selection[$o] = $v;
                            }
                        }*/
                        $option['selections'][] = $selection;

                        if (($_selection->getIsDefault() || ($selectionCount == 1 && $_option->getRequired())) && $_selection->isSalable())
                            $selected[$_option->getId()][] = $_selection->getSelectionId();

                    }
                    $bundleAttributeOptions[] = $option;
                }

                $result['bundle_option'] = $bundleAttributeOptions;
                $price_type = 'dynamic';
                if($product->getPriceType()){
                    $price_type = 'fixed';
                }
                $result['price_type'] = $price_type;

            }

            $result['attributes'] = $attributeOptions;
            $result['children']   = $associatedProductsArray;

            //Custom Options
            $customOptions = array();
            $options       = $product->getOptions();

            if(count($options)) {
                $customOptions = $this->_getProductCustomOptions($product, $options);
            }

            $result['options'] = $customOptions;

            if(Mage::helper('bakerloo_gifting')->productIsGiftcard($product)) {
                $giftCardOptions = Mage::helper('bakerloo_gifting')->getGiftcardOptions($product);

                if(is_array($giftCardOptions) and !empty($giftCardOptions))
                    $result['gift_card_options'] = $giftCardOptions;
            }

            Varien_Profiler::start('POS::' . __METHOD__ . '::additional_attributes');
            //Additional attributes
            $additionalAttributesConfig = (string) Mage::helper('bakerloo_restful')->config('catalog/additional_attributes', $this->getStoreId());
            if(!empty($additionalAttributesConfig)) {
                $attributes = explode(',', $additionalAttributesConfig);

                if(is_array($attributes) && !empty($attributes)) {
                    $additionalAttributeData = array();

                    foreach($attributes as $_attributeCode) {

                        if(!strlen($_attributeCode)) {
                            continue;
                        }

                        $_attributeValue = $product->getAttributeText($_attributeCode);
                        if(!$_attributeValue) {

                            $method = 'get' . uc_words($_attributeCode, '');
                            if( is_callable(array($product, $method)) ) {
                                $_attributeValue = $product->$method();
                            }

                            if(!$_attributeValue) {
                                $_attributeValue = $product->getData($_attributeCode);

                                if(!$_attributeValue)
                                    $_attributeValue = '';
                            }

                        }

                        //Array values not supported on the app.
                        if( is_array($_attributeValue) )
                            continue;

                        $_attr = $product->getResource()->getAttribute($_attributeCode);

                        if($_attr->getFrontendInput() == 'boolean')
                            $_attributeValue = $_attributeValue == 'Yes' ? "1" : "0";

                        $additionalAttributeData []= array(
                            'name'  => $_attributeCode,
                            'label' => $_attr->getFrontendLabel(),
                            'type'  => $_attr->getFrontendInput(),
                            'value' => $_attributeValue,
                        );

                    }

                    $result['additional_attributes'] = $additionalAttributeData;
                }
            }
            Varien_Profiler::stop('POS::' . __METHOD__ . '::additional_attributes');

            if($since != -1)
                $result['inventory'] = Mage::getModel('bakerloo_restful/api_inventory')->setStoreId($this->getStoreId())->_createDataObject($product->getId());

        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $this->returnDataObject($result);

    }

    private function _getProductCustomOptions(Mage_Catalog_Model_Product $product, $options) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $customOptions = array();

        $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
        foreach ($options as $option) {
            /* @var $option Mage_Catalog_Model_Product_Option */

            $value = array();

            $value['option_id']  = (int)$option->getOptionId();
            $value['title']      = (string)$option->getTitle();
            $value['type']       = (string)$option->getType();
            $value['is_require'] = (int)$option->getIsRequire();
            $value['sort_order'] = (int)$option->getSortOrder();

            if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {

                $i = 0;
                $itemCount = 0;
                foreach ($option->getValues() as $_value) {
                    /* @var $_value Mage_Catalog_Model_Product_Option_Value */
                    $value['option_values'][$i] = array(
                        'option_type_id' => (int)$_value->getOptionTypeId(),
                        'title'          => (string)$_value->getTitle(),
                        'price'          => (float)$this->getPriceValue($_value->getPrice(), $_value->getPriceType()),
                        'price_type'     => (string)$_value->getPriceType(),
                        'sku'            => (string)$_value->getSku(),
                        'sort_order'     => (int)$_value->getSortOrder(),
                    );

                    $i++;
                }
            }
            else {
                $value['price']          = (float)$this->getPriceValue($option->getPrice(), $option->getPriceType());
                $value['price_type']     = (string)$option->getPriceType();
                $value['sku']            = (string)$option->getSku();
                $value['max_characters'] = (int)$option->getMaxCharacters();

            }

            $customOptions[] = $value;

        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $customOptions;

    }

    public function getAttributesConfig($_product) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $attributes = array();
        $options    = array();

        $products    = array();
        $allProducts = $_product->getTypeInstance(true)
            ->getUsedProducts(null, $_product);

        foreach ($allProducts as $product) {
            //if ($product->isSaleable()) {
                $products[] = $product;
            //}
        }

        $allowAttributes = $_product->getTypeInstance(true)
            ->getConfigurableAttributes($_product);

        foreach ($products as $product) {
            $productId  = $product->getId();

            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();

                if(!is_object($productAttribute)) {
                    Mage::throwException("Attribute error: " . $attribute->getLabel() . '-' . $attribute->getProductId());
                }

                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttribute->getId()])) {
                    $options[$productAttribute->getId()] = array();
                }

                if (!isset($options[$productAttribute->getId()][$attributeValue])) {
                    $options[$productAttribute->getId()][$attributeValue] = array();
                }
                $options[$productAttribute->getId()][$attributeValue][] = (int)$productId;
            }
        }

        foreach ($allowAttributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();

            if(!is_object($productAttribute)) {
                Mage::throwException("Attribute error: " . $attribute->getLabel() . '-' . $attribute->getProductId());
            }

            $attributeId = $productAttribute->getId();
            $info = array(
                'id'        => (int)$productAttribute->getId(),
                'attribute_code' => $productAttribute->getAttributeCode(),
                'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if(!isset($options[$attributeId][$value['value_index']])) {
                        continue;
                    }

                    $info['options'][] = array(
                        'value_index'   => (int)$value['value_index'],
                        'products'      => isset($options[$attributeId][$value['value_index']]) ? $options[$attributeId][$value['value_index']] : array(),
                    );
                }
            }
            $attributes[$productAttribute->getAttributeCode()] = $info;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $attributes;
    }

    public function getPriceValue($value) {
        return number_format($value, 2, null, '');
    }

    private function _addRelatedProductsData(Mage_Catalog_Model_Product $product, array &$result) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $related = array(
                         'cross_sell' => 'getCrossSellProducts',
                         'related'    => 'getRelatedProducts',
                         'up_sell'    => 'getUpSellProducts'
                        );

        foreach ($related as $key => $method) {

            $products = array();

            $related = $product->{$method}();
            foreach ($related as $prod) {
                $products[] = array('product_id' => (int)$prod->getId());
            }

            $result [$key]= $products;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

    }

    private function _getTierPrice($product) {
        return $this->_priceStruct($product, 'tier_price');
    }

    private function _getGroupPrice($product) {
        if(version_compare(Mage::getVersion(), '1.7.0.0', '<')) {
            return array();
        }

        return $this->_priceStruct($product, 'group_price');
    }

    private function _priceStruct($product, $dataType) {
        $dataPrice = array();

        $dataPriceData = $product->getData($dataType);

        if(is_array($dataPriceData) && !empty($dataPriceData)) {
            foreach ($dataPriceData as $_tprice) {
                $_tprice['price_id']          = (int)$_tprice['price_id'];
                $_tprice['website_id']        = (int)$_tprice['website_id'];
                $_tprice['all_groups']        = (int)$_tprice['all_groups'];
                $_tprice['customer_group_id'] = (int)$_tprice['cust_group'];

                unset($_tprice['cust_group']);

                if(isset($_tprice['price_qty'])) {
                    $_tprice['price_qty'] = (float)$_tprice['price_qty'];
                }

                $_tprice['price']             = (float)$_tprice['price'];
                $_tprice['website_price']     = (float)$_tprice['website_price'];

                $dataPrice [] = $_tprice;
            }
        }

        return $dataPrice;
    }

    /**
     * Retrieve the last sold item
     *
     * @return array
     * @throws Exception
     */
    public function getLastSold(){
        if(!$this->getStoreId())
            Mage::throwException('Please provide a store ID.');

        $model = Mage::getModel('bakerloo_restful/api_lastSoldProducts');
        $latest = $model->_getAllItems();
        return $latest;
    }

    public function getBestseller(){
        if(!$this->getStoreId())
            Mage::throwException('Please provide a store ID.');

        $model = Mage::getModel('bakerloo_restful/api_bestSellingProducts');
        $bestSellers = $model->_getAllItems();
        return $bestSellers;
    }

    /**
     * Retrieve DELETED or removed from website products.
     *
     * @return Collection data.
     */
    public function trashed() {
        $this->checkGetPermissions();

        $this->_model = 'bakerloo_restful/catalogtrash';
        $this->_iterator = false;

        //get page
        $page = $this->_getQueryParameter('page');
        if(!$page) {
            $page = 1;
        }

        $myFilters = array();
        $since     = $this->_getQueryParameter('since');
        if(!is_null($since)) {
            array_push($myFilters, "updated_at,gt,{$since}");
        }

        $filters = $this->_getQueryParameter('filters');
        if(is_null($filters)) {
            $filters = $myFilters;
        }
        else {
            $filters = array_merge($filters, $myFilters);
        }

        return $this->_getAllItems($page, $filters);

    }

    /**
     * Retrieve product price correctly from real object.
     *
     * @param $product
     * @return float
     */
    private function _getProductPrice($product) {

        $price = $product->getPrice();

        //Avoid price tricks from this module, just give me the configurable price.
        if($product instanceof OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product) {
            $_product = new Mage_Catalog_Model_Product();
            $_product->setPriceCalculation(false);
            $_product->load($product->getId());

            $price = $_product->getPrice();
        }

        return (float)$price;
    }

    /**
     * Return categories with product position data.
     *
     * @param $product
     * @return array
     */
    public function _getCategories($product) {

        Varien_Profiler::start('POS::' . __METHOD__);

        $cats = $product->getCategoryIds();

        $categories = array();

        for($i=0;$i<count($cats);$i++) {
            $categoryId = $cats[$i];

            $myCategoryData = array(
              'category_id' => $categoryId,
              'position'    => 0,
            );

            $positions = $this->categoryProductPositions($categoryId);
            if(!empty($positions)) {

                $exists = array_key_exists(((int)$product->getId()), $positions);
                if($exists) {
                    $myCategoryData['position'] = (int)$positions[$product->getId()];
                }

            }

            $categories []= $myCategoryData;
        }

        Varien_Profiler::stop('POS::' . __METHOD__);

        return $categories;
    }

    private function categoryProductPositions($categoryId) {
        $key = 'pos_categories_pos_' . $categoryId;

        $positionsRegistry = Mage::registry($key);
        if(is_null($positionsRegistry)) {
            $category  = new Varien_Object( array('id' => $categoryId) );
            $positions = Mage::getResourceModel('catalog/category')->getProductsPosition($category);
            Mage::register($key, $positions);
        }

        return Mage::registry($key);
    }

}
