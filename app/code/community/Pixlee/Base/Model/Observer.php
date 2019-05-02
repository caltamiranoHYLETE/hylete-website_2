<?php
class Pixlee_Base_Model_Observer {

  const ANALYTICS_BASE_URL = 'https://inbound-analytics.pixlee.com/events/';
  protected $_urls = array();

  public function __construct() {
    // Prepare URLs used to ping Pixlee analytics server
    $this->_urls['addToCart'] = self::ANALYTICS_BASE_URL . 'addToCart';
    $this->_urls['removeFromCart'] = self::ANALYTICS_BASE_URL . 'removeFromCart';
    $this->_urls['checkoutStart'] = self::ANALYTICS_BASE_URL . 'checkoutStart';
    $this->_urls['checkoutSuccess'] = self::ANALYTICS_BASE_URL . 'conversion';
  }

  // Analytics
  // ADD PRODUCT TO CART
  public function addToCart(Varien_Event_Observer $observer) {
    if (!$this->_checkAnalyticsEnabled())
        return;

    $product = $observer->getEvent()->getProduct();
    $websiteCode = Mage::app()->getStore()->getWebsite()->getCode();
    $productData = $this->_extractProduct($product);
    $payload = $this->_preparePayload($productData, $websiteCode);
    $this->_sendPayload('addToCart', $payload);
  }

  // CHECKOUT SUCCESS
  public function checkoutSuccess(Varien_Event_Observer $observer) {
    if (!$this->_checkAnalyticsEnabled())
        return;

    $quote = new Mage_Sales_Model_Order();
    $incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
    $websiteCode = Mage::app()->getStore()->getWebsite()->getCode();
    $quote->loadByIncrementId($incrementId);
    $cartData = $this->_extractCart($quote);
    $cartData['cart_type'] = 'magento';
    $customerData = $this->_extractCustomer($quote);
    $payload = array_merge($cartData, $customerData);
    $payload = $this->_preparePayload($payload, $websiteCode);
    $this->_sendPayload('checkoutSuccess', $payload);
  }

  public function scheduledExportProducts() {
    Mage::log("Pixlee::Exporting all products");
    $helper = Mage::helper('pixlee');
    $categoriesMap = $helper->getCategoriesMap();

    foreach (Mage::app()->getWebsites() as $website) {
      $websiteId = $website->getId();
      $separateVariants = Mage::app()->getWebsite($websiteId)->getConfig('pixlee/advanced/export_variants_separately');
      $pixleeAPI = $helper->getNewPixlee($websiteId);
      if (!$pixleeAPI || is_null($pixleeAPI)) {
        continue;
      }
      $numProducts = $helper->getTotalProductsCount($websiteId);
      $limit = 100;
      $offset = 0;

      while ($offset < $numProducts) {
        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addAttributeToFilter('status', array('neq' => 2));
        $products->addWebsiteFilter($websiteId);

        if (!$separateVariants) {
          $products->addAttributeToFilter('visibility', array('neq' => 1));
        }
        $products->getSelect()->limit($limit, $offset);
        $products->addAttributeToSelect('*');
        $offset = $offset + $limit;

        foreach ($products as $product) {
          $productCreated = $helper->exportProductToPixlee($product, $categoriesMap, $pixleeAPI, $websiteId);
        }

        unset($products);
      }
    }
  }

  // VALIDATE CREDENTIALS
  public function validateCredentials(Varien_Event_Observer $observer){
    $websiteCode = $observer->getEvent()->getData('website');
    $websiteId = Mage::getModel('core/website')->load($websiteCode)->getId();
    $pixleeAccountApiKey = Mage::app()->getWebsite($websiteId)->getConfig('pixlee/pixlee/account_api_key');
    $pixleeAccountSecretKey = Mage::app()->getWebsite($websiteId)->getConfig('pixlee/pixlee/account_secret_key');

    $this->_pixleeAPI = new Pixlee_Pixlee($pixleeAccountApiKey, $pixleeAccountSecretKey);
    try{
      $this->_pixleeAPI->getAlbums();
    }catch(Exception $e){
      Mage::getSingleton("adminhtml/session")->addWarning("The API credentials seem to be wrong. Please check again.");
    }
  }


  // Helper functions

  // Shorthand for having to check the config every time
  protected function _checkAnalyticsEnabled() {
    $pixleeAccountId = Mage::app()->getWebsite()->getConfig('pixlee/pixlee/account_id');
    $pixleeAnalyticsEnabled = Mage::app()->getWebsite()->getConfig('pixlee/advanced/enable_analytics');

    if (is_null($pixleeAccountId) || $pixleeAccountId == 0 || $pixleeAccountId == '') {
      return false;
    } else {
      if ($pixleeAnalyticsEnabled) {
        return true;
      } else {
        return false;
      }
    }
  }

  protected function _getPixleeCookie() {
    if(isset($_COOKIE['pixlee_analytics_cookie'])){
      if($cookie = $_COOKIE['pixlee_analytics_cookie']) {
        // Return the decoded cookie as an associative array, not a PHP object
        // as json_decode prefers.
        return json_decode($cookie, true);
      }
    }
    return false;
  }

  /**
   * Build a payload from the Pixlee provided cookie, appending extra data not
   * provided by the cookie by default (e.g. API key and User ID).
   **/
  protected function _preparePayload($extraData = array(), $websiteCode) {
    $helper = Mage::helper('pixlee');

    Mage::log("* In _preparePayload");
    if(($payload = $this->_getPixleeCookie()) && $helper->isActive()) {
      // Append all extra data to the payload
      foreach($extraData as $key => $value) {
        // Don't accidentally overwrite existing data.
        if(!isset($payload[$key])) {
          $payload[$key] = $value;
        }
      }
      Mage::log("** Before building payload");
      // Required key/value pairs not in the payload by default.
      $payload['API_KEY']= Mage::app()->getWebsite()->getConfig('pixlee/pixlee/account_api_key');
      $payload['uid'] = $payload['CURRENT_PIXLEE_USER_ID'];
      Mage::log("** After building payload");
      $payload['ecommerce_platform'] = 'magento_1';
      $payload['ecommerce_platform_version'] = '2.0.0';
      $payload['region_code'] = $websiteCode;
      $payload['version_hash'] = $this->_getVersionHash();
      return json_encode($payload);
    }
    return false; // No cookie exists,
  }

  protected function _sendPayload($event, $payload) {
    Mage::log("* In _sendPayload. Event: $event");
    Mage::log("** Payload: $payload");
    if($payload && isset($this->_urls[$event])) {
      // I'm reading that curl won't actually raise an exception, but rather
      // it'll just return false - however, this couldn't hurt
      try {
        $ch = curl_init($this->_urls[$event]);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // If the Pixlee server doesn't respond after 3 seconds, close the connection
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

        // Set User Agent
        if(isset($_SERVER['HTTP_USER_AGENT'])){
          curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }

        $response   = curl_exec($ch);
        $responseInfo   = curl_getinfo($ch);
        $responseCode   = $responseInfo['http_code'];
        curl_close($ch);

        if( !$this->isBetween($responseCode, 200, 299) ) {
          Mage::log("HTTP $responseCode response from Pixlee API", Zend_Log::ERR);
        } elseif ( is_object($response) && is_null( $response->status ) ) {
          Mage::log("Pixlee did not return a status", Zend_Log::ERR);
        } elseif( is_object($response) && !$this->isBetween( $response->status, 200, 299 ) ) {
          $errorMessage   = implode(',', (array)$response->message);
          Mage::log("$response->status - $errorMessage ", Zend_Log::ERR);
        } else {
          return true;
        }
      } catch (Exception $e) {
        Mage::log("PIXLEE ERROR: " . $e->getMessage());
      }
    }
    return false;
  }

  protected function _extractProduct($product) {
    $productData = array();
    Mage::log("* In _extractProduct");
    if(is_a($product, 'Mage_Sales_Model_Quote_Item')) {
      $productData['quantity'] = (int) $product->getQty();
      $product = $product->getProduct();
    } else if(is_a($product, 'Mage_Sales_Model_Order_Item')) {
      // BUGZ-1081: We used to have getQtyToInvoice() here, but it seems Goorin
      // has maybe...and auto-invoice extension maybe?
      $productData['quantity'] = (int) $product->getQtyOrdered();
      $product = $product->getProduct();
    } else {
      $productData['quantity'] = (int) $product->getQty();
    }

    if($product->getId()) {
      $productData['variant_id'] = (int) $product->getIdBySku($product->getSku());
      $productData['variant_sku'] = $product->getSku();
      $productData['price'] = Mage::helper('core')->currency($product->getPrice(), true, false); // Get price in the main currency of the store. (USD, EUR, etc.)
      $productData['currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();
    }

    // Moving _extractActualProduct into the helper, because I want to use it
    // in product exports (when a 'simple' type product is modified in the dashboard)
    // but don't want to duplicate code
    $helper = Mage::helper('pixlee');

    // Here we need to know whether to pass back the parent product SKU
    // or the variant product SKU, based on 'export_variants_separately'
    $separateVariants = Mage::app()->getWebsite()->getConfig('pixlee/advanced/export_variants_separately');

    if ($separateVariants) {
      $productData['product_id'] = (int) $product->getIdBySku($product->getSku());
      $productData['product_sku'] = $product->getSku();
    } else {
      $product = $helper->_extractActualProduct($product);
      $productData['product_id'] = (int) $product->getId();
      $productData['product_sku'] = $product->getSku();
    }
    return $productData;
  }

  protected function _extractCart($quote) {
    $cartData = array('cart_contents' => array());

    if(is_a($quote, 'Mage_Sales_Model_Quote')) {
      foreach ($quote->getAllVisibleItems() as $item) {
        $cartData['cart_contents'][] = $this->_extractProduct($item);
      }
      $cartData['cart_total'] = Mage::helper('core')->currency($quote->getGrandTotal(), true, false);
      $cartData['cart_total_quantity'] = round($quote->getItemsQty());
      return $cartData;
    } else if(is_a($quote, 'Mage_Sales_Model_Order')) {
      foreach ($quote->getAllVisibleItems() as $item) {
        $cartData['cart_contents'][] = $this->_extractProduct($item);
      }
      $cartData['cart_total'] = Mage::helper('core')->currency($quote->getGrandTotal(), true, false);
      $cartData['cart_total_quantity'] = round($quote->getTotalQtyOrdered());
      return $cartData;
    }

    return false;
  }

  protected function _extractCustomer($quote) {
    if(is_a($quote, 'Mage_Sales_Model_Quote') || is_a($quote, 'Mage_Sales_Model_Order')) {

      $result = array();

      if (method_exists($quote, 'getShippingAddress')) {
        $shippingAddress = $quote->getShippingAddress();
        if (!empty($shippingAddress)) {
          $email = $shippingAddress->getEmail();
          if (!$email && method_exists($quote, 'getCustomerEmail')) $email = $quote->getCustomerEmail();
        }
        $result['email'] = $email;
      }

      if (method_exists($quote, 'getCustomerId')) { $
        $result['customer_id'] = $quote->getCustomerId();
      }

      if (method_exists($quote, 'getRealOrderId')) {
        $result['order_id'] = $quote->getRealOrderId();
      }

      if (method_exists($quote, 'getOrderCurrencyCode')) {
        $result['currency'] = $quote->getOrderCurrencyCode();
      }

      return $result;
    } else {
      return false;
    }
  }

  protected function isBetween($theNum, $low, $high){
    if($theNum >= $low && $theNum <= $high) {
      return true;
    } else {
      return false;
    }
  }

  protected function _getVersionHash() {
    $version_hash = file_get_contents(Mage::getModuleDir('', 'Pixlee_Base').'/version.txt');
    $version_hash = str_replace(array("\r", "\n"), '', $version_hash);
    return $version_hash;
  }

}
