<?php
class Zaius_Engage_Model_Observer_Customer extends Zaius_Engage_Model_Observer {

  public function entity($observer) {
    if (Mage::helper('zaius_engage')->isEnabled()) {
      $this->postCustomerEntity($observer->getCustomer());
    }
  }

  public function entityFromAddress($observer) {
    if (Mage::helper('zaius_engage')->isEnabled()) {
      $this->postCustomerEntity($observer->getCustomerAddress()->getCustomer());
    }
  }

  private function postCustomerEntity($customer) {
    $customerData = $customer->getData();
    $helper = Mage::helper('zaius_engage');
    $entity = array(
      'email'       => $customerData['email'],
      'first_name'  => $customerData['firstname'],
      'last_name'   => $customerData['lastname']
    );
    $helper->addCustomerId($customer->getId(), $entity);
    $addresses = $customer->getAddresses();
    $addressData = null;
    if (isset($customerData['default_billing']) && isset($addresses[$customerData['default_billing']])) {
      $addressData = $addresses[$customerData['default_billing']]->getData();
    } else if (isset($customerData['default_shipping']) && isset($addresses[$customerData['default_shipping']])) {
      $addressData = $addresses[$customerData['default_shipping']]->getData();
    }
    if ($addressData) {
      $streetParts       = mb_split('\R', (isset($addressData['street']) ? $addressData['street'] : ''));
      $entity['street1'] = $streetParts[0];
      $entity['street2'] = count($streetParts) > 1 ? $streetParts[1] : '';
      $entity['city']    = $addressData['city'];
      $entity['state']   = $addressData['region'];
      $entity['zip']     = $addressData['postcode'];
      $entity['country'] = $addressData['country_id'];
      $entity['phone']   = $addressData['telephone'];
    }
    $this->postEntity('customer', $entity);
  }

  public function register($observer) {
    if (Mage::helper('zaius_engage')->isEnabled()) {
      Zaius_Engage_Model_BlockManager::getInstance()->addEvent('customer', array('action' => 'register'));
    }
  }

  public function login($observer) {
    if (Mage::helper('zaius_engage')->isEnabled()) {
      Zaius_Engage_Model_BlockManager::getInstance()->addEvent('customer', array('action' => 'login'));
    }
  }

  public function logout($observer) {
    if (Mage::helper('zaius_engage')->isEnabled()) {
      Zaius_Engage_Model_BlockManager::getInstance()->addEvent('customer', array('action' => 'logout'));
      Zaius_Engage_Model_BlockManager::getInstance()->addEvent('anonymize');
    }
  }

}
