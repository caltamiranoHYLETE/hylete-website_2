<?php
class Zaius_Engage_Block_Template extends Mage_Core_Block_Template {

  public function getEventType() {
    $data = $this->getData();
    return $data['event_type'];
  }

  public function getEventDataJson() {
    $data = $this->getData();
    $eventData = $data['event_data'];
    $store = Mage::app()->getStore();
    if ($store) {
      $eventData['magento_website']    = $store->getWebsite()->getName();
      $eventData['magento_store']      = $store->getGroup()->getName();
      $eventData['magento_store_view'] = $store->getName();
    }
    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
      $customer = Mage::getSingleton('customer/session')->getCustomer();
      Mage::helper('zaius_engage')->addCustomerIdOrEmail($customer->getId(), $eventData);
    }
    return Mage::helper('core')->jsonEncode($eventData);
  }

}
