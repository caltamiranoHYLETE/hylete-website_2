<?php
class Pixlee_Base_Pixlee_ExportController extends Mage_Adminhtml_Controller_Action {

  public function exportAction() {
    // Load constants, helpers and categories
    $separateVariants = Mage::getStoreConfig('pixlee/advanced/export_variants_separately', Mage::app()->getStore());
    $helper = Mage::helper('pixlee');
    $pixleeAPI = $helper->getNewPixlee();
    if (!$pixleeAPI || is_null($pixleeAPI)) {
      Mage::getSingleton("adminhtml/session")->addWarning("API credentials seem to be wrong, please check and try again");
      return;
    }
    $categoriesMap = $helper->getCategoriesMap();
    $numProducts = $helper->getTotalProductsCount();

    // Pagination variables
    $counter = 0;   
    $limit = 100;
    $offset = 0;

    // Tell distilery that the job started
    $jobId = uniqid();
    $helper->notifyExportStatus('started', $jobId, $numProducts);

    while ($offset < $numProducts) {
      $products = Mage::getModel('catalog/product')->getCollection();
      $products->addAttributeToFilter('status', array('neq' => 2));   
      if (!$separateVariants) {
        $products->addAttributeToFilter('visibility', array('neq' => 1));
      }
      $products->getSelect()->limit($limit, $offset);
      $products->addAttributeToSelect('*');
      $offset = $offset + $limit;

      foreach ($products as $product) {
        $productCreated = $helper->exportProductToPixlee($product, $categoriesMap, $pixleeAPI);
        if ($productCreated) $counter += 1;
      }

      unset($products);
    }

    $helper->notifyExportStatus('finished', $jobId, $counter);
    $json = array('action' => 'success');
    $json['pixlee_remaining_text'] = $helper->getPixleeRemainingText();
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
