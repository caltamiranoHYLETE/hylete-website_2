<?php
class Zaius_Engage_Model_Api extends Mage_Api_Model_Resource_Abstract {

  const DEFAULT_COUPON_QTY = 1;
  const DEFAULT_COUPON_FORMAT = 'alphanum';

  private $categoriesById = null;

  public function customers($jsonOpts) {
    $version = Mage::getConfig()->getNode('modules/Zaius_Engage/version');
    $helper = Mage::helper('zaius_engage');
    list($limit, $offset) = $this->parseOpts($jsonOpts);

    $customerCollection =
      Mage::getModel('customer/customer')->getCollection()
        ->addAttributeToSelect('email')
        ->addAttributeToSelect('firstname')
        ->addAttributeToSelect('lastname')
        ->joinAttribute('billing_street',      'customer_address/street',     'default_billing',  null, 'left')
        ->joinAttribute('billing_city',        'customer_address/city',       'default_billing',  null, 'left')
        ->joinAttribute('billing_region',      'customer_address/region',     'default_billing',  null, 'left')
        ->joinAttribute('billing_postcode',    'customer_address/postcode',   'default_billing',  null, 'left')
        ->joinAttribute('billing_country_id',  'customer_address/country_id', 'default_billing',  null, 'left')
        ->joinAttribute('billing_telephone',   'customer_address/telephone',  'default_billing',  null, 'left')
        ->joinAttribute('shipping_street',     'customer_address/street',     'default_shipping', null, 'left')
        ->joinAttribute('shipping_city',       'customer_address/city',       'default_shipping', null, 'left')
        ->joinAttribute('shipping_region',     'customer_address/region',     'default_shipping', null, 'left')
        ->joinAttribute('shipping_postcode',   'customer_address/postcode',   'default_shipping', null, 'left')
        ->joinAttribute('shipping_country_id', 'customer_address/country_id', 'default_shipping', null, 'left')
        ->joinAttribute('shipping_telephone',  'customer_address/telephone',  'default_shipping', null, 'left')
        ->addAttributeToSort('entity_id');
    $customerCollection->getSelect()->limit($limit, $offset);

    $customers = array();
    foreach ($customerCollection as $mageCustomer) {
      $customerData = $mageCustomer->getData();
      $email = $customerData['email'];

      $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
      $isSubscribed = false;
      if($subscriber->getId())
      {
        $isSubscribed = $subscriber->getData('subscriber_status') == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
      }

      $customer = array(
        'email'       => $email,
        'first_name'  => $customerData['firstname'],
        'last_name'   => $customerData['lastname'],
        'subscribed'  => $isSubscribed
      );

      $customerId = $helper->getCustomerID($mageCustomer->getId());
      if (!empty($customerId)) {
        $customer['customer_id'] = $customerId;
      }

      $addressType = null;
      if (isset($customerData['default_billing']) && $customerData['default_billing'] != null) {
        $addressType = 'billing';
      } else if (isset($customerData['default_shipping']) && $customerData['default_shipping'] != null) {
        $addressType = 'shipping';
      }
      if ($addressType != null) {
        $streetParts         = mb_split('\R', (isset($customerData["${addressType}_street"]) ? $customerData["${addressType}_street"] : ''));
        $customer['street1'] = $streetParts[0];
        $customer['street2'] = count($streetParts) > 1 ? $streetParts[1] : '';
        $customer['city']    = $customerData["${addressType}_city"];
        $customer['state']   = $customerData["${addressType}_region"];
        $customer['zip']     = $customerData["${addressType}_postcode"];
        $customer['country'] = $customerData["${addressType}_country_id"];
        $customer['phone']   = $customerData["${addressType}_telephone"];
      }
      $customer['zaius_engage_version'] = $version;
      $customers[] = array(
        'type' => 'customer',
        'data' => $customer
      );
    }

    return json_encode($customers);
  }

  public function products($jsonOpts) {
    $version = Mage::getConfig()->getNode('modules/Zaius_Engage/version');
    list($limit, $offset) = $this->parseOpts($jsonOpts);

    $productCollection = null;
    if (Mage::helper('zaius_engage')->isCollectAllProductAttributes()) {
      $productCollection =
        Mage::getModel('catalog/product')->getCollection()
          ->addAttributeToSelect('id')
          ->addAttributeToSort('entity_id');
    } else {
      $productCollection =
        Mage::getModel('catalog/product')->getCollection()
          ->addAttributeToSelect('name')
          ->addAttributeToSelect('sku')
          ->addAttributeToSelect('price')
          ->addAttributeToSelect('special_price')
          ->addAttributeToSelect('special_from_date')
          ->addAttributeToSelect('special_to_date')
          ->addAttributeToSelect('short_description')
          ->addAttributeToSelect('image')
          ->addAttributeToSelect('manufacturer')
          ->addAttributeToSort('entity_id');
    }
    $productCollection->getSelect()->limit($limit, $offset);

    $categoryCollection =
      Mage::getModel('catalog/category')->getCollection()
        ->addAttributeToSelect('name');
    $this->categoriesById = array();
    foreach ($categoryCollection as $mageCategory) {
      $this->categoriesById[$mageCategory->getId()] = $mageCategory;
    }

    $mediaConfigHelper = Mage::getModel('catalog/product_media_config');
    $products = array();
    foreach ($productCollection as $mageProductForId) {
      $mageProduct = $mageProductForId;
      $product = array();
      $helper = Mage::helper('zaius_engage');
      if ($helper->isCollectAllProductAttributes()) {
        $mageProduct = Mage::getModel('catalog/product')->load($mageProductForId->getId());
        $product = Zaius_Engage_Model_ProductAttribute::getAttributes($mageProduct);
      }
      $product['product_id']  = $helper->getProductID($mageProduct->getId());
      $product['name']        = $mageProduct->getName();
      $product['sku']         = $mageProduct->getSku();
      $product['description'] = $mageProduct->getShortDescription();
      $product['image_url']   = $mediaConfigHelper->getMediaUrl($mageProduct->getImage());
      $product['category']    = $this->getDeepestCategoryPath($mageProduct);
      if ($mageProduct->getManufacturer()) {
        $product['brand'] = $mageProduct->getAttributeText('manufacturer');
      }
      if ($mageProduct->getPrice()) {
        $product['price'] = $mageProduct->getPrice();
      }
      if ($mageProduct->getSpecialPrice()) {
        $product['special_price'] = $mageProduct->getSpecialPrice();
        if ($mageProduct->getSpecialFromDate()) {
          $product['special_price_from_date'] = strtotime($mageProduct->getSpecialFromDate());
        }
        if ($mageProduct->getSpecialToDate()) {
          $product['special_price_to_date'] = strtotime($mageProduct->getSpecialToDate());
        }
      }
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->loadByProduct($mageProduct);
        if ($stockItem && $stockItem->getId() && $stockItem->getManageStock()) {
            $product['qty'] = $stockItem->getQty();
            $product['is_in_stock'] = $stockItem->getIsInStock();
        }
      $product['zaius_engage_version'] = $version;
      $products[] = array(
        'type' => 'product',
        'data' => $product
      );
    }

    return json_encode($products);
  }

  public function orders($jsonOpts) {
    $version = Mage::getConfig()->getNode('modules/Zaius_Engage/version');
    $helper = Mage::helper('zaius_engage');
    list($limit, $offset) = $this->parseOpts($jsonOpts);

    $orderCollection =
      Mage::getModel('sales/order')->getCollection()
        ->addAttributeToSort('entity_id');
    $orderCollection->getSelect()->limit($limit, $offset);

    $orders = array();
    foreach ($orderCollection as $mageOrder) {
      $ip = '';
      if ($mageOrder->getXForwardedFor()) {
        $ip = $mageOrder->getXForwardedFor();
      } else if ($mageOrder->getRemoteIp()) {
        $ip = $mageOrder->getRemoteIp();
      }
      $event = array(
        'action'             => 'purchase',
        'ts'                 => strtotime($mageOrder->getCreatedAt()),
        'ip'                 => $ip,
        'ua'                 => '',
        'order'              => $helper->buildOrder($mageOrder)
      );

      try {
       $store = $mageOrder->getStore();
       if ($store) {
         if ($store->getWebsite()) {
           $event['magento_website'] = $store->getWebsite()->getName();
         }
         if ($store->getGroup()) {
           $event['magento_store'] = $store->getGroup()->getName();
         }
         $event['magento_store_view'] = $store->getName();
       }
      } catch (Mage_Core_Model_Store_Exception $e) { }
      $customerId = $mageOrder->getCustomerId();
      $customerIdToUse = $helper->getCustomerID($customerId);
      if (!empty($customerIdToUse)) {
        $event['customer_id'] = $customerIdToUse;
      } elseif ($mageOrder->getCustomerEmail()) {
        $event['email'] = $mageOrder->getCustomerEmail();
      }
      $event['zaius_engage_version'] = $version;
      $orders[] = array(
        'type' => 'order',
        'data' => $event
      );

      if ($mageOrder->getTotalRefunded() > 0) {
        $event['action'] = 'refund';
        $event['order'] = $helper->buildOrderNegation(
          $mageOrder, $mageOrder->getTotalRefunded() * -1);
        $event['zaius_engage_version'] = $version;
        $orders[] = array(
          'type' => 'order',
          'data' => $event
        );
      } elseif ($mageOrder->getTotalCanceled() > 0) {
        $event['action'] = 'cancel';
        $event['order'] = $helper->buildOrderNegation(
          $mageOrder, $mageOrder->getTotalCanceled() * -1);
        $event['zaius_engage_version'] = $version;
        $orders[] = array(
          'type' => 'order',
          'data' => $event
        );
      }
    }

    return json_encode($orders);
  }

  private function parseOpts($jsonOpts) {
    $opts = json_decode($jsonOpts, true);
    $limit = null;
    if (!isset($opts['limit']) || is_null($opts['limit']) || !is_numeric($opts['limit'])) {
      Mage::throwException('Must specify valid limit');
    } else {
      $limit = intval($opts['limit']);
    }
    $offset = 0;
    if (isset($opts['offset'])) {
      if (!is_null($opts['offset']) && is_numeric($opts['offset'])) {
        $offset = intval($opts['offset']);
      } else {
        Mage::throwException('Invalid offset');
      }
    }
    return array($limit, $offset);
  }

  private function getDeepestCategory($product) {
    $maxDepth = -1;
    $deepestCategory = null;
    $categoryIds = $product->getCategoryIds();
    if ($categoryIds) {
      foreach ($categoryIds as $categoryId) {
        $category = $this->categoriesById[$categoryId];
        if ($category) {
          $depth = count(explode('/', $category->getPath()));
          if ($depth > $maxDepth) {
            $maxDepth = $depth;
            $deepestCategory = $category;
          }
        }
      }
    }
    return $deepestCategory;
  }

  private function getDeepestCategoryPath($product) {
    $category = $this->getDeepestCategory($product);
    if ($category) {
      return $this->buildCategoryPath($category->getId());
    }
    return null;
  }

  private function buildCategoryPath($catId) {
    $catPath = '';
    $cat = $this->categoriesById[$catId];
    if ($cat) {
      $catIds = explode('/', $cat->getPath());
      $numCats = count($catIds) - 1;
      $i = 0;
      foreach (array_slice($catIds, 1) as $catId) {
        $cat = $this->categoriesById[$catId];
        if ($cat) {
          $catPath .= $cat->getName();
          if (++$i < $numCats) {
            $catPath .= ' > ';
          }
        }
      }
    }
    return $catPath;
  }

  public function createCoupons($jsonOpts)
  {
      /** @var Mage_SalesRule_Helper_Coupon $helper */
      $helper = Mage::helper('salesrule/coupon');
      $version = (string)Mage::getConfig()->getNode('modules/Zaius_Engage/version');
      $opts = json_decode($jsonOpts, true);
      $ruleId = isset($opts['rule_id'])? intval($opts['rule_id']) : 0;
      $format = isset($opts['format'])? $opts['format'] : self::DEFAULT_COUPON_FORMAT;
      $qty = isset($opts['qty'])? intval($opts['qty']): self::DEFAULT_COUPON_QTY;
      $length = isset($opts['length'])? intval($opts['length']) : $helper->getDefaultLength();
      $delimiter = isset($opts['delimiter'])? $opts['delimiter'] : $helper->getCodeSeparator();
      $dash = isset($opts['dash'])? intval($opts['dash']) : $helper->getDefaultDashInterval();
      $prefix = isset($opts['prefix'])? $opts['prefix'] : $helper->getDefaultPrefix();
      $suffix = isset($opts['suffix'])? $opts['suffix'] : $helper->getDefaultSuffix();

      /** @var Mage_SalesRule_Model_Rule $rule */
      $rule = Mage::getModel('salesrule/rule')->load($ruleId);
      if (!$rule || !$rule->getId()) {
          Mage::throwException('No salesrule exists with id ' . $ruleId);
      }
      if (
          $rule->getCouponType() == Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON
          || $rule->getCouponType() == Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC
          && !$rule->getUseAutoGeneration()
      ) {
          Mage::throwException('Cannot auto-generate coupons for this rule.');
      }
      $massGenerator = $rule->getCouponMassGenerator();
      $massGenerator->setRuleId($ruleId)
          ->setFormat($format)
          ->setQty(1)
          ->setLength($length)
          ->setDelimiter($delimiter)
          ->setDash($dash)
          ->setPrefix($prefix)
          ->setSuffix($suffix);
      Mage_SalesRule_Model_Rule::setCouponCodeGenerator($massGenerator);

      $codes = array();
      for ($i = 0; $i < $qty; ++$i) {
          $coupon = $this->_acquireCoupon($rule);
          $codes[] = $coupon->getCode();
      }
      $event = array(
          'type' => 'coupon',
          'data' => array(
              'zaius_engage_version' => $version,
              'codes' => $codes
          )
      );
      return json_encode($event);
  }

  protected function _acquireCoupon($rule, $saveNewlyCreated = true, $saveAttemptCount = 10)
  {
      /** @var Mage_SalesRule_Model_Coupon $coupon */
      $coupon = Mage::getModel('salesrule/coupon');
      $coupon->setRule($rule)
          ->setIsPrimary(false)
          ->setUsageLimit($rule->getUsesPerCoupon() ? $rule->getUsesPerCoupon() : null)
          ->setUsagePerCustomer($rule->getUsesPerCustomer() ? $rule->getUsesPerCustomer() : null)
          ->setExpirationDate($rule->getToDate());

      $couponCode = Mage_SalesRule_Model_Rule::getCouponCodeGenerator()->generateCode();
      $coupon->setCode($couponCode);

      $ok = false;
      if (!$saveNewlyCreated) {
          $ok = true;
      } else if ($rule->getId()) {
          for ($attemptNum = 0; $attemptNum < $saveAttemptCount; $attemptNum++) {
              try {
                  $coupon->save();
              } catch (Exception $e) {
                  if ($e instanceof Mage_Core_Exception || $coupon->getId()) {
                      throw $e;
                  }
                  $coupon->setCode(
                      $couponCode .
                      Mage_SalesRule_Model_Rule::getCouponCodeGenerator()->getDelimiter() .
                      sprintf('%04u', rand(0, 9999))
                  );
                  continue;
              }
              $ok = true;
              break;
          }
      }
      if (!$ok) {
          Mage::throwException(Mage::helper('salesrule')->__('Can\'t acquire coupon.'));
      }

      return $coupon;
  }

  public function subscribers($jsonOpts) {
    $version = Mage::getConfig()->getNode('modules/Zaius_Engage/version');
    $helper = Mage::helper('zaius_engage');
    list($limit, $offset) = $this->parseOpts($jsonOpts);

    $subscriberCollection = Mage::getModel('newsletter/subscriber')->getCollection()->setOrder('subscriber_id');
    $subscriberCollection->getSelect()->limit($limit, $offset);

    $subscribers = array();
    foreach ($subscriberCollection as $subscriber) {
      $data = $subscriber->getData();
      $entry = array(
        'email'      => $data['subscriber_email'],
        'list_id'    => $helper->getNewsletterListID(),
        'subscribed' => ($data['subscriber_status'] == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
      );

      $entry['zaius_engage_version'] = $version;
      $subscribers[] = array(
        'type' => 'subscriber',
        'data' => $entry
      );
    }

    return json_encode($subscribers);
  }
}
