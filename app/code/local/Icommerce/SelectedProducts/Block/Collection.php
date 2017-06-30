<?php

class Icommerce_SelectedProducts_Block_Collection extends Mage_Catalog_Block_Product_Abstract
{
    protected $_old_magento = false;
    protected $_disallowed_types = null;

    function __construct()
    {
        parent::__construct();
        $this->setTemplate("catalog/product/list/ic_selected.phtml");

        if (Icommerce_Default::getMagentoVersion() < 1400 || (Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') && Icommerce_Default::getMagentoVersion() >= 1700 && Icommerce_Default::getMagentoVersion() < 1800)) {
            $this->_old_magento = true;
        }
    }

    public function shouldShowBuyButton()
    {
        return !$this->hasShowBuyButton() || (bool)$this->getShowBuyButton();
    }

    public function getCollectionTitle()
    {
        return $this->getDataSetDefault('title', 'Featured Products');
    }

    public function setSkipCurrentProduct($bool)
    {
        if ($this->getData('skip_current_product') xor $bool) {
            $this->setData('skip_current_product', $bool);
            if ($bool) {
                $this->setNumGet($this->getNumGet() + 1);
            } else {
                $this->setNumGet($this->getNumGet() - 1);
            }
        }

        return $this;
    }

    // Prepare collection, arguments passed via setXYZ [or from XML] have precedence
    public function getCollection($attrib = "all", $num_get = 3, $desc = true, $attribs = array("entity_id", "sku", "image", "name"), $attributesToFilter = array(), $instock_only = 0, $xtra_options = array())
    {
        $seconds_to_expire = null;

        if (($tmp = $this->getAttribSort())) $attrib = $tmp;
        if (($tmp = $this->getNumGet())) $num_get = $tmp;
        if (($tmp = $this->getDescending())) $desc = $tmp;
        if (($this->getDescending() == '0')) $desc = false; // From widget it will either be 0 or 1, never missing

        if (($this->getDescending() === 'desc')) $desc = true; // RJB-925, descending do not work with 0 or 1, changed to asc/desc. Now it works. Preserve the previous row just in case.
        if (($this->getDescending() === 'asc')) $desc = false; // RJB-925, descending do not work with 0 or 1, changed to asc/desc. Now it works. Preserve the previous row just in case.

        if (($tmp = $this->getAttribsSelect())) $attribs = $tmp;
        if ($this->hasData('instock_only')) {
            $instock_only = (bool )$this->getData('instock_only') ? 1 : 0;
        }
        if ($tmp = $this->getData('xtra_options')) {
            $xtra_options = (array )$tmp;
        }
        if (!empty($xtra_options['custom_cache_age'])) {
            $seconds_to_expire = (int )$xtra_options['custom_cache_age'];
        }

        // 1. first try to get cached results
        $cache_id = $this->getCacheId($attrib, $num_get, $desc, $this->getRealCatId(), $attribs, $attributesToFilter, $this->getExtraCacheId($attrib), $instock_only, $xtra_options);

        if (!($coll = $this->getCachedCollection($cache_id))) {
            $coll = $this->getRealCollection($attrib, $num_get, $desc, $attribs, $attributesToFilter, $instock_only, $xtra_options);
            $this->cacheCollection($cache_id, $coll);
        }

        return $coll;
    }

    public function getExtraCacheId($attrib)
    {
        // Add ID that is outside of input query params ?
        if ($attrib == 'viewed_this_viewed_that') {
            return $this->getLastViewedProductId();
        } elseif($attrib == 'cart_related') {
            $pids = $this->getCartItemIds();
            return implode('-', $pids);
        }

        return '';
    }

    public function getCacheId($attribute = "all", $num_get = 3, $desc = true, $categoryId, $attributes = array("entity_id", "sku", "image", "name"), $attributesToFilter = array(), $extraId = '', $onlyInStock = 0, $extraOptions = array() )
    {
        $cacheKeyParts = array(
            'attribute'         => $attribute,
            'num_get'           => $num_get,
            'desc'              => $desc,
            'cat_id'            => $categoryId,
            'attribs'           => $attributes,
            'attributesToFilter'=> $attributesToFilter,
            'extra_id'          => $extraId,
            'instock'           => $onlyInStock,
            'xtra_options'      => $extraOptions,
        );

        if ($attribute == 'manual_products') {
            $cacheKeyParts['manual_product_ids'] = Mage::helper('selectedproducts')->sanitizeManualProductIds($this->getPageContent());
        }


        if (!Mage::getStoreConfig('selectedproducts/settings/has_global_products')) {
            $cacheKeyParts = array_merge(array('store_id' => Icommerce_Default::getStoreId()), $cacheKeyParts);
        }

        return Zend_Json::encode($cacheKeyParts);
    }

    // get cached collection or false;
    public static function getCachedCollection($key)
    {
        return Icommerce_SelectedProducts_Model_Cache::getCollection($key);
    }

    public static function cacheCollection($key, $coll)
    {
        return Icommerce_SelectedProducts_Model_Cache::cacheCollection($key, $coll);
    }

    // get real, uncached collection
    public function getRealCollection($attribute = "all", $num_get = 3, $desc = true, $attribs = array("entity_id", "sku", "image", "name"), $attributesToFilter = array(), $instock_only = 0, $xtra_options = array())
    {
        if ($attribute == "new") {
            $attribute = "entity_id";
            $desc = true;
        } else if ($attribute == "old") {
            $attribute = "entity_id";
            $desc = false;
        }

        $desc = $desc == 1 ? 'desc' : 'asc';
        $productIds = null;

        /** @var $coll Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $coll = null;
        if ($attribute == "viewed_this_viewed_that") {
            $pid_last = $this->getLastViewedProductId();
            $productIds = array( /* calculation of products viewed with this one */ );
        }
        elseif ($attribute == "views_count") {
            $coll = $this->getViewsCountCollection();
        }
        elseif ($attribute == "ordered_qty") {
            $coll = $this->getOrderedQtyCollection($desc);
        }
        elseif ($attribute == "random") {
            $coll = Mage::getResourceModel('catalog/product_collection');
            //Mage::getModel('catalog/layer')->prepareProductCollection($coll);
            $coll->getSelect()->order('rand()');
        }
        // Vaimo-AR
        elseif ($attribute == "news") {
            $todayStartOfDayDate  = Mage::app()->getLocale()->date()
                    ->setTime('00:00:00')
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $todayEndOfDayDate  = Mage::app()->getLocale()->date()
                    ->setTime('23:59:59')
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $coll = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToFilter('news_from_date', array('or'=> array(
                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter('news_to_date', array('or'=> array(
                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter(
                    array(
                        array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                        array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
                )
                ->addAttributeToSort('entity_id', 'desc')
                //->addAttributeToSort('news_from_date', 'desc')
            ;
        }
        elseif ($attribute == "special") {
            $todayStartOfDayDate  = Mage::app()->getLocale()->date()
                    ->setTime('00:00:00')
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $todayEndOfDayDate  = Mage::app()->getLocale()->date()
                    ->setTime('23:59:59')
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $coll = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToFilter('special_from_date', array('or'=> array(
                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter('special_to_date', array('or'=> array(
                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter(
                    array(
                        array('attribute' => 'special_from_date', 'is'=>new Zend_Db_Expr('not null')),
                        array('attribute' => 'special_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
                )
                ->addAttributeToSort('special_from_date', 'desc')
            ;
        }
        /************/
        elseif (substr($attribute, 0, 6) == "review") {
            $productIds = $this->getReviewedIds($attribute, $desc, $num_get);
        }
        elseif ($attribute == "in_pricerule" || $attribute == "pricerule") {
            $productIds = $this->getPriceRuleIds($num_get);
        }
        elseif ($attribute == "suggested_related") {
            // This can be quite slow
            $orders = $this->prepareOrdersCollection();
            $suggestedProducts = $this->prepareSuggestedProducsCollection($orders);
            return $suggestedProducts;
        }
        elseif($attribute == "most_searched"){
             $productIds = $this->getMostSearched( $num_get );
        }
		elseif ($attribute == 'cart_related') {
			$productIds = $this->getCartRelatedIds($num_get);
		}
        elseif ($attribute == 'cat_index_position'){
            if (!empty($xtra_options['ignore_cat_idx'])) {
                $xtra_options['ignore_cat_idx'] = 0;
            }
            $coll = Mage::getResourceModel('catalog/product_collection');
            $coll->getSelect()->order("cat_index.position asc");
        }
        elseif($attribute == "manual_products"){
            $coll = Mage::getResourceModel('catalog/product_collection');
            $productIds = Mage::helper('selectedproducts')->sanitizeManualProductIds($this->getPageContent(), true);
            $this->setCategoryId(null);
        }
        else {
            // Get list ordered by other product eav attribute
            //$coll = Mage::getResourceModel('reports/product_collection');
            //$coll = Mage::getModel('catalog/product')->getCollection();
            $coll = Mage::getResourceModel('catalog/product_collection');
            if ($attribs !== "*") {
                $coll->addAttributeToSelect($attribute);
            }
            if (!empty($attribute) && $attribute !== "all") {
                $coll->addAttributeToSort($attribute, $desc);
            }

            if ($attribute === 'price') { // RJB-925
                $coll->addAttributeToFilter($attribute, array('gt' => 0));
            }
            if (Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', 'activation_date') && $attribute === 'activation_date') { // RJB-925
                $coll->addAttributeToFilter($attribute,array(
                    'notnull' => true,
                    'neq'=>''
                ));
            }
        }

        $cat = null;
        $cat_id = $this->getCategoryId();
        if ($cat_id == "0" && $productIds === null && empty($xtra_options['ignore_cat_idx'])) {
            // fallback to root category to apply a proper category filter later
            $cat_id = Mage::app()->getStore($coll->getStoreId())->getRootCategoryId();
        }
        if ($cat_id) {
            if ($cat_id instanceof Varien_Object) {
                $cat = $cat_id;
            } else {
                // If used as widget the catalog_category_widget_chooser adds this funny category/ string
                $cat_id = str_replace('category/', '', $cat_id);
                if( !Icommerce_Utils::isInteger($cat_id) ) {
                    if ($cat_id == 'current') {
                        $cat = $this->getCurrentCategoryId();
                    } elseif ($cat_id == 'parent') {
                        if( $cur = $this->getCurrentCategoryId() ){
                            $cat_id = Icommerce_Eav::getValue( $cur, "parent_id", "catalog_category" );
                        }
                    } else {
                        // Try Url Key
                        $cat = Icommerce_Category::loadByUrlKey($cat_id);
                    }
                }
            }
            if( !$cat && $cat_id ){
                if (empty($xtra_options['ignore_cat_idx'])) {
                    $cat = Mage::getModel("catalog/category")->load($cat_id);
                } else {
                    $cat = new Varien_Object();
                    $cat->setId((int )$cat_id);
                }

            }
            if( $cat ) $this->setCategoryId($cat);
        }

        if ($cat) {
            //$this->setCategoryId($cat);
            if (empty($xtra_options['ignore_cat_idx'])) {
                $coll->addCategoryFilter($cat);
            } else {
                // use without index table
                $conditions = array(
                    'cat_pro.product_id=e.entity_id',
                    $coll->getConnection()->quoteInto('cat_pro.category_id=?', $cat->getId())
                );
                // include subcategories if selector in admin is set to yes
                if (Mage::getStoreConfig('selectedproducts/settings/include_children_categories')) {
                    $_category = Mage::getModel('catalog/category')->load($cat->getId());
                    $all_child_categories = $_category->getAllChildren(); // get all child categories ids

                    $conditions = array(
                        'cat_pro.product_id=e.entity_id',
                        sprintf('cat_pro.category_id IN(%s)', $all_child_categories)
                    );
                }

                $joinCond = join(' AND ', $conditions);

                $fromPart = $coll->getSelect()->getPart(Zend_Db_Select::FROM);
                if (isset($fromPart['cat_pro'])) {
                    $fromPart['cat_pro']['joinCondition'] = $joinCond;
                    $coll->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
                } else {
                    $coll->getSelect()->join(
                        array('cat_pro' => $coll->getTable('catalog/category_product')),
                        $joinCond,
                        array('cat_index_position2' => 'position')
                    );
                }

                $orderPart = $coll->getSelect()->getPart(Zend_Db_Select::ORDER);
                $order_changed = false;
                foreach ($orderPart as &$order) {
                    if ($order[0] == 'cat_index_position') {
                        $order_changed = true;
                        $order[0] = 'cat_pro.position';
                        if (isset($order[1]) && is_string($order[1])) {
                            // make sure that it's position ASC
                            $order[1] = 'asc';
                        }
                    }
                }
                if (!$order_changed && $attribute == 'position') {
                    $existingOrder = (array)$orderPart;
                    if (count($existingOrder) > 1) {
                        $existingOrder = array_reverse($existingOrder);
                    }
                    $existingOrder[] = array('cat_pro.position', 'asc');
                    if (count($existingOrder) > 1) {
                        $existingOrder = array_reverse($existingOrder);
                    }
                    $order_changed = true;
                    $orderPart = $existingOrder;
                }
                if ($order_changed) {
                    $coll->getSelect()->setPart(Zend_Db_Select::ORDER, $orderPart);
                }
            }
        }

        if ($productIds === null) {

            $coll->addAttributeToSelect($attribs);
            $coll->addUrlRewrite();
            // On ignoring category index, ignore also price-index table as sometimes index table is partly filled and incorrect data is returned.
            // price will be fetched later for specific product in template thru price-model using getPrice() call
            if (empty($xtra_options['ignore_cat_idx'])) {
                $coll->addFinalPrice();
            }
            $coll->addAttributeToFilter("status", 1)
                ->setPageSize($num_get)
            ;
            if (empty($xtra_options['ignore_cat_idx'])) {
                $coll->setVisibility(array(2, 4));
            } else {
                if (!$coll->isEnabledFlat()) {
                    $coll->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
                    $coll->getSelect()->where('IF(at_visibility.value_id > 0, at_visibility.value, at_visibility_default.value) in (?)', array(2, 4));
                } else {
                    $coll->getSelect()->where('`e`.`visibility` in (?)', array(2, 4));
                }
            }
            $coll->addStoreFilter(Mage::app()->getStore()->getId());

        } else {
            // Have array of product ids, load products
            $coll = Mage::getResourceModel("catalog/product_collection");
            $coll->addIdFilter($productIds);
            $coll->addAttributeToSelect($attribs);
            $coll->addUrlRewrite();
            $coll->addFinalPrice();
        }

        if(isset($productIds)){
            if($attribute == "manual_products" && count($productIds) > 0){
                $coll->getSelect()->order("FIELD(e.entity_id, " . implode(',', $productIds) . ")");
            }
        }

        if (count($attributesToFilter) > 0) {
            foreach ($attributesToFilter as $key => $value) {
                $coll->addAttributeToFilter($key, $value);
            }
        }

        $inc_instock_filter = Mage::getStoreConfig('selectedproducts/settings/instock_filter');
        if($instock_only == 1 || $inc_instock_filter){
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($coll);
        }

        $use_fallback= Mage::getStoreConfig('selectedproducts/settings/fallback_attribute_name');
        if($use_fallback != ""){
            $coll = $this->addFallbackProducts($coll, $attribs);
        }

        Mage::dispatchEvent('vaimo_selectedproducts_prepare_collection', array('block' => $this, 'products' => $coll));

        // Disabled products not allowed
        $coll->addFieldToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        return $coll->distinct(true);
    }


    protected function getLastViewedProductId(){
        $prod = Mage::registry( "current_product" );
        if( !$prod ){
            $prod = Mage::registry( "product" );
            if( !$prod ){
                return Mage::getSingleton('catalog/session')->getLastViewedProductId();
            }
        }
        return $prod->getId();
    }


    protected function addFallbackProducts($coll, $attribs){
        $fallback_number_of_products = Mage::getStoreConfig('selectedproducts/settings/fallback_number_of_products');
        $current_collection_products = $coll->count();
        if($current_collection_products<$fallback_number_of_products)
        {

            $number_of_products_to_get = $fallback_number_of_products-$current_collection_products;

            $fallback_attribute_name = Mage::getStoreConfig('selectedproducts/settings/fallback_attribute_name');
            if($fallback_attribute_name!="")
            {
                $fallback_collection = Mage::getResourceModel('catalog/product_collection')
                        ->addFieldToFilter($fallback_attribute_name, array('eq'=>'1'))
                        ->setPageSize($number_of_products_to_get)
                        ->setCurPage(1)
                        ->load();


                $merged_ids = array_merge($coll->getAllIds(), $fallback_collection->getLoadedIds());

                $coll = Mage::getResourceModel('catalog/product_collection')
                    ->addFieldToFilter('entity_id', $merged_ids)->addAttributeToSelect($attribs);
            }
        }

        return $coll;
    }

    protected function getViewsCountCollection()
    {
        $views_count_days = Mage::getStoreConfig('selectedproducts/settings/views_count_days');

        //for 1.3 and the likes, without flat tables
        if ($this->_old_magento) {
            $coll = Mage::getResourceModel('reports/product_collection')
                    ->addViewsCount();
            return $coll;
        }

        //Magento 1.4 and up
        foreach (Mage::getModel('reports/event_type')->getCollection() as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = $eventType->getId();
                break;
            }
        }
        $coll = Mage::getResourceModel('reports/product_collection');
        $select = $coll->getSelect();
        $select
                ->join(array('r' => Mage::getModel('core/resource')->getTableName('reports/event')),
                       "e.entity_id = r.object_id", array())
                ->where('r.event_type_id = ?', $productViewEvent);

        if (!empty($views_count_days)) {
            $date = new Zend_Date();
            $iso_to = $date->getIso();
            $date->subDay((int )$views_count_days);
            $iso_from = $date->getIso();
            $select->where('r.logged_at > ?', $iso_from);
        }

        $select
                ->columns(array('views' => 'COUNT(r.event_id)'))
                ->group('e.entity_id')
                ->order('views desc')
                ->having('views > 0');

        return $coll;
    }

    protected function getOrderedQtyCollection($desc = 'desc')
    {
		$order_filter_length = Mage::getStoreConfig('selectedproducts/settings/order_filter_length');

        //for 1.3 and the likes, without flat tables
        if ($this->_old_magento) {
            $coll = Mage::getResourceModel('reports/product_collection');
			if (!empty($order_filter_length)) {
				$date = new Zend_Date();
				$iso_to = $date->getIso();
				$date->subDay($order_filter_length);
				$iso_from = $date->getIso();
				$coll->addOrderedQty($iso_from, $iso_to);
			} else {
				$coll->addOrderedQty();
			}
            $coll->setOrder('ordered_qty', $desc); //desc = best sellers on top
            return $coll;
        }

        //Magento 1.4 and up
        $coll = Mage::getResourceModel('reports/product_collection');
        $coll->getSelect()
                ->join(array('oi' => Mage::getModel('core/resource')->getTableName('sales/order_item')),
                       "e.entity_id = oi.product_id", array())
                ->join(array('o' => Mage::getModel('core/resource')->getTableName('sales/order')),
                       "oi.order_id = o.entity_id", array())
                ->where('e.type_id NOT IN (?)', $this->getDisallowedTypes())
                ->where('o.state<>?', array('canceled'))
                ->columns(array('ordered_qty' => 'SUM(oi.qty_ordered)'))
                ->group('e.entity_id')
                ->order('ordered_qty ' . $desc); //desc = best sellers on top

		if (!empty($order_filter_length)) {
			$date = new Zend_Date();
			$iso_to = $date->getIso();
			$date->subDay($order_filter_length);
			$iso_from = $date->getIso();
			$coll->getSelect()->where("oi.created_at BETWEEN '{$iso_from}' AND '{$iso_to}'");
		}

        return $coll;
    }

    // Prepare an array of ID:s for reviewed items
    private function getReviewedIds($attrib, $desc, $num_get)
    {
        $sid = Icommerce_Default::getStoreId();
        $order = $attrib == "review_best" ? "rating_summary" : "reviews_count";
        $sql = "SELECT entity_pk_value FROM review_entity_summary WHERE entity_type=1 AND store_id=$sid ORDER BY $order $desc LIMIT 0,$num_get";
        return Icommerce_Db::getColumn($sql);
    }

	private function getCartItemIds() {
		static $_getCartItemIds = null;

		if ($_getCartItemIds !== null) {
			return $_getCartItemIds;
		}
		$items = Mage::getSingleton('checkout/cart')->getItems();
		if (empty($items)) {
			return array();
		}

		/**
		 * product IDs in cart
		 * @var $cart_pids array
		 */
		$cart_pids = array();
		/**
		 * @var $item Mage_Sales_Model_Quote_Item
		 */
		foreach ($items as $item) {
			$pid = (int )$item->getProduct()->getEntityId();
			$cart_pids[$pid] = $pid;
		}

		$_getCartItemIds = $cart_pids;
		return $cart_pids;
	}

	/**
	 * Get related products in cart
	 *
	 * @return array
	 */
	private function getCartRelatedIds($num_get) {
		$cart_pids = $this->getCartItemIds();
		if (empty($cart_pids)) {
			return array();
		}

		$sec_cart_pids = array_values($cart_pids);
		$rel_pids = array();

		// get from each product related items (total:{$num_get}) and then strip items to match needed collection count (final count: {$num_get})
		$found_pids = array();
		foreach ($cart_pids as $pid) {
			$sql = 'SELECT linked_product_id FROM catalog_product_link WHERE link_type_id = 1 AND product_id = ? AND NOT linked_product_id IN (?) LIMIT ' . (int )$num_get;

			$rel_pid_arr = Icommerce_Db::getColumn($sql, array($pid, $sec_cart_pids));

			if (!empty($rel_pid_arr)) {
				$found_pids[$pid] = $rel_pid_arr;
				$sec_cart_pids += $rel_pid_arr;
			}
		}

		while (true) {
			if (empty($found_pids)) {
				break;
			}
			foreach ($found_pids as $pid => $ids) {
				$rel_pids[] = array_shift($found_pids[$pid]);
				if (count($rel_pids) >= $num_get) {
					break(2);
				}
			}
		}

		return $rel_pids;
	}

    private function getPriceRuleIds($num_get)
    {
        // Either a product is in a price rule or not
        // There is no good way to sort it except for random.
        // Checking dates makes senes
        $wid = Icommerce_Default::getWebsiteId();
        $aid_vis = Icommerce_Eav::getAttributeId("visibility", "catalog_product");
        $aid_stat = Icommerce_Eav::getAttributeId("status", "catalog_product");
        $sql = "SELECT distinct(crp.product_id) FROM catalogrule_product as crp, catalog_product_entity_int as cpei, catalog_product_entity_int as cpei1
                WHERE cpei.entity_id=crp.product_id AND cpei.attribute_id=$aid_stat AND cpei.value=1 AND
                      cpei1.entity_id=crp.product_id AND cpei1.attribute_id=$aid_vis AND cpei1.value IN (2,4)
                ORDER BY RAND() LIMIT 0,$num_get";
        return Icommerce_Db::getColumn($sql);
    }

    private function getMostSearched( $num_get ){
        $sql = "SELECT product_id, COUNT(product_id) as cnt
                FROM catalogsearch_result
                GROUP BY product_id
                ORDER BY cnt DESC
                LIMIT " . $num_get;
        return Icommerce_Db::getColumn($sql);
    }


    // Prepare collection of customer's orders
    private function prepareOrdersCollection()
    {
        $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addAttributeToFilter("customer_id", Mage::getSingleton('customer/session')->getCustomerId());
        $orders->load( /*true*/);
        return $orders;
    }

    // Prepare collection of customer's list of suggested products
    private function prepareSuggestedProducsCollection($_orders)
    {

        $_suggestedProducts = array();
        $_alreadySuggested = array();
        foreach ($_orders as $order) /* this customer's orders */
        {
            $items = $order->getAllItems();
            foreach ($items as $item) /* order rows */
            {
                /* get product for this row */
                $orderedProd = Mage::getModel('catalog/product')->setStoreId($order->getStoreId())->load($item->getProductId());
                $link = 'up_sell';
                $linkedProds = Icommerce_Default::getLinkedProducts($orderedProd, $link);
                foreach ($linkedProds as $lProd) {
                    if (!in_array($lProd->getSKU(), $_alreadySuggested)) {
                        $_suggestedProducts[] = $lProd;
                        $_alreadySuggested[] = $lProd->getSKU();
                    }
                }
            } /* end foreach ($items as $item) */
        } /* end foreach ($orders as $order) */

        return $_suggestedProducts;
    }

    public function getCurrentCategoryId(){
        // The reason we look in cookie for MOF is that with FPC... the sessions value don't get updated,
        // when serving new category page via cache. The MOF cookie will always be right, when used.
        $cat_id = null;
        if( isset($_COOKIE["mof_filters"]) ){
            $filters = Zend_Json::decode( $_COOKIE["mof_filters"] );
            if( isset($filters["id"]) ) $cat_id = $filters["id"];
        }
        if( !$cat_id && ($cat = Mage::registry("current_category")) ){
            $cat_id = $cat->getId();
        }
        return $cat_id;
    }

    static $_real_cat_ids_by_prod = array();
    protected function getRealCatId( $prod=null ){
        $pid = $prod ? Icommerce_Db::toId($prod->getId()) : 0;
        if( $pid ){
            if( !isset(self::$_real_cat_ids_by_prod[$pid]) ){
                // Take category ID from block base category, or from product ?
                $sid = Icommerce_Default::getStoreId();
                $cat_id = Icommerce_Db::getValue( "SELECT max(category_id) FROM catalog_category_product_index WHERE product_id=? AND store_id IN (0,?)", array($pid,$sid) );
                self::$_real_cat_ids_by_prod[$pid] = $cat_id;
            }
            return self::$_real_cat_ids_by_prod[$pid];
        } else {
            // Figure out the right category ID
            $cat_id = $this->getCategoryId();
            if( $cat_id instanceof Varien_Object ){
                $cat_id = $cat_id->getId();
            }
            else {
                if( !Icommerce_Utils::isInteger($cat_id) ) {
                    if ($cat_id == "current") {
                        $cat_id = $this->getCurrentCategoryId();
                    }
                    elseif ($cat_id == "parent") {
                        if( $cur = $this->getCurrentCategoryId() ){
                            $cat_id = Icommerce_Eav::getValue( $cur, "parent_id", "catalog_category" );
                        } else {
                            $cat_id = null;
                        }
                    }
                    else {
                        // Try Url Key
                        //$cat = Icommerce_Category::loadByUrlKey($cat_id);
                        //$cat_id = Icommerce_Db::getValue( "SELECT " );
                    }
                }
            }
            return $cat_id;
        }
    }

    static $_cat_data = array();
    protected function getCategoryName( $prod=null ){
        if( !($cat_id=$this->getRealCatId($prod)) ){
            // No category ID available, so use a fallback value
            return Mage::getStoreConfig("selectedproducts/settings/fallback_category_label");
        }
        if( !isset(self::$_cat_data[$cat_id]["name"]) ){
            $name = Icommerce_Eav::getValue($cat_id,"name","catalog_category");
            self::$_cat_data[$cat_id]["name"] = $name ? $name : "";
        }
        return self::$_cat_data[$cat_id]["name"];
    }

    public function getCategoryImageHtml( $prod=null, $image_attrib="image", $width=32, $height=32 ){
        if( !($cat_id=$this->getRealCatId($prod)) ) return "";
        $img = Icommerce_Eav::getValue($cat_id,$image_attrib,"catalog_category");
        if( !$img ) return "";

        $imgUrl = $this->getUrl().'media/catalog/category/'. $img;
        $name = Icommerce_Eav::getValue($cat_id,"name","catalog_category");
        $imgHtml = "<img src='$imgUrl' height='$height' width='$width'  alt='".$this->htmlEscape($name)."' title='".$this->htmlEscape($name)."' class='category-image' />";

        return $imgHtml;
    }

    protected function getCategoryUrl( $prod=null ){
        if( !($cat_id=$this->getRealCatId($prod)) ) return "";
        if( !isset(self::$_cat_data[$cat_id]["url_path"]) ){
            $val = Icommerce_Eav::getValue($cat_id,"url_path","catalog_category");
            self::$_cat_data[$cat_id]["url_path"] = $val ? $val : "";
        }
        return Mage::getBaseUrl() . self::$_cat_data[$cat_id]["url_path"];
    }

    /*
    protected $_price_block;
    public function getPriceHtml( $product, $displayMinimalPrice = false, $idSuffix='' ){
        if( !$this->_price_block ){
            $this->_price_block = $this->getLayout()->createBlock( "catalog/product" );
            $this->_price_block->setTemplate( 'catalog/product/price.phtml' );
        }
        $this->_price_block->setProduct( $product )
            ->setDisplayMinimalPrice($displayMinimalPrice)
            ->setIdSuffix($idSuffix);
        return $this->_price_block->toHtml();
    }
    */

    static $_ratings;
    public function getProductRating( $prod ){
        $pid = $prod instanceof Varien_Object ? $prod->getId() : $prod;
        $sids = "0,".Icommerce_Default::getStoreId();
        $val = Icommerce_Db::getValue( "SELECT rating_summary FROM review_entity_summary WHERE entity_type=1 AND entity_pk_value=? AND store_id IN (?)", array($pid,$sids) );
        return $val;
    }


    public function shuffle($coll)
    {
        // Collect as an array
        $arr = array();
        foreach ($coll as $prod) {
            $arr[] = $prod;
        }

        // shuffle it
        $cnt = count($arr);
        for ($ix = 0; $ix < $cnt; $ix++) {
            $ix2 = rand() % $cnt;
            if ($ix != $ix2) {
                $tmp = $arr[$ix];
                $arr[$ix] = $arr[$ix2];
                $arr[$ix2] = $tmp;
            }
        }
        return $arr;
    }

    function setDisallowedTypes($types = "") {
        if(is_array($types))
            $this->_disallowed_types = $types;
        else
            $this->_disallowed_types = explode(",",$types);
    }

    function getDisallowedTypes() {
        // if disallowed types is not set, use default values
        if($this->_disallowed_types == null)
            return array('grouped', 'configurable', 'bundle');
        return $this->_disallowed_types;
    }

    /**
     * Get collection using collection cache key. Note that this function resets the global storeId for Magento.
     *
     * @param $key  Key for specific collection cache row that identifies certain cache AND holds the parameters for refreshing it
     * @param $restore_store    Indicates if previously selected store should be restored after fetching the collection.
     * @return array|null   Either collection OR null, if something went wrong
     */
    public function getRealCollectionByCacheKey($key, $restore_store = true) {
        static $default_store_id;

        // fetch current store so that we could reset/restore it after we get our collection
        if($restore_store)
            $original_store_id =  Mage::app()->getCurrentStore();

        // keep fallback to serialize
        $fst_char = substr($key, 0, 1);
        if ($fst_char <> '{') {
            $params = unserialize($key);
        } else {
            $params = Zend_Json::decode($key);
        }

        if ($params) {
            if(isset($params['cat_id']))
                $this->setCategoryId($params['cat_id']);

            if (isset($params['store_id']))
                Mage::app()->setCurrentStore($params['store_id']);
            else {
                if(!$default_store_id)
                    $default_store_id = Icommerce_Db::getValue('SELECT MIN(store_id) FROM core_store WHERE is_active=1 AND website_id!=0');

            	Mage::app()->setCurrentStore($default_store_id);
            }

            $coll = $this->getRealCollection($params['attribute'], $params['num_get'], $params['desc'], $params['attribs'], $params['attributesToFilter'], isset($params['instock']) ? $params['instock'] : 0, isset($params['xtra_options']) ? $params['xtra_options'] : array());

            // Restore the original store after fetching the collection
            if($restore_store)
                Mage::app()->setCurrentStore($original_store_id);

            return $coll;
        }
        return null;
    }
}
