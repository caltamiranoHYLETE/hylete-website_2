<?php
class Zaius_Engage_Model_Observer_Newsletter extends Zaius_Engage_Model_Observer {

  public function subscriptionChange($observer) {
    $helper = Mage::helper('zaius_engage');
    if ($helper->isEnabled()) {
      $data = $observer->getDataObject();
      $status = $data->getSubscriberStatus();
      $subscriberData = $data->getData();
      if ($status == 1) {
        /* The following BlockManager event is deprecated but left in for legacy support */
        Zaius_Engage_Model_BlockManager::getInstance()->addEvent(
          'newsletter', array('action' => 'subscribe', 'email' => $subscriberData['subscriber_email']));
        $event = array(
          'action'      => 'subscribe',
          'email'       => $subscriberData['subscriber_email'],
          'list_id'     => $helper->getNewsletterListID()
        );
        $this->postEvent('list', $event);
      } else if ($status == 3) {
        /* The following BlockManager event is deprecated but left in for legacy support */
        Zaius_Engage_Model_BlockManager::getInstance()->addEvent(
          'newsletter', array('action' => 'unsubscribe', 'email' => $subscriberData['subscriber_email']));
        $event = array(
          'action'      => 'unsubscribe',
          'email'       => $subscriberData['subscriber_email'],
          'list_id'     => $helper->getNewsletterListID()
        );
        $this->postEvent('list', $event);
      }
    }
  }

}
