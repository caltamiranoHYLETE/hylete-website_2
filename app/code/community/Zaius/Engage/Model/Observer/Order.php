<?php
class Zaius_Engage_Model_Observer_Order extends Zaius_Engage_Model_Observer {

  public function orderPlaced($observer) {
    $zaiusEngage = Mage::helper('zaius_engage');
    if ($zaiusEngage->isEnabled() && $zaiusEngage->isTrackOrdersOnFrontend() && !Mage::app()->getStore()->isAdmin()) {
      $mageOrder = $observer->getOrder();
      if ($mageOrder == null) {
        Mage::log('ZAIUS: Unable to retrieve order information. Please contact your Zaius rep for support.');
      } else {
         $event = array(
          'action' => 'purchase',
          'order' => $zaiusEngage->buildOrder($mageOrder),
        );
        Zaius_Engage_Model_BlockManager::getInstance()->addEvent('order', $event);
      }
    }
  }

  public function orderSaved($observer) {
    $zaiusEngage = Mage::helper('zaius_engage');
    if ($zaiusEngage->isEnabled() && (!$zaiusEngage->isTrackOrdersOnFrontend() || Mage::app()->getStore()->isAdmin())) {
      $mageOrder = $observer->getOrder();
      if ($mageOrder == null) {
        Mage::log('ZAIUS: Unable to retrieve order information. Please contact your Zaius rep for support.');
      } else {
        $goalStates = array($mageOrder::STATE_PROCESSING, $mageOrder::STATE_COMPLETE);
        if (in_array($mageOrder->getState(), $goalStates) && !in_array($mageOrder->getOrigData('state'), $goalStates)) {
          $this->postBackendOrder($mageOrder, $zaiusEngage->getVUID());
        }
      }
    }
  }

  public function cancel($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled()) {
      $magePayment = $observer->getPayment();
      $mageOrder = $magePayment->getOrder();
      if ($mageOrder == null) {
        Mage::log('ZAIUS: Unable to retrieve order information. Please contact your Zaius rep for support.');
      }
      $event = array(
        'action'      => 'cancel',
        'order'       => $helper->buildOrderCancel($mageOrder, $magePayment)
      );
      $helper->addCustomerIdOrEmail($mageOrder->getCustomerId(), $event);
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
      $this->postEvent('order', $event);
    }
  }

  public function refund($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled()) {
      $mageCreditmemo = $observer->getCreditmemo();
      $magePayment    = $observer->getPayment();
      $mageOrder      = $magePayment->getOrder();
      if ($mageOrder == null) {
        Mage::log('ZAIUS: Unable to retrieve order information. Please contact your Zaius rep for support.');
      }
      $event = array(
        'action'      => 'refund',
        'order'       => $helper->buildOrderRefund($mageOrder, $mageCreditmemo)
      );
      $helper->addCustomerIdOrEmail($mageOrder->getCustomerId(), $event);
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
      $this->postEvent('order', $event);
    }
  }

  private function postBackendOrder($mageOrder, $vuid = null) {
    $helper = Mage::helper('zaius_engage');
    $ip = '';
    if ($mageOrder->getXForwardedFor()) {
      $ip = $mageOrder->getXForwardedFor();
    } else if ($mageOrder->getRemoteIp()) {
      $ip = $mageOrder->getRemoteIp();
    }
    $event = array(
      'action'      => 'purchase',
      'order'       => $helper->buildOrder($mageOrder),
      'ua'          => '',
      'ip'          => $ip
    );
    if ($vuid) {
      $event['vuid'] = $vuid;
    }
    if ($mageOrder->getCustomerId()) {
      $helper->addCustomerIdOrEmail($mageOrder->getCustomerId(), $event);
    }
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
    $this->postEvent('order', $event);
  }
}
