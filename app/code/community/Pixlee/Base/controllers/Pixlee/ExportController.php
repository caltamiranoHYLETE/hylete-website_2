<?php
class Pixlee_Base_Pixlee_ExportController extends Mage_Adminhtml_Controller_Action {

  public function exportAction() {
    // Load constants, helpers and categories
    $referrer = Mage::helper('core/http')->getHttpReferer();
    preg_match("/section\/pixlee\/website\/(.*)\/key\//", $referrer, $matches);
    $websiteCode = ($matches[1]);
    $websiteId = Mage::getModel('core/website')->load($websiteCode)->getId();

    $separateVariants = Mage::app()->getWebsite($websiteId)->getConfig('pixlee/advanced/export_variants_separately');
    $helper = Mage::helper('pixlee');
    $pixleeAPI = $helper->getNewPixlee($websiteId);
    if (!$pixleeAPI || is_null($pixleeAPI)) {
      Mage::getSingleton("adminhtml/session")->addWarning("API credentials seem to be wrong, please check and try again");
      return;
    }
    $categoriesMap = $helper->getCategoriesMap();
    $numProducts = $helper->getTotalProductsCount($websiteId);

    // Pagination variables
    $counter = 0;   
    $limit = 100;
    $offset = 0;

    // Tell distilery that the job started
    $jobId = uniqid();
    $helper->notifyExportStatus('started', $jobId, $numProducts, $websiteId);

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
        if ($productCreated) $counter += 1;
      }

      unset($products);
    }

    $helper->notifyExportStatus('finished', $jobId, $counter, $websiteId);
    $json = array('action' => 'success');
    $json['pixlee_remaining_text'] = $helper->getPixleeRemainingText($websiteId);
    $this->getResponse()->setHeader('Content-type', 'application/json');
    $this->getResponse()->setBody(json_encode($this->utf8_converter($json)));
  }

  protected function _isAllowed() {
    return Mage::getSingleton('admin/session')->isAllowed('pixlee');
  }

  public function utf8_converter($array) {
    array_walk_recursive($array, function(&$item, $key){
      if(!mb_detect_encoding($item, 'utf-8', true)){
        $item = utf8_encode($item);
      }
    });
 
    return $array;
  }
}
