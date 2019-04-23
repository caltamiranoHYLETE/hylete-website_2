<?php

class Pixlee_Base_Helper_Data extends Mage_Core_Helper_Abstract {

  protected $_pixleeAPI;
  protected $_pixleeProductAlbumModel;
  protected $_isTesting;

  /**
   * Used to initialize the Pixlee API with a stub for testing purposes.
   */
  public function _initTesting($pixleeAPI = null, $pixleeProductAlbum = null) {
    if(!empty($pixleeAPI)) {
      $this->_pixleeAPI = $pixleeAPI;
    }
    if(!empty($pixleeProductAlbum)) {
      $this->_pixleeProductAlbumModel = $pixleeProductAlbum;
    }

    $this->_isTesting = true;
  }

  public function isActive() {
    if($this->_isTesting) {
      return true;
    }

    $pixleeAccountId = Mage::getStoreConfig('pixlee/pixlee/account_id', Mage::app()->getStore());
    $pixleeAccountApiKey = Mage::getStoreConfig('pixlee/pixlee/account_api_key', Mage::app()->getStore());
    if(!empty($pixleeAccountId) && !empty($pixleeAccountApiKey)) {
      return true;
    } else {
      return false;
    }
  }

  public function isInactive() {
    return !$this->isActive();
  }

  public function getNewPixlee() {
    $pixleeAccountId = Mage::getStoreConfig('pixlee/pixlee/account_id', Mage::app()->getStore());
    $pixleeAccountApiKey = Mage::getStoreConfig('pixlee/pixlee/account_api_key', Mage::app()->getStore());
    $pixleeAccountSecretKey = Mage::getStoreConfig('pixlee/pixlee/account_secret_key', Mage::app()->getStore());

    try {
      $this->_pixleeAPI = new Pixlee_Pixlee($pixleeAccountApiKey, $pixleeAccountSecretKey);
      return $this->_pixleeAPI;
    } catch (Exception $e) {
      Mage::log("PIXLEE ERROR: " . $e->getMessage());
    }
  }

  public function getPixleeAlbum() {
    if(empty($this->_pixleeProductAlbumModel)) {
      return Mage::getModel('pixlee/product_album');
    }
    return $this->_pixleeProductAlbumModel;
  }

  public function getPixleeRemainingText() {
    if($this->isInactive()) {
      return "Save your Pixlee API access information before exporting your products.";
    } else {
      return "(Re) Export your products to Pixlee.";
    }
  }

  public function _extractActualProduct($product) {
    Mage::log("*** Before _extractActualProduct");
    Mage::log("Name: {$product->getName()}");
    Mage::log("ID: {$product->getId()}");
    Mage::log("SKU: {$product->getSku()}");
    Mage::log("Type: {$product->getTypeId()}");
    $mainProduct = $product;
    $temp_product_id = Mage::getModel('catalog/product')->getIdBySku($product->getSku());
    $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($temp_product_id);
    Mage::log("Parent IDs are");
    Mage::log($parent_ids);
    if($parent_ids) {
      $mainProduct = Mage::getModel('catalog/product')->load($parent_ids[0]);
    } else {
      $mainProduct = Mage::getModel('catalog/product')->load($product->getId()); // Get original sku as stated in product catalog
    }
    $mainProductClass = get_class($mainProduct);
    Mage::log("*** After _extractActualProduct");
    Mage::log("Name: {$mainProduct->getName()}");
    Mage::log("ID: {$mainProduct->getId()}");
    Mage::log("SKU: {$mainProduct->getSku()}");
    Mage::log("Type: {$mainProduct->getTypeId()}");
    Mage::log("Class: {$mainProductClass}");
    return $mainProduct;
  }

  // Sum up the stock numbers of all the children products
  // EXPECTS A 'configurable' TYPE PRODUCT!
  // If we wanted to be more robust, we could pass the argument to the _extractActualProduct
  // function, but as of 2016/03/11, getAggregateStock is only called after _extractActualProduct
  // has already been called
  public function getAggregateStock($actualProduct) {


    Mage::log("*** In getAggregateStock");
    $aggregateStock = NULL;

    // If after calling _extractActualProduct, there is no 'configurable' product, and only
    // a 'simple' product, we won't get anything back from
    // getModel('catalog/product_type_configurable')
    if ($actualProduct->getTypeId() == "simple") {
      Mage::log("Product Type: Simple");
      // If the product's not keeping track of inventory, we'll error out when we try
      // to call the getQty() function on the output of getStockItem()
      if (is_null($actualProduct->getStockItem())) {
        $aggregateStock = NULL;
      } else {
        $aggregateStock = max(0, $actualProduct->getStockItem()->getQty());
      }
    } else {
      // 'grouped' type products have 'associated products,' which presumably
      // point to simple products
      if ($actualProduct->getTypeId() == "grouped") {
        Mage::log("Product Type: Grouped");
        $childProducts = $actualProduct->getTypeInstance(true)->getAssociatedProducts($actualProduct);
      // And finally, my original assumption that all 'simple' products are
      // under the umbrella of some 'configurable' product
      } else if ($actualProduct->getTypeId() == "configurable") {
        if (!is_a($actualProduct, "Mage_Catalog_Model_Product")) {
          Mage::log("Defaulting to empty children array, actualProduct is " . get_class($actualProduct));
          $childProducts = array();
        } else {
          Mage::log("Product Type: Mage_Catalog_Model_Product");
          $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$actualProduct);
        }
      } else {
        Mage::log("Product Type: None");
        $childProducts = array();
      }

      Mage::log("Child products of length " . sizeof($childProducts));

      foreach ($childProducts as $child) {
        // Sometimes Magento gives a negative inventory quantity
        // I don't want that to affect the overall count
        // TODO: There is probably a good reason why it goes negative
        Mage::log("Child Name: {$child->getName()}");
        Mage::log("Child SKU: {$child->getSku()}");
        if (is_null($child->getStockItem())) {
          Mage::log("Child product not tracking stock, setting to NULL");
        } else {
          Mage::log("Child Stock: {$child->getStockItem()->getQty()}");
          if (is_null($aggregateStock)) {
            $aggregateStock = 0;
          }
          $aggregateStock += max(0, $child->getStockItem()->getQty());
        }
      }
    }
    Mage::log("Returning aggregateStock: {$aggregateStock}");
    return $aggregateStock;
  }

  public function getVariantsDict($actualProduct) {

    Mage::log("*** In getVariantsDict");
    $variantsDict = array();

    // If after calling _extractActualProduct, there is no 'configurable' product, and only
    // a 'simple' product, we won't get anything back from
    // getModel('catalog/product_type_configurable')
    if ($actualProduct->getTypeId() == "simple") {
      if (is_null($actualProduct->getStockItem())) {
        $variantStock = NULL;
      } else {
        $variantStock = max(0, $actualProduct->getStockItem()->getQty());
      }
      $variantsDict[$actualProduct->getId()] = array(
        'variant_stock' => $variantStock,
        'variant_sku' => $actualProduct->getSku(),
      );
    } else {
      // 'grouped' type products have 'associated products,' which presumably
      // point to simple products
      if ($actualProduct->getTypeId() == "grouped") {
        $childProducts = $actualProduct->getTypeInstance(true)->getAssociatedProducts($actualProduct);
      // And finally, my original assumption that all 'simple' products are
      // under the umbrella of some 'configurable' product
      } else if ($actualProduct->getTypeId() == "configurable") {
        if (!is_a($actualProduct, "Mage_Catalog_Model_Product")) {
            Mage::log("Defaulting to empty children array, actualProduct is " . get_class($actualProduct));
            $childProducts = array();
        } else {
            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$actualProduct);
        }
      } else {
        $childProducts = array();
      }

      Mage::log("Child products of length " . sizeof($childProducts));

      foreach ($childProducts as $child) {
        // Sometimes Magento gives a negative inventory quantity
        // I don't want that to affect the overall count
        // TODO: There is probably a good reason why it goes negative
        $variantId = $child->getId();

        if (is_null($child->getStockItem())) {
          $variantStock = NULL;
        } else {
          $variantStock = max(0, $child->getStockItem()->getQty());
        }

        $variantsDict[$variantId] = array(
          'variant_stock' => $variantStock,
          'variant_sku' => $child->getSku(),
        );
      }
    }
    
    if (empty($variantsDict)) {
      return "{}";
    } else {
      return json_encode($variantsDict);
    }
  }

  // getter function to retrieve the category_ids and category names for a product
  // one product can have more than one category, hence its a list
  public function getCategories($product, $categoriesMap) {
    $allCategoriesIds = array();
    $productCategories = $product->getCategoryIds();

    foreach ($productCategories as $categoryId) {
      $parent_ids = $categoriesMap[$categoryId]['parent_ids'];
      $allCategoriesIds = array_merge($allCategoriesIds, $parent_ids);
    }

    $allCategoriesIds = array_unique($allCategoriesIds, SORT_NUMERIC);
    $result = array();
    foreach ($allCategoriesIds as $categoryId) {
      $fields = array(
        'category_id' => $categoryId,
        'category_name' => $categoriesMap[$categoryId]['name'],
        'category_url' => $categoriesMap[$categoryId]['url']
      );

      array_push($result, $fields);
    }

    return $result;
  }

  // Verify that an image exists for this product
  public function getImage($product) {
    $image_name = '';
    if ($product->getImage() != "no_selection") {
      $image_name = $product->getImage();
    } else if ($product->getSmallImage() != "no_selection") {
      $image_name = $product->getSmallImage();
    } else if ($product->getThumbnail() != "no_selection") {
      $image_name = $product->getThumbnail();
    }
    return Mage::getModel('catalog/product_media_config')->getMediaUrl($image_name);
  }

  // Construct some stuff to pass to 'extra_fields'
  public function getExtraFields($actualProduct, $categoriesMap) {
    Mage::log("Constructing product 'extra_fields'");

    // If we failed earlier in _extractActualProduct, and still have a
    // Mage_Sales_Model_Order_Item class instance here, we'll error out
    // when trying to call getProductOptionCollection
    if (!is_a($actualProduct, 'Mage_Catalog_Model_Product')) {
      $extraFields = '';
    } else {

      // Magento's definition of "custom options"
      $customOptionsDict = array();

      // Each $child here is basically an individual row
      $options = Mage::getModel('catalog/product_option')->getProductOptionCollection($actualProduct);
      foreach ($options as $child) {
        Mage::log("* Product has an option");
        // $child at this point is a PHP object, ->getValues() converts it
        // to an array that we can JSONify and pass to our servers
        foreach ($child->getValues() as $v) {
          Mage::log("* An option:");
          Mage::log($v->getData());
          $customOptionsDict[] = $v->getData();
        }
      }

      // Additionally, save all photos associated with the product
      $productPhotos = array();
      $images = $actualProduct->getMediaGalleryImages();
      // Applies to Simple, Configurable, Grouped, Bundle and all other types
      if (!is_null($images) && sizeof($images) > 0) {
        foreach ($actualProduct->getMediaGalleryImages() as $image) {
          array_push($productPhotos, $image->getUrl());
        }              
      }

      if ($actualProduct->getTypeId() == "configurable") {
        $children = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $actualProduct);
        foreach ($children as $child) {
          $child = Mage::getModel('catalog/product')->load($child->getId());
          foreach ($child->getMediaGalleryImages() as $image) {
            array_push($productPhotos, $image->getUrl());
          }
        }
      } elseif ($actualProduct->getTypeId() == "grouped") {
        $children = $actualProduct->getTypeInstance(true)->getAssociatedProducts($actualProduct);
        foreach ($children as $child) {
          $child = Mage::getModel('catalog/product')->load($child->getId());
          foreach ($child->getMediaGalleryImages() as $image) {
            array_push($productPhotos, $image->getUrl());
          }
        }
      }

      $productPhotos = array_values(array_unique($productPhotos));

      // Only configurable products can do the following
      // NOTE: If it gets called on a non-configurable product WE WILL FATAL ERROR
      // The try/catch will NOT handle the fatal error!
      $configurableAttributes = null;
      try {
        if ($actualProduct->isConfigurable()) {
          $configurableAttributes = $actualProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($actualProduct);
        }
      } catch (Exception $e) {
        Mage::log("Got error trying to get default color, setting to null: " . $e->getMessage());
      }

      // In addition to Magento's "custom options", we also want to throw
      // a few things in here, for our own sake
      $extraFields = json_encode(array(
        'magento_custom_options' => $customOptionsDict,
        'magento_product_type' => $actualProduct->getTypeId(),
        'magento_product_visibility' => $actualProduct->getVisibility(),
        'magento_sku' => $actualProduct->getSku(),
        'magento_configurable_attributes' => $configurableAttributes,
        'product_photos' => $productPhotos,
        'categories' => $this->getCategories($actualProduct, $categoriesMap)
      ));
    }

    Mage::log("Made 'extra_fields'");
    Mage::log($extraFields);
    return $extraFields;
  }

  public function getCategoriesMap() {
    // One optimization pending
    // addUrlRewriteToResult joins the URLs table with the query that gets categories
    // However, getUrl() later in the code still results in a query
    // TODO - Find a way to get the URL of the category without additional DB cost

    $categories = Mage::getModel('catalog/category')->getCollection()
      ->addAttributeToSelect('id')
      ->addAttributeToSelect('name')
      ->addUrlRewriteToResult();

    $helper = array();
    foreach ($categories as $category) {
      $helper[$category->getId()] = $category->getName();
    }

    $allCategories = array();
    foreach ($categories as $cat) {
      $path = $cat->getPath();
      $parents = explode('/', $path);
      $fullName = '';

      $realParentIds = array();

      foreach ($parents as $parent) {
        if ((int) $parent != 1 && (int) $parent != 2) {
          $name = $helper[(int) $parent];
          $fullName = $fullName . $name . ' > ';
          array_push($realParentIds, (int) $parent);
        }
      }

      $categoryBody = array(
        'name' => substr($fullName, 0, -3), 
        'url' => $cat->getUrl($cat),
        'parent_ids' => $realParentIds
      );
      $allCategories[$cat->getId()] = $categoryBody;
    }

    // Format
    // Hashmap where keys are category_ids and values are a hashmp with name and url keys
    return $allCategories;
  }

  public function getTotalProductsCount() {
    $separate_variants = Mage::getStoreConfig('pixlee/advanced/export_variants_separately', Mage::app()->getStore());
    $collection = Mage::getModel('catalog/product')->getCollection();
    $collection->addAttributeToFilter('status', array('neq' => 2));   
    if (!$separate_variants) {
      $collection->addAttributeToFilter('visibility', array('neq' => 1));
    }
        
    $count = $collection->getSize();
    return $count;
  }

  public function getUnexportedCount() {
    $separate_variants = Mage::getStoreConfig('pixlee/advanced/export_variants_separately', Mage::app()->getStore());
    $albumTable = Mage::getSingleton('core/resource')->getTableName('pixlee/product_album');
    $collection = Mage::getModel('catalog/product')->getCollection();
        
    if (!$separate_variants) {
      $collection->addAttributeToFilter('visibility', array('neq' => 1));
    }

    $collection->addAttributeToFilter('status', array('neq' => 2))
      ->getSelect()
      ->joinLeft(array('albums' => $albumTable), 'e.entity_id = albums.product_id')
      ->where('albums.product_id IS NULL')
      ->addAttributeToSelect('*');

    $count = $collection->getSize();
    return $count;
  }

  public function getUnexportedProducts() {
    $separate_variants = Mage::getStoreConfig('pixlee/advanced/export_variants_separately', Mage::app()->getStore());
    $albumTable = Mage::getSingleton('core/resource')->getTableName('pixlee/product_album');
    $collection = Mage::getModel('catalog/product')->getCollection();
        
    if (!$separate_variants) {
      $collection->addAttributeToFilter('visibility', array('neq' => 1));
    }

    $collection->addAttributeToFilter('status', array('neq' => 2))
      ->getSelect()
      ->joinLeft(array('albums' => $albumTable), 'e.entity_id = albums.product_id')
      ->where('albums.product_id IS NULL')
      ->addAttributeToSelect('*');

    return $collection;
  }

  public function notifyExportStatus($status, $job_id, $num_products) {
    $api_key = Mage::getStoreConfig('pixlee/pixlee/account_api_key', Mage::app()->getStore());
    $payload = array(
      'api_key' => $api_key,
      'status' => $status,
      'job_id' => $job_id,
      'num_products' => $num_products,
      'platform' => 'magento_1'
    );

    $ch = curl_init('https://distillery.pixlee.com/api/v1/notifyExportStatus?api_key=' . $api_key);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json'
    ));
    $response = curl_exec($ch);
  }

  public function updateStock($product) {
    $separateVariants = Mage::getStoreConfig('pixlee/advanced/export_variants_separately', Mage::app()->getStore());
    $name = $product->getName();
    $sku = $product->getSku();

    if ($separateVariants) {
      $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
      $aggregateStock = (int) $stockItem;
    } else {
      $aggregateStock = (int) $this->getAggregateStock($product);
    }

    $product = array(
      'name' => $name, 
      'sku' => $sku, 
      'stock' => $aggregateStock
    );
    $pixleeAPI = $this->getNewPixlee();

    if (!$pixleeAPI || is_null($pixleeAPI)) {
      Mage::log("Pixlee: Incorrect credentials filled in configuration. Please check developers.pixlee.com/magento");
      return;
    } else {
        try {
            $productCreated = $pixleeAPI->createProduct($product);
        } catch (Exception $e) {
            Mage::log($product);
            Mage::logException($e);
        }
    }
  }

  public function exportProductToPixlee($product, $categoriesMap, $pixleeAPI) {
    $separateVariants = Mage::getStoreConfig('pixlee/advanced/export_variants_separately', Mage::app()->getStore());
    $imagelessProducts = Mage::getStoreConfig('pixlee/advanced/export_imageless_products', Mage::app()->getStore());
    $id = $product->getId();
    $name = $product->getName();
    $sku = $product->getSku();
    $productPrice = floatval(preg_replace('/[^\d.]/', '', $product->getPrice()));
    $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

    if ($separateVariants) {
      $parentProduct = $this->_extractActualProduct($product);
      $buyNowLinkUrl = $parentProduct->getProductUrl();
      $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
      $aggregateStock = (int) $stockItem;

      if ($product->getImage() == "no_selection") {
        $productPhoto = $this->getImage($parentProduct);
      } else {
        $productPhoto = $this->getImage($product);
      }
    } else {
      $buyNowLinkUrl = $product->getProductUrl();
      $aggregateStock = (int) $this->getAggregateStock($product);
      $productPhoto = $this->getImage($product);
    }

    if (!$imagelessProducts && $productPhoto == Mage::getModel('catalog/product_media_config')->getMediaUrl('')) {
      Mage::log("PIXLEE ERROR: Could not find a valid image url for {$product->getName()}, SKU: {$product->getSku()}");
      return false;
    }
    
    $variantsDict = $this->getVariantsDict($product);
    $extraFields = $this->getExtraFields($product, $categoriesMap);

    $productBody = array(
      'name' => $name, 
      'sku' => $sku, 
      'buy_now_link_url' => $buyNowLinkUrl,
      'product_photo' => $productPhoto, 
      'price' => $productPrice, 
      'stock' => $aggregateStock,
      'native_product_id' => $id, 
      'variants_json' => $variantsDict,
      'extra_fields' => $extraFields, 
      'currency' => $currencyCode
    );

    $productCreated = $pixleeAPI->createProduct($productBody);

    unset($variantsDict);
    unset($extraFields);
    unset($productBody);

    $albumId = 0;
    // Distillery returns the product album on the 'create' verb
    if(isset($productCreated->id)) {
      // Treat $response->id as the created album_id (because that's what it is)
      $album = $this->getPixleeAlbum();
      $album->setProductId($product->getId())->setPixleeAlbumId($productCreated->id);
      $album->save();
      return true;
    } else {
      return false;
    }
  }

}
