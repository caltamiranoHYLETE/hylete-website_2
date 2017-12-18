<?php
class Zaius_Engage_Model_Observer {

  public function render($observer) {
    if (!Mage::app()->getStore()->isAdmin()) {
      $beforeBodyEnd = $this->getBeforeBodyEndBlock();
      $layout = Mage::getSingleton('core/layout');
      $blockMgr = Zaius_Engage_Model_BlockManager::getInstance();
      if (Mage::helper('zaius_engage')->isEnabled() && $beforeBodyEnd) {
        $zaiusBlock = $layout->createBlock('core/text_list', 'zaius_engage_before_body_end');
        $zaiusBlock->append($blockMgr->initBlock());
        $blockMgr->addEvent('pageview');
        foreach ($blockMgr->getEventBlocks() as $eventBlock) {
          $zaiusBlock->append($eventBlock);
        }
        $zaiusBlock->append($blockMgr->closeBlock());
        $beforeBodyEnd->append($zaiusBlock);
        $blockMgr->clearEvents();
      }
    }
  }

  private function getBeforeBodyEndBlock() {
    $beforeBodyEnd = Null;
    $layout = Mage::getSingleton('core/layout');
    if ($layout) {
      $before_body_end = $layout->getBlock('before_body_end');
    }
    return $before_body_end;
  }

  protected function getFullActionName($observer) {
    $name = NULL;
    $action = $observer->getAction();
    if ($action) {
      $name = $action->getFullActionName();
    }
    return $name;
  }

  protected function getParams($observer) {
    $params = NULL;
    $action = $observer->getAction();
    if ($action) {
      $request = $action->getRequest();
      if ($request) {
        $params = $request->getParams();
      }
    }
    return $params;
  }

  protected function postEntity($type, $data) {
    $this->post('https://api.zaius.com/v2/entities', $type, $data);
  }

  protected function postEvent($type, $data) {
    $this->post('https://api.zaius.com/v2/events', $type, $data);
  }

  protected function post($url, $type, $data) {
    $body = json_encode(array('type' => $type, 'data' => $data));
    $length = strlen($body);
    $trackerId = Mage::helper('zaius_engage')->getTrackingID();
    $curl = curl_init();
    $version = (string)Mage::getConfig()->getNode('modules/Zaius_Engage/version');
    curl_setopt_array($curl, array(
      CURLOPT_URL            => $url,
      CURLOPT_CUSTOMREQUEST  => 'POST',
      CURLOPT_POSTFIELDS     => $body,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER     => array(
        'Content-Type: application/json',
        "Content-Length: $length",
        "Zaius-Tracker-Id: $trackerId",
        "Zaius-Engage-Version: $version"
      )
    ));
    $result = curl_exec($curl);
    $info = curl_getinfo($curl);
    if ($result === false || ($info['http_code'] != 200 && $info['http_code'] != 202)) {
      $error = curl_error($curl);
      Mage::log("Failed to POST to Zaius: $error");
    }
    curl_close($curl);
  }

}
