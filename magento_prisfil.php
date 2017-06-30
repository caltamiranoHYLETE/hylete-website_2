<?php
	ob_start();

	// make sure we don't time out
	set_time_limit(0);

    // Open Magento
    require_once 'app/Mage.php';
    Mage::app();

    // See if there is a configuration file
    $conf_path = str_replace( ".php", ".xml", $_SERVER["SCRIPT_FILENAME"] );
    if( file_exists($conf_path) ){
        try {
            $sxml = simplexml_load_file($conf_path);
        } catch( Exception $e ){
            echo "Exception parsing onfig XML: " . (string)$e;
            die();
        }
    } else {
        $sxml = simplexml_load_string('<?xml version="1.0"?><prisfiloptions/>');
    }

    // Set store
    $n = $sxml->xpath("store_id");
    $store_id = $n ? (string)$n[0] : "1";
    $store_id = Icommerce_Default::getStoreId($store_id);
    Mage::app()->setCurrentStore($store_id);

    // Any extra attribs ?
    $xtra_attribs = array();
    if( $axas = $sxml->xpath("extra_attributes") ){
        $xas = $axas[0];
        foreach( $xas->children() as $xa ){
            $acode = $xa->getName();
            $attrs = $xa->attributes();
            $label = $acode;
            if( isset($attrs["label"]) ){
                $label = $attrs["label"];
            }
            $xtra_attribs[$acode] = (string)$label;
        }
    }

    // Custom in / out of stock values?
    $n = $sxml->xpath("in_stock_value");
    $in_stock_value = $n ? (string)$n[0] : "1";
    $n = $sxml->xpath("out_of_stock_value");
    $out_of_stock_value = $n ? (string)$n[0] : "3";

    // How to decide in/out of stock ?
    $stock_algo = "in-stock-flag";
    if( $n = $sxml->xpath("stock_options/algo") ){
        $stock_algo = (string)$n[0];
        if( $stock_algo!="use-qty" ){
            $stock_algo = "in-stock-flag";
        }
    }

    // How to decide in/out of stock ?
    $config_algo = "one-or-more";
    if( $n = $sxml->xpath("stock_options/configurable") ){
        $config_algo = (string)$n[0];
        if( $config_algo!="all" ){
            $config_algo = "one-or-more";
        }
    }

    // Show simple products from configurable ?
    $show_config_simple = $sxml->xpath("products/configurable/include-invisible-simple");
    // Rewrite configurable product url to simple products
    $rewrite_urls_of_simple = $sxml->xpath("products/configurable/rewrite-urls-of-simple");

    // Printout CSV Header
    $heading = array('category1','category2','brand','title','id','price','deliverycost','availability','link','image_link','description');
    $heading = array_merge($heading,$xtra_attribs);
    echo "\"".implode("\"\t\"", $heading)."\"\r\n";

    //---------------------- GET THE PRODUCTS
    $products = Mage::getModel('catalog/product')->getCollection()->addStoreFilter($store_id);
    $products->addAttributeToFilter('status',1);//enabled
    $products->addAttributeToFilter('visibility', 4);//catalog, search
    $products->addFinalPrice();
    $products->addAttributeToSelect('*');
    // ## Add store front sensitivity

    //$product = Mage::getModel('catalog/product');

    $filters = $sxml->xpath('filters');
    $filters = isset($filters[0]) ? $filters[0] : array();

    foreach ($filters as $attribute => $filter) {
        $condition = (string)$filter->condition;
        $value = (string)$filter->value;
        $products->addAttributeToFilter($attribute, array($condition => $value));
    }

    $first = true;
    $n_prod = 0;
    foreach($products as $product) {
        //if( $n_prod++>1 ) break;

        // Trick for nested loop
        $wrap_arr = array( $product );
        //$conf_url = null;
        while( $product = array_pop($wrap_arr) ){
            $product_data = array();

            $is_config = ($product->getData("type_id")=="configurable");
            if( $is_config && isset($show_config_simple[0]) ){
                // Include simple hidden child products that are in stock
                $eids = Icommerce_Db::getColumn( "SELECT product_id FROM catalog_product_super_link WHERE parent_id=?",
                                                 array($product->getData("entity_id")) );
                if( !empty($eids) ){
                    $entity_rewrite = array();
                    $simples = Mage::getModel('catalog/product')->getCollection();
                    $simples->addAttributeToFilter( 'status', 1 ); // enabled
                    $simples->addAttributeToFilter( 'visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE ); // catalog, search
                    $simples->getSelect()->where( "e.entity_id in (".implode(",",$eids).")" );
                    $simples->addFinalPrice();
                    $simples->addAttributeToSelect('*');
                    foreach( $simples->load() as $s ){
                        $wrap_arr[] = $s;
                         if(isset($rewrite_urls_of_simple[0])) $entity_rewrite[$s->getEntityId()] = $product->getProductUrl();
                    }
                }
            }

            $product_data['category1'] = '';
            $product_data['category2'] = '';

            // Get the product categories
            foreach($product->getCategoryIds() as $_categoryId){
                $category = Mage::getModel('catalog/category')->load($_categoryId);
                $product_data['category1'].=$category->getUrl_path().', ';
                $product_data['category2'].=$category->getName().', ';
            }

            $product_data['category1']=rtrim($product_data['category1'],', ');
            $product_data['category2']=rtrim($product_data['category2'],', ');
            $product_data['brand']=Icommerce_Default::formatData($product,"manufacturer");
            $product_data['title']=$product->getName();
            $product_data['sku']=$product->getSku();
            //$product_data['price']=$product->getPrice();
            $product_data['price']=Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
            $product_data['deliverycost']='';

            $pid = $product->getId();
            if( !$is_config ){
                $stockItem = $product->getStockItem();
                // 1.4 does not give QTY here
                //$avail_qty = $stockItem->getQty();
                if( $stock_algo=="use-qty" ){
                    // Take stock from MySQL inventory tables
                    $qty = Icommerce_Db::getValue( "SELECT qty FROM cataloginventory_stock_item WHERE product_id=$pid" );
                    $is_in_stock = $qty > 0;
                }
                else {
                    $is_in_stock = $stockItem->getData("is_in_stock") ? 1 : 0;
                }
            } else {
                // Look on child products
                $is_in_stock = $config_algo=="all" ? true : false;
                $pids = Icommerce_Db::getColumn("SELECT product_id FROM catalog_product_super_link WHERE parent_id = $pid");
                foreach( $pids as $id ){
                    $qty = Icommerce_Db::getValue( "SELECT qty FROM cataloginventory_stock_item WHERE product_id=$id" );
                    if( $config_algo=="all" && $qty<=0 ){
                        $is_in_stock = false;
                        break;
                    } else if( $config_algo!="all" && $qty>0 ) {
                        $is_in_stock = true;
                        break;
                    }
                }
            }
            $product_data['availability'] = $is_in_stock ? $in_stock_value : $out_of_stock_value;

            // We are not allowed to have several products linking to the same product in general.
            // $link = !$conf_url ? $product->getProductUrl() : $conf_url;
            $link = $product->getProductUrl();
            if(isset($entity_rewrite[$product->getEntityId()])) $link = $entity_rewrite[$product->getEntityId()];

            $product_data['link'] = str_replace("magento_prisfil.php", "index.php", $link);

            $product_data['image_link'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
            $product_data['description'] = $product->getDescription();

            // Add the extra attributes
            foreach( $xtra_attribs as $acode => $alabel ){
                $product_data[$acode] = Icommerce_Default::formatData( $product, $acode );
            }

            // sanitize data
            $first_col = true;
            foreach($product_data as $k=>$val){
                if( !$first_col ) echo "\t";
                if( !$val ) $val="";
                $val = strip_tags($val);
                if( $k!='link' && $k!='image_link' && strlen($val)>100 ){
                    $val = substr($val,0,100);
                }
                $bad = array('"',"'","\r\n","\n","\r","\t",chr(160),"&#160;");
                $good = array("",""," "," "," ",""," "," ");
                echo '"'.str_replace($bad,$good,$val).'"';
                $first_col = false;
            }
            echo "\r\n";

            if($first){
                $first = false;
                ob_end_flush();
            }

            // Make sure Magento does not reuse old data.
            $product->unsetData( );
        }
    }
?>
