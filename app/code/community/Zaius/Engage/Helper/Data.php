<?php
class Zaius_Engage_Helper_Data extends Mage_Core_Helper_Abstract {

  const VUID_LENGTH = 36;

  public function isEnabled() {
    return Mage::helper('core/data')->isModuleEnabled('Zaius_Engage');
  }

  public function getTrackingID() {
    return trim(Mage::getStoreConfig('zaius_engage/zaius_config/tracking_id'));
  }

  public function getGlobalIDPrefix() {
    return trim(Mage::getStoreConfig('zaius_engage/zaius_config/global_id_prefix'));
  }

  public function getNewsletterListID() {
    $listId = trim(Mage::getStoreConfig('zaius_engage/zaius_config/zaius_newsletter_list_id'));
    if (empty($listId)) {
      $listId = 'newsletter';
    }
    $storeName = trim(Mage::app()->getStore()->getGroup()->getName());
    $storeName = mb_strtolower($storeName, mb_detect_encoding($storeName));
    $storeName = mb_ereg_replace('\s+', '_', $storeName);
    $storeName = mb_ereg_replace('[^a-z0-9_\.\-]', '', $storeName);
    $listId = $storeName . '_' . $listId;
    return $this->applyGlobalIDPrefix($listId);
  }

  public function isUseMagentoCustomerID() {
    return Mage::getStoreConfigFlag('zaius_engage/zaius_config/use_magento_customer_id');
  }

  public function isTrackProductListings() {
    return Mage::getStoreConfigFlag('zaius_engage/zaius_config/track_product_listings');
  }

  public function isTrackOrdersOnFrontend() {
    return Mage::getStoreConfigFlag('zaius_engage/zaius_config/track_orders_on_frontend');
  }

  public function isCollectAllProductAttributes() {
    return Mage::getStoreConfigFlag('zaius_engage/zaius_config/collect_all_product_attributes');
  }

  public function getVUID() {
    $vuid = null;
    if (!Mage::app()->getStore()->isAdmin()) {
      $vuidCookie = Mage::getModel('core/cookie')->get('vuid');
      if ($vuidCookie && strlen($vuidCookie) >= self::VUID_LENGTH) {
        $vuid = substr($vuidCookie, 0, self::VUID_LENGTH);
      }
    }
    return $vuid;
  }

  public function addCustomerId($customerId, &$data) {
    $customerIdToUse = $this->getCustomerID($customerId);
    if (!empty($customerIdToUse)) {
      $data['customer_id'] = $customerIdToUse;
    }
  }

  public function addCustomerIdOrEmail($customerId, &$data) {
    $customerIdToUse = $this->getCustomerID($customerId);
    if (!empty($customerIdToUse)) {
      $data['customer_id'] = $customerIdToUse;
    } elseif (!empty($customerId)) {
      $customer = Mage::getModel('customer/customer')->load($customerId);
      if ($customer) {
        $customerEmail = $customer->getEmail();
        if (!empty($customerEmail)) {
          $data['email'] = $customer->getEmail();
        }
      }
    }
  }

  public function getCustomerID($customerId) {
    $customerIdToUse = null;
    if ($this->isUseMagentoCustomerID()) {
      $customerIdToUse = $this->applyGlobalIDPrefix($customerId);
    }
    return $customerIdToUse;
  }

  public function getProductID($productId) {
    return $this->applyGlobalIDPrefix($productId);
  }

  public function getOrderID($orderId) {
    return $this->applyGlobalIDPrefix($orderId);
  }

  public function applyGlobalIDPrefix($idToPrefix) {
    $prefix = $this->getGlobalIDPrefix();
    if (!empty($prefix) && !empty($idToPrefix)) {
      $idToPrefix = $prefix . $idToPrefix;
    }
    return $idToPrefix;
  }

  public function buildCategoryPath($catId) {
    $catPath = '';
    $cat = Mage::getModel('catalog/category')->load($catId);
    $catIds = explode('/', $cat->getPath());
    $numCats = count($catIds) - 1;
    $i = 0;
    foreach (array_slice($catIds, 1) as $catId) {
      $cat = Mage::getModel('catalog/category')->load($catId);
      $catPath .= $cat->getName();
      if (++$i < $numCats) {
        $catPath .= ' > ';
      }
    }
    return $catPath;
  }

  public function buildOrder($mageOrder) {
    $order = array(
      'order_id'    => $this->getOrderID($mageOrder->getIncrementId()),
      'total'       => $mageOrder->getBaseGrandTotal(),
      'subtotal'    => $mageOrder->getBaseSubtotal(),
      'coupon_code' => $mageOrder->getCouponCode(),
      'discount'    => $mageOrder->getBaseDiscountAmount() * -1,
      'tax'         => $mageOrder->getBaseTaxAmount(),
      'shipping'    => $mageOrder->getBaseShippingAmount(),
      'currency' => $mageOrder->getBaseCurrencyCode(),
      'native_total' => $mageOrder->getGrandTotal(),
      'native_subtotal' => $mageOrder->getSubtotal(),
      'native_discount' => $mageOrder->getDiscountAmount() * -1,
      'native_tax' => $mageOrder->getTaxAmount(),
      'native_shipping' => $mageOrder->getShippingAmount(),
      'native_currency' => $mageOrder->getCurrencyCode()
    );
    if ($mageOrder->getBillingAddress() != null) {
      $billAddress           = $mageOrder->getBillingAddress()->getData();
      $order['bill_address'] = $this->formatAddress($billAddress);
      $order['email']        = $billAddress['email'];
      $order['phone']        = $billAddress['telephone'];
      $order['first_name']   = $billAddress['firstname'];
      $order['last_name']    = $billAddress['lastname'];
    }
    if ($mageOrder->getShippingAddress() != null) {
      $order['ship_address'] = $this->formatAddress($mageOrder->getShippingAddress()->getData());
    }
    if ($order['email'] == null && $mageOrder->getCustomerEmail() != null) {
      $order['email'] = $mageOrder->getCustomerEmail();
    }
    $order['items'] = array();
    foreach ($mageOrder->getAllVisibleItems() as $mageItem) {
      $order['items'][] = array(
        'product_id' => $this->getProductID($mageItem->getProductId()),
        'subtotal'   => $mageItem->getBaseRowTotal(),
        'sku'        => $mageItem->getSku(),
        'quantity'   => $mageItem->getQtyOrdered(),
        'price'      => $mageItem->getBasePrice(),
        'discount'   => $mageItem->getBaseDiscountAmount() * -1,
        'native_subtotal' => $mageItem->getRowTotal(),
        'native_price' => $mageItem->getPrice(),
        'native_discount' => $mageItem->getDiscountAmount() * -1
      );
    }
    return $order;
  }

  public function buildOrderCancel($mageOrder, $magePayment) {
    return $this->buildOrderNegation($mageOrder, $magePayment->getBaseAmountOrdered() * -1, $magePayment->getAmountOrdered() * -1);
  }

  public function buildOrderRefund($mageOrder, $mageCreditmemo) {
    return $this->buildOrderNegation($mageOrder, $mageCreditmemo->getBaseGrandTotal() * -1, $mageCreditmemo->getGrandTotal() * -1);
  }

  public function formatAddress($address) {
    $street = '';
    if (isset($address['street'])) {
      $street = mb_ereg_replace('\R', ", ", $address['street']);
    }
    return "$street, ${address['city']}, ${address['region']}, ${address['postcode']}, ${address['country_id']}";
  }

  public function buildOrderNegation($mageOrder, $refundAmount, $nativeRefundAmount) {
    $refundAmountStr = sprintf("%0.4f", $refundAmount);
    $nativeRefundAmountStr = sprintf("%0.4f", $nativeRefundAmount);
    $order = array(
      'order_id'    => $this->getOrderID($mageOrder->getIncrementId()),
      'total'       => $refundAmountStr,
      'subtotal'    => $refundAmountStr,
      'currency' => $mageOrder->getBaseCurrencyCode(),
      'native_total' => $nativeRefundAmountStr,
      'native_subtotal' => $nativeRefundAmountStr,
      'native_currency' => $mageOrder->getCurrencyCode()
    );
    if ($mageOrder->getBillingAddress() != null) {
      $billAddress           = $mageOrder->getBillingAddress()->getData();
      $order['email']        = $billAddress['email'];
      $order['phone']        = $billAddress['telephone'];
      $order['first_name']   = $billAddress['firstname'];
      $order['last_name']    = $billAddress['lastname'];
    }
    if ($order['email'] == null && $mageOrder->getCustomerEmail() != null) {
      $order['email'] = $mageOrder->getCustomerEmail();
    }
    return $order;
  }


  public function computeQuoteHashV3($quote) {
    $secret = trim(Mage::getStoreConfig('zaius_engage/zaius_config/cart_abandon_secret_key'));
    if ($quote == null || $quote->getId() == null || $secret == '' || $quote->getStoreId() == null) {
      return null;
    } else {
      return base64_encode(md5($quote->getId().$secret.$quote->getStoreId()));
    }
  }

  public function computeQuoteHashV2($quote) {
    if ($quote == null || $quote->getId() == null || $quote->getCreatedAt() == null || $quote->getStoreId() == null) {
      return null;
    } else {
      return base64_encode(md5($quote->getId().$quote->getCreatedAt().$quote->getStoreId()));
    }
  }

  public function computeQuoteHashV1($quote) {
    if ($quote == null || $quote->getId() == null || $quote->getCreatedAt() == null || $quote->getStoreId() == null) {
      return null;
    } else {
      return base64_encode(Mage::helper('core')->encrypt($quote->getId().$quote->getCreatedAt().$quote->getStoreId()));
    }
  }
}
