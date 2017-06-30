<?php
$jpi_opts_sorted;

function JPI_OptSort($o1, $o2)
{
    global $jpi_opts_sorted;
    $v1 = isset($jpi_opts_sorted[$o1]) ? $jpi_opts_sorted[$o1] : (int) $o1;
    $v2 = isset($jpi_opts_sorted[$o2]) ? $jpi_opts_sorted[$o2] : (int) $o2;
    return ($v1 - $v2 != 0) ? 2 * ($v1 - $v2) : 1;
}

class Icommerce_JsonProductInfo_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $cache;
    protected $dbread;
    protected $cfg = array();
    protected $virtual_attrs = array();
    protected $explicit_attrs = array();
    protected $lookup_order = array();
    protected $cached_attrs;
    protected $acode_id_map = array();
    protected $simple_datas;
    protected $renderers = array();
    protected $simulate_product_view;
    protected $request;

    public $no_cache;
    
    // Set to non-null to supress cache reads of values
    public function __construct()
    {
        $this->cache = Mage::app()->getCacheInstance();
        $this->dbread = Icommerce_Db::getRead();
        $this->request = Mage::app()->getFrontController()->getRequest();
        $p = $this->request->getParams();
        $no_cache = (isset($p["jpi_no_cache"]) ? $p["jpi_no_cache"] : false) || (isset($p["no_jpi_cache"]) ? $p["no_jpi_cache"] : false);
        $this->no_cache = (bool) $no_cache;
    }

    static $st_cfg;

    static function getConfig($param)
    {
        if (isset(self::$st_cfg[$param])) {
            return self::$st_cfg[$param];
        }
        $val = Mage::getStoreConfig("jsonproductinfo/settings/" . $param);
        self::$st_cfg[$param] = $val;
        return $val;
    }

    public function toProduct($prod)
    {
        if ($prod == "current")
            $prod = Mage::registry("current_product");
        return $prod;
    }

    /**
     * Return true if we have activated setting 'show products out of stock'
     *
     * @return bool
     */
    public function ignoreStock()
    {
        static $st_ignore_stock;
        if ($st_ignore_stock === null) {
            $st_ignore_stock = Mage::getStoreConfig("cataloginventory/options/show_out_of_stock");
        }
        return (bool) $st_ignore_stock;
    }

    /**
     * Returns if in product view or not (then media gallery and other details can be loaded)
     *
     * @return bool
     */
    public function isProductView()
    {
        if ($this->simulate_product_view)
            return true;
        return Mage::registry("current_product") ? true : false;
    }

    /**
     * Enforce product view
     *
     * @return bool
     */
    public function simulateProductView($do_it = true)
    {
        $this->simulate_product_view = $do_it;
    }
    
    // Value cache to avoid repeated lookup of simple products
    protected $simple_cache = array();

    /**
     * Returns an array of product IDs related in given way with "aggregate product"
     *
     * @param $prod The product
     * @param $prod The relation
     * @return array
     */
    public function getSimpleIds($prod, $relation = "")
    {
        $simples_to_ignore = Mage::getStoreConfig('forcedsimpleproduct/settings/simples_to_ignore');
        $pid = $prod->getData("entity_id");
        $key = "$pid-$relation";
        if (isset($this->simple_cache[$key])) {
            return $this->simple_cache[$key];
        }
        
        $eids = null;
        if ($relation) {
            $link_type_id = Icommerce_Db::getValue("SELECT link_type_id FROM catalog_product_link_type WHERE code=?", 
                    array(
                            $relation
                    ));
            if (! $link_type_id)
                Mage::throwException("JsonProductInfo - unknown link type: $relation");
            $eids = Icommerce_Db::getColumn("SELECT linked_product_id FROM catalog_product_link WHERE product_id=? AND link_type_id=?", 
                    array(
                            $pid,
                            $link_type_id
                    ));
        } else {
            $type = $prod->getData("type_id");
            if ($type == "configurable") {
                if (! $simples_to_ignore)
                    $simples_to_ignore = "''";
                $eids = Icommerce_Db::getColumn("SELECT product_id FROM catalog_product_super_link WHERE parent_id=? AND product_id NOT IN ($simples_to_ignore)", 
                        array(
                                $pid
                        ));
            } else 
                if ($type == "simple") {
                    $eids = array(
                            $pid
                    );
                } else {
                    // Dispatch event to populate relation
                    $eids = array();
                    Mage::dispatchEvent("json_pinfo-get-simple-ids", array(
                            "simple_ids" => &$eids
                    ));
                }
        }
        
        // Make sure the simple ones are enabled and on current website
        if ($eids) {
            /**
             *
             * @var $productIds Mage_Catalog_Model_Resource_Product_Collection
             */
            $productIds = Mage::getResourceModel("catalog/product_collection")->addIdFilter($eids)
                ->addAttributeToFilter("status", Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->addWebsiteFilter(array(
                    Mage::app()->getWebsite()
                        ->getId()
            ));
            $eids = $productIds->getAllIds();
        }
        
        // Store, if accessed again
        $this->simple_cache[$key] = $eids;
        return $eids;
    }

    /**
     * This function adds a "virtual attribute" - an attribute that has no value from the product entity.
     * The value is provided to frontend by custom logic.
     *
     * @param $acode
     * 
     * @return bool
     */
    public function addVirtualAttribute($acode)
    {
        if (! $acode)
            return false;
            // Array case ?
        if (is_array($acode)) {
            foreach ($acode as $an_acode) {
                if (! $this->addVirtualAttribute($an_acode))
                    return false;
            }
            return true;
        } else {
            // Single attribute case
            $this->virtual_attrs[$acode] = null;
        }
        
        return true;
    }

    /**
     * This function will add an explicit attribute to the list of attributes that are loaded
     * for simple products.
     *
     * @param $acode
     * 
     * @return bool
     */
    public function addExplicitAttribute($acode)
    {
        if (! $acode)
            return false;
            // Array case ?
        if (is_array($acode)) {
            foreach ($acode as $an_acode) {
                $this->addExplicitAttribute($an_acode);
            }
        } else {
            // Single attribute case
            $this->explicit_attrs[$acode] = Icommerce_Eav::getAttributeId($acode);
        }
        return true;
    }

    protected function getCachedAttribs()
    {
        if ($this->cached_attrs == null) {
            $db_attrs = Icommerce_Db::getAssociative(
                    "SELECT ea.attribute_code, ea.attribute_id FROM catalog_eav_attribute as cea
                    INNER JOIN eav_attribute as ea ON cea.attribute_id=ea.attribute_id WHERE cea.jsonproductinfo_cache=1");
            $this->cached_attrs = array_merge($db_attrs, $this->explicit_attrs, $this->virtual_attrs);
        }
        return $this->cached_attrs;
    }

    /**
     * Registers a value renderer for a particular attribute
     *
     * @param $attributeCode The attribute code to be rendered
     * @param $renderer A model of a class that extends Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract
     *          
     * @return $this
     */
    public function addAttributeRenderer($attributeCode, $renderer)
    {
        if (!$renderer instanceof Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract) {
            $rendererModel = Mage::getModel($renderer);
            if (! $renderer_obj instanceof Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract) {
                Mage::throwException("addAttributeRenderer - $attributeCode - Object not a renderer: $renderer");
            }
            $renderer = $rendererModel;
        }
        
        $this->renderers[$attributeCode] = $renderer;
        $renderer->_helper = $this;
        return $this;
    }

    /**
     * Returns an array of simple stock values (usually for merging into main
     * product info array)
     *
     * @param $prod
     * @param $relation Pick up simple products via related/upsell/crossell/...
     * 
     * @return array
     */
    public function getSimpleStockValues($prod, $relation = "")
    {
        $prod = $this->toProduct($prod);
        
        // Simple stock info in cache ?
        $pid = $prod->getData("entity_id");
        $cache_key = "jpinfo-s-$pid-$relation" . $this->getCurrentStockId();
        $sval = (! $this->no_cache ? $this->cache->load($cache_key) : "");
        if ($sval) {
            $val = Zend_Json::decode($sval);
            return $val;
        }
        
        // Have to calculate from DB
        $eids = $this->getSimpleIds($prod, $relation);
        $eid_str = implode(",", $eids);
        // Multi site stock ?
        $stock_id = $this->getCurrentStockId();
        
        $stockItems = Mage::getModel('cataloginventory/stock_item')->getCollection()
            ->addProductsFilter($eids)
            ->addStockFilter($stock_id);
        
        $globalManageStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $globalBackorders = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS);
        
        $baseColumns = array(
                'product_id',
                'qty',
                'stock_status' => 'is_in_stock',
                'min_qty',
                'backorders',
                'use_config_backorders',
                'manage_stock',
                'use_config_manage_stock',
                'use_config_min_qty'
        );
        $extraColumns = $this->getConfig("additional_stockinfo");
        $extraColumns = ($extraColumns ? explode(",", $extraColumns) : array());
        $stockItems->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array_merge($baseColumns, $extraColumns));
        
        $qtys = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAssoc($stockItems->getSelect());
        $config_min_qty = (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY);
        
        $val = array();
        $use_stock_levels = $this->getConfig("use_stock_red_yellow");
        foreach ($eids as $eid) {
            $qty = 0;
            if (isset($qtys[$eid]["qty"])) {
                $qty = (int) $qtys[$eid]["qty"];
            }
            if (isset($qtys[$eid]["use_config_min_qty"]) && $qtys[$eid]["use_config_min_qty"] == 1) {
                
                $qty = $qty - $config_min_qty;
                if ($qty < 0)
                    $qty = 0;
            } else {
                if (isset($qtys[$eid]["min_qty"])) {
                    $qty = $qty - (int) $qtys[$eid]["min_qty"];
                    if ($qty < 0)
                        $qty = 0;
                }
            }
            
            $val[$eid]["qty"] = $qty;
            if ($use_stock_levels) {
                // ### Check $qty with regards to product limit
            }
            if (isset($qtys[$eid]["stock_status"])) {
                $val[$eid]["stock_status"] = (int) $qtys[$eid]["stock_status"];
                if ($val[$eid]["stock_status"] && isset($qtys[$eid]["backorders"])) {
                    if ((! $qtys[$eid]["use_config_backorders"] && $qtys[$eid]["backorders"]) || ($qtys[$eid]["use_config_backorders"] && $globalBackorders)) {
                        $val[$eid]["backorders"] = 1;
                    }
                }
            }
            
            if (isset($qtys[$eid]["manage_stock"])) {
                if ((! $qtys[$eid]["use_config_manage_stock"] && ! $qtys[$eid]["manage_stock"]) || ($qtys[$eid]["use_config_manage_stock"] && ! $globalManageStock)) {
                    $val[$eid]["manage_stock"] = 0;
                }
            }
            
            // Do custom extra stock columns
            foreach ($extraColumns as $col) {
                if (isset($qtys[$eid][$col]))
                    $val[$eid][$col] = $qtys[$eid][$col];
            }
        }
        
        // Save in cache
        $sval = Zend_Json::encode($val);
        $tags = $this->getCacheTags($prod, $eids);
        $this->cache->save($sval, $cache_key, $tags, $this->getConfig("lifetime_stock"));
        return $val;
    }

    private function getCurrentStockId()
    {
        return Icommerce_Products::getStockId();
    }

    protected function getCacheTags($prod, $eids)
    {
        $tags = array(
                "CATALOG_PRODUCT_" . $prod->getId(),
                "JPIINFO"
        );
        foreach ($eids as $eid) {
            $tags[] = "CATALOG_PRODUCT_" . $eid;
        }
        return $tags;
    }

    /**
     * Returns an array of simple attribute values
     *
     * @param $prod
     * @param $relation Pick up simple products via related/upsell/crossell/...
     * 
     * @return array
     */
    public function getSimpleBaseValues($prod, $relation = "", $cache_tag_app = "")
    {
        $prod = $this->toProduct($prod);
        
        $pid = $prod->getData("entity_id");
        $cache_key_base = $this->getCacheKey($prod, $relation, 'b') . $cache_tag_app;
        
        $sval = (! $this->no_cache ? $this->cache->load($cache_key_base) : "");
        if ($sval) {
            // Got base attributes values on simple from cache
            $val = Zend_Json::decode($sval);
            $this->simple_datas["$pid-$relation" . $cache_tag_app] = $val;
            return $val;
        }
        
        // Have to base attributes values on simple here & now
        $eids = $this->getSimpleIds($prod, $relation);
        
        // What attributes should we load from simple products ?
        $attrs = $this->getCachedAttribs();
        
        // Merge in lookup attributes
        if ($attrs_lookup = $this->getLookupOrder($prod, $relation, false)) {
            foreach ($attrs_lookup as $acode) {
                if (! isset($attrs[$acode])) {
                    $attrs[$acode] = Icommerce_Eav::getAttributeId($acode, "catalog_product");
                }
            }
        }
        
        // Fix not to loose the real option values (eliminates need for reversed
        // lookup
        // later)
        if (count($attrs_lookup) > 0) {
            $lookup_attrs = array_combine($attrs_lookup, $attrs_lookup);
        }
        $tval = array();
        foreach ($attrs as $acode => $aid) {
            // Get raw DB values for real attributes (no attribute ID indicates
            // a
            // virtual attribute)
            $vals = $aid ? Icommerce_Eav::readEavValues($eids, $acode, "catalog_product") : array();
            foreach ($eids as $eid) {
                $db_val = isset($vals[$eid]) ? $vals[$eid] : "";
                
                // If we have a specialized renderer, use that, otherwise just
                // format
                // with Default function
                if (! isset($this->renderers[$acode])) {
                    $val = Icommerce_Default::formatValue($db_val, $acode);
                } else {
                    $val = $this->renderers[$acode]->render($eid, $acode, $db_val, $tval, $prod);
                }
                
                $tval[$eid][$acode] = $val;
                if (isset($lookup_attrs[$acode]))
                    $tval[$eid]["_oid:$acode"] = $db_val;
            }
        }
        
        if ($this->getConfig("use_media_gallery")) {
            
            // Need "default" renderer ?
            if (! isset($this->renderers["media_gallery"])) {
                $this->addAttributeRenderer("media_gallery", new Icommerce_JsonProductInfo_Model_Attribute_Renderer_Media_Gallery());
            }
            
            foreach ($tval as $eid => $value) {
                $val = $this->renderers["media_gallery"]->render($eid, "media_gallery", "", $tval, $prod);
                $tval[$eid]["media_gallery"] = $val;
            }
        }
        
        // Make simple array and move entity_id onto values
        $val = array();
        foreach ($tval as $eid => $arr) {
            $arr["id"] = $eid; // Add the id to the array 20120927 Peter
            $val[$eid] = $arr;
        }
        
        // Cache it, make id dependent on every simple product + the "center"
        // product
        $sval = Zend_Json::encode($val);
        $tags = $this->getCacheTags($prod, $eids);
        $this->cache->save($sval, $cache_key_base, $tags, $this->getConfig("lifetime"));
        $this->simple_datas["$pid-$relation" . $cache_tag_app] = $val;
        
        return $val;
    }

    /**
     * Gemerate cache key
     *
     * @param $product Product whose id we're going to use
     * @param $relation Relational data
     * @param $custom Custom mark for the cache key
     * 
     * @return string Generated cache key
     */
    public function getCacheKey($product, $relation, $custom)
    {
        $sid = Mage::app()->getStore()->getStoreId();
        $pid = $product->getId();
        
        // Support for frontend currency switching
        $currency = "";
        if ($cookie = $this->request->getCookie("currency")) {
            $currency = "-$cookie";
        }
        
        return 'jpinfo-' . $custom . '-' . $sid . '-' . $pid . '-' . $relation . "-" . $currency;
    }

    /**
     * Returns an array of simple attribute values, that have been tagged in
     * admin
     * for extraction
     *
     * @param $prod
     * @param string $relation Pick up simple products via related/upsell/crossell/...
     * 
     * @return array
     */
    public function getSimpleValues($prod, $relation = "", $cache_tag_app = "")
    {
        $prod = $this->toProduct($prod);
        if (! $prod)
            Mage::throwException("getSimpleValues - no product");
            
            // Is both base attribute and stock info in cache ?
        $pid = $prod->getData("entity_id");
        $cache_key = $this->getCacheKey($prod, $relation, 'm') . $this->getCurrentStockId() . $cache_tag_app;
        $sval = (! $this->no_cache ? $this->cache->load($cache_key) : "");
        
        if ($sval) {
            $val = Zend_Json::decode($sval);
            
            $this->simple_datas["$pid-$relation" . $cache_tag_app] = $val;
            return $val;
        }
        
        // Get base values for simple products
        $val = $this->getSimpleBaseValues($prod, $relation, $cache_tag_app);
        
        if ($this->getConfig("use_media_gallery")) {
            $val = $this->removeDuplicateImages($val);
        }
        
        // We need to merge in stock info also
        $val_stock = $this->getSimpleStockValues($prod, $relation);
        foreach ($val_stock as $eid => $vals) {
            // Fix, Doggie. Ignore stock values if not represented in simple
            // values
            if (isset($val[$eid])) {
                $val[$eid] = array_merge($vals, $val[$eid]);
            }
        }
        
        // Custom event for project specific addons to product info
        $transport = new Varien_Object(array(
                'products' => $val
        ));
        Mage::app()->dispatchEvent('jsonproductinfo_prepare_product_data', array(
                'transport' => $transport
        ));
        $val = $transport->getProducts();
        
        // Save in merged info cache - with shorter lifetime
        $sval = Zend_Json::encode($val);
        $tags = $this->getCacheTags($prod, $this->getSimpleIds($prod));
        $lifetime = min($this->getConfig("lifetime"), $this->getConfig("lifetime_stock"));
        $this->cache->save($sval, $cache_key, $tags, $lifetime);
        $this->simple_datas["$pid-$relation" . $cache_tag_app] = $val;
        
        return $val;
    }

    /**
     * Jevgeni Bogatyrjov : Removes duplicate images from media gallery before
     * output
     *
     * @param array $val            
     * @return array
     */
    private function removeDuplicateImages($val = array())
    {
        foreach ($val as $key => $option) {
            $option_media_gallery_unique = array();
            foreach ($option['media_gallery'] as $gallery_item) {
                $option_media_gallery_unique[serialize($gallery_item)] = $gallery_item;
            }
            $val[$key]['media_gallery'] = array();
            foreach ($option_media_gallery_unique as $unique_image) {
                $val[$key]['media_gallery'][] = $unique_image;
            }
        }
        return $val;
    }

    /**
     * Sets an explicit ordering between the super attributes that wil be used
     * to
     * create the
     * lookup order.
     * The $order value here can be shorter or longer than the actually used
     * super attributes on a given product. The $order argument is really omly
     * an
     * order, the
     * actually used attributes in the lookup is decided by the product.
     *
     * @param array $order            
     * @return Icommerce_JsonProductInfo_Helper_Data
     */
    public function setLookupOrder(array $order)
    {
        $this->lookup_order = array();
        foreach ($order as $attr) {
            $this->lookup_order[$attr] = true;
        }
        return $this;
    }

    /**
     * Returns an array of "attribute codes" in which simple products can be
     * accessed according to product type
     *
     * @param $prod
     * @param $relation
     * 
     * @return array
     */
    public function getLookupOrder($prod, $relation = "", $supress_super_attrs = true)
    {
        $prod = $this->toProduct($prod);
        if (! $prod)
            Mage::throwException("getLookupOrder - no product");
        $pid = $prod->getData("entity_id");
        
        static $st_orders = array();
        $cache_key = "$pid-$relation-${supress_super_attrs}";
        if (isset($st_orders[$cache_key]))
            return $st_orders[$cache_key];
        
        $sorted_order = array();
        if ($relation) {} else {
            $type = $prod->getData("type_id");
            if ($type == "configurable") {
                $sql_supress_super = ($supress_super_attrs ? "AND cea.jsonproductinfo_suppress_lookup!=1" : "");
                $order = Icommerce_Db::getColumn(
                        "SELECT ea.attribute_code  FROM catalog_product_super_attribute as sa
                        INNER JOIN catalog_eav_attribute as cea ON cea.attribute_id=sa.attribute_id
                        INNER JOIN eav_attribute as ea ON ea.attribute_id=sa.attribute_id
                        WHERE product_id=? $sql_supress_super ORDER BY sa.position, sa.product_super_attribute_id", array(
                                $pid
                        ));
                
                // First extract out attribites we have explicit order for
                foreach ($this->lookup_order as $attr => $val) {
                    if (($ix = array_search($attr, $order)) !== FALSE) {
                        $sorted_order[] = $attr;
                        unset($order[$ix]);
                    }
                }
                // Then do the rest
                foreach ($order as $ix => $attr) {
                    $sorted_order[] = $attr;
                }
            }
        }
        $st_orders[$cache_key] = $sorted_order;
        return $sorted_order;
    }

    protected $_raw_opt_id_mode;
    // Used to decide whether to make lookup array with
    // only option_ids (instead of option labels)
    
    /**
     * Recursively build up a lookup array by walk attributes and options
     *
     * @param $lut lookup array
     * @param $attributes Customizable attribute codes that will be used to build the lookup array
     * @param $simpleProductData Simple Product data
     * @param $eid ???            
     */
    protected function addToLut(&$lut, &$attributes, $simpleProductData, $eid)
    {
        $attributeCode = array_shift($attributes);
        $optionValue = $simpleProductData[$attributeCode];
        if ($this->_raw_opt_id_mode) {
            // It is better to use a stored original option_id rather than doing
            // reverse
            // lookup of option value below
            if (isset($simpleProductData["_oid:$attributeCode"])) {
                $optionId = $simpleProductData["_oid:$attributeCode"];
                unset($simpleProductData["_oid:$attributeCode"]);
            } else {
                $optionId = Icommerce_Default::getOptionValueId($attributeCode, $optionValue);
            }
        } else {
            $optionId = $optionValue;
        }
        
        if (count($attributes) > 0) {
            if ($optionId) {
                $this->addToLut($lut[$optionId], $attributes, $simpleProductData, $eid);
            }
        } else {
            // Populate bottom level
            $value = $eid ? $eid : $simpleProductData;
            
            if ($optionId) {
                // If reverse lookup fails above, we otherwise get empty key
                // here.
                $lut[$optionId] = array_merge($value, array(
                        'parent_attribute_code' => $attributeCode
                ));
            }
        }
    }

    /**
     * Returns an array for navigating aggregate product according to structured
     * relation with simples ones
     *
     * @param $prod The
     *            product
     * @param $relation A
     *            relation (upsell/related/...)
     * @param $supress_super_attrs In
     *            generated array, should we skip those super attribs that are
     *            tagged so in admin ?
     * @param $emit_only_id Set
     *            this to true to just get the entity_id as value (instead of
     *            whole
     *            simple info array).
     * @return array
     */
    public function getLookupArray($prod, $relation = "", $supress_super_attrs = true, $emit_only_id = false, $raw_opt_id_mode = false, $cache_tag_app = "")
    {
        $order = $this->getLookupOrder($prod, $relation, $supress_super_attrs);
        
        // Create the array used for lookups by JS
        $lut = array();
        $simples = $this->getSimpleValues($prod, $relation, $cache_tag_app);
        $this->_raw_opt_id_mode = $raw_opt_id_mode;
        foreach ($simples as $eid => $s) {
            $order_tmp = $order;
            $this->addToLut($lut, $order_tmp, $s, $emit_only_id ? $eid : null);
        }
        return $lut;
    }

    public function sortByOptionPosition($r)
    {
        // No reason to continue if the input value is empty (will run into problems with getAssociative call)
        if (! count($r)) {
            return $r;
        }
        
        $opts_sorted = array_keys($r);
        sort($opts_sorted);
        $opt_id_str = implode(",", $opts_sorted); // sort
        static $st_sorted_orders = array();
        if (isset($st_sorted_orders[$opt_id_str])) {
            $r_s = $st_sorted_orders[$opt_id_str];
        } else {
            $r_s = Icommerce_Db::getAssociative("SELECT option_id, sort_order FROM eav_attribute_option WHERE option_id IN ($opt_id_str) ORDER BY sort_order ASC");
            $st_sorted_orders[$opt_id_str] = $r_s;
        }
        
        global $jpi_opts_sorted;
        $jpi_opts_sorted = $r_s;
        uksort($r, "JPI_OptSort");
        
        return $r;
    }

    /**
     * Returns an array containing all used attribute IDs, attribute options and option IDs
     *
     * @return array
     */
    public function getAttributeIdLookup($include_sort = false)
    {
        if (!$this->simple_datas) {
            return array();
        }
        $sid = Mage::app()->getStore()->getId();
        
        // This could also be cached, since it is a bit of iteration
        // Cache key is keys of simple_datas
        $cache_key = "aid_lut:" . implode("-", array_keys($this->simple_datas)) . "_" . $sid;
        $sval = (! $this->no_cache ? $this->cache->load($cache_key) : "");
        if ($sval) {
            $val = Zend_Json::decode($sval);
            return $val;
        } else {
            $this->acode_id_map = null;
        }
        
        if (! count($this->acode_id_map) && count($this->simple_datas)) {
            // Have to loop through all loaded simple datas to extract out used
            // attribs
            // and option IDs
            $is_select = array();
            // TODO : This end, is a lame fix for multiple products per page. Need a proper rewrite of the whole structure. 
            $lastElement = end($this->simple_datas);
            // ($lastElement as $key => $val) {
                foreach ($lastElement as $eid => $vals) {
                    foreach ($vals as $acode => $val) {
                        if (substr($acode, 0, 5) == "_oid:")
                            continue;
                        if (! isset($is_select[$acode])) {
                            $ainfo = Icommerce_Eav::getAttributeInfo($acode);
                            $is_select[$acode] = ($ainfo && ($ainfo["frontend_input"] == "select" || $ainfo["frontend_input"] == "multiselect"));
                        }
                        if ($is_select[$acode]) {
                            foreach (explode(",", $val) as $opt_code) {
                                if (! isset($this->acode_id_map[$acode][$opt_code])) {
                                    $oid = isset($vals["_oid:$acode"]) ? $vals["_oid:$acode"] : Icommerce_Default::getOptionValueId($acode, $opt_code);
                                    $this->acode_id_map[$acode][$opt_code] = $oid ? $oid : - 1;
                                }
                            }
                        }
                    }
                }
            //}
        }
        
        foreach ($this->acode_id_map as $acode => $map) {
            // Make sure we also can lookup the attribute ID
            if (! isset($this->acode_id_map[$acode]["attribute_id"])) {
                $this->acode_id_map[$acode]["attribute_id"] = Icommerce_Eav::getAttributeId($acode);
            }
        }
        if ($include_sort) {
            foreach ($this->acode_id_map as $acode => &$map) {
                // sortByOptionPosition expetcs option ID as keys, we have to flip twice to use it
                $this->acode_id_map[$acode] = array_flip($this->sortByOptionPosition(array_flip($map)));
            }
        }
        
        $sval = Zend_Json::encode($this->acode_id_map);
        $this->cache->save($sval, $cache_key, array(
                "jpinfo"
        ), $this->getConfig("lifetime"));
        
        return $this->acode_id_map;
    }

    protected function sumQtyBelow($info)
    {
        if (isset($info["qty"]))
            return $info["qty"];
        $qty = 0;
        foreach ($info as $v => $info2) {
            if (is_array($info2)) {
                $qty += $this->sumQtyBelow($info2);
            }
        }
        return $qty;
    }

    /**
     * Will collect all options at a given level (super attribute count) and sum up quantities / saleable
     *
     * @return array
     */
    protected function collectOptionsNested(&$lut_array, $lvl, &$r, $attributeCode = false)
    {
        if (! $lvl) {
            // $acode = $lut_array["parent_attribute_code"];
            foreach ($lut_array as $v => $info) {
                if (! isset($r[$v])) {
                    $r[$v] = array(
                            "qty" => 0
                    );
                }
                $r[$v]["qty"] += $this->sumQtyBelow($info);
                
                if ($attributeCode) {
                    if (! isset($r[$v][$attributeCode])) {
                        $r[$v][$attributeCode] = array();
                    }
                    
                    if (isset($info[$attributeCode]) && $info[$attributeCode]) {
                        $r[$v][$attributeCode][] = $info[$attributeCode];
                        $r[$v][$attributeCode] = array_unique($r[$v][$attributeCode]);
                    }
                }
                // ## Handle saleable also as it is (possibly) more true
            }
        } else {
            $lvl --;
            foreach ($lut_array as $v => &$info) {
                $this->collectOptionsNested($info, $lvl, $r, $attributeCode);
            }
        }
    }

    /**
     * Will collect all options at a given level (super attribute count) and sum
     * up
     * quantities / saleable
     *
     * @param $lut_array //The lookup array
     * @param $lvl // The depth to collect at (corresponds to super attribute count)
     * @param bool $filter_saleable // Filter saleable
     * @param bool $attribute // Attribute code
     * @param bool $sort // Sort by option position
     * @return array
     */
    public function collectOptionsAtLevel(&$lut_array, $lvl, $filter_saleable = true, $attribute = false, $sort = true)
    {
        $r = array();
        $this->collectOptionsNested($lut_array, $lvl, $r, $attribute);
        if ($filter_saleable) {
            // Remove those that are not saleable
            foreach ($r as $v => &$info) {
                if (isset($info['qty']) && $info['qty'] <= 0) {
                    unset($r[$v]);
                }
            }
        }
        
        // Sort according to used options in $all_opts
        if ($sort) {
            $r = $this->sortByOptionPosition($r);
        }
        
        return $r;
    }

    protected function getOptionsForFirstSalableNested($lut_array, $lu_order, $path, $cond)
    {
        // Are we at innermost level of array ?
        $innermost = null;
        
        foreach ($lut_array as $v) {
            $innermost = is_array($v) ? false : true;
            break;
        }
        
        if ($innermost === null) {
            return null;
        }
        
        if ($innermost) {
            if ((isset($lut_array['stock_status']) && $lut_array['stock_status'] && isset($lut_array['qty']) && $lut_array['qty'] > 0) || (isset($lut_array["manage_stock"]) &&
                     $lut_array["manage_stock"] == 0)) {
                if (! $cond || $lut_array[$cond[0]] == $cond[1]) {
                    return $path;
                }
            }
            return null;
        }
        
        foreach ($lut_array as $k => $v) {
            if ($lu_order) {
                $path[$lu_order[0]] = $k;
            } else {
                if ($k == '') {
                    throw new Exception('First salable option without a key value');
                } else {
                    array_push($path, $k);
                }
            }
            $r = $this->getOptionsForFirstSalableNested($v, $lu_order ? array_slice($lu_order, 1) : null, $path, $cond);
            
            if ($r) {
                return $r;
            }
            
            array_pop($path);
        }
    }

    /**
     * Will return option path to first saleable product
     *
     * @param $lut_array The lookup array (returned by getLookupArray() above)
     * @param $lu_order Attribute IDs / codes to use as indexces in returned path)
     * @param $cond An optional condition to be filfilled by matching product
     * @return array
     */
    public function getOptionsForFirstSalable($lut_array, $lu_order = null, $cond = null)
    {
        $r = $this->getOptionsForFirstSalableNested($lut_array, $lu_order, array(), $cond);
        return $r ? $r : array();
    }

    /**
     * Will sum all quantities and return true if total qty is 1 or more
     *
     * @param $lut_array The lookup array (returned by getLookupArray() above)
     * @return boolean
     */
    public function isSalableByQty($lut_array)
    {
        $qty_all = $this->sumQtyBelow($lut_array);
        return $qty_all > 0;
    }

    /**
     * Will sum all quantities and return true if total qty is 1 or more
     *
     * @param $simple_info The simple data array: id=>data (returned by getSimpleInfo() above)
     * @return boolean
     */
    public function isSalableByQtyFromSimpleVals($simple_info)
    {
        $qty_all = 0;
        foreach ($simple_info as $eid => $data) {
            if (isset($data["qty"]))
                $qty_all += $data["qty"];
        }
        return $qty_all > 0;
    }

    /**
     * Helper function: simply returns prices of configurable's simple products
     *
     * @param $prod the product
     * @return array
     */
    public function getPriceArr($prod)
    {
        return array_map(function ($elem)
        {
            return $elem['price_numeric'];
        }, $this->getSimpleBaseValues($prod));
    }
}
