<?php
class Zaius_Engage_Model_BlockManager {

  static protected $_instance;
  static public function getInstance() {
    if (!self::$_instance) {
      self::$_instance = new Zaius_Engage_Model_BlockManager;
    }
    return self::$_instance;
  }

  public function initBlock() {
    return $this->getLayout()
      ->createBlock('zaius_engage/template')
      ->setTemplate('zaius_engage/init.phtml')
      ->setData($this->initData());
  }

  public function closeBlock() {
    return $this->getLayout()
      ->createBlock('zaius_engage/template')
      ->setTemplate('zaius_engage/close.phtml');
  }

  public function addEvent($eventType, $eventParams = array()) {
    $events = $this->getEvents();
    $event = new stdClass;
    $event->eventType = $eventType;
    $event->eventParams = $eventParams;
    $events[] = $event;
    $this->getSession()->setEvents($events);
  }

  public function getEvents() {
    $events = $this->getSession()->getEvents();
    if (!$events) {
      $events = array();
    }
    return $events;
  }

  public function getEventBlocks() {
    $blocks = array();
    foreach ($this->getEvents() as $event) {
      if ($event->eventType == 'anonymize') {
        $blocks[] = $this->getLayout()
          ->createBlock('zaius_engage/template')
          ->setTemplate('zaius_engage/anonymize.phtml');
      } else {
        $blocks[] = $this->getLayout()
          ->createBlock('zaius_engage/template')
          ->setTemplate('zaius_engage/event.phtml')
          ->setData('event_type', $event->eventType)
          ->setData('event_data', $event->eventParams);
      }
    }
    return $blocks;
  }

  public function clearEvents() {
    $this->getSession()->setEvents(array());
  }

  private function getLayout() {
    return Mage::getSingleton('core/layout');
  }

  private function getSession() {
    return Mage::getSingleton('zaius_engage/session');
  }

  private function initData() {
    return array();
  }

}
