<?php
class Zaius_Engage_Model_Observer_Navigation extends Zaius_Engage_Model_Observer {

  public function browse($observer) {
    if (Mage::helper('zaius_engage')->isEnabled()) {
      $params = $this->getParams($observer);
      $action = $this->getFullActionName($observer);
      $eventData = array();
      if ($action === 'catalog_category_view') {
        $eventData['action'] = 'browse';
        $eventData['category'] = Mage::helper('zaius_engage')->buildCategoryPath($params['id']);
      } else if ($action === 'catalogsearch_result_index') {
        $eventData['action'] = 'search';
        $eventData['search_term'] = $params['q'];
      }

      if (count($eventData) > 0) {
        Zaius_Engage_Model_BlockManager::getInstance()->addEvent('navigation', $eventData);
      }
    }
  }

}