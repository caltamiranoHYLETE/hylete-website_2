<?php
/**
 
 * @category	BSS
 * @package	BSS_Fastorder
 
 */

class BSS_FastOrder_AjaxController extends Mage_Core_Controller_Front_Action
{

        /**
         * Get a products list corresponding to the sku typed
         */
        public function indexAction() {
          $max = $this->getRequest()->getParam('max');
          $sku = $this->getRequest()->getParam('sku');

          $collection = Mage::getModel('catalog/product')->getCollection()
          ->addAttributeToSelect('*')
          ->addStoreFilter()
          ->addUrlRewrite()
          ->addAttributeToFilter('type_id', array('eq' => 'simple'))
          ->setPage(1, $max);

          if(Mage::getStoreConfig('bss_fastorder/general_settings/fastorder_namesearch_enable'))
          {
            $collection->addAttributeToFilter(array(
              array(
               'attribute' => 'sku',
               'like' => '%'.$sku.'%'
               ),
              array(
               'attribute' => 'name',
               'like' => '%'.$sku.'%'
               )
              ));
          }
          else
          {
            $collection->addAttributeToFilter(array(
              array(
               'attribute' => 'sku',
               'like' => '%'.$sku.'%'
               )
              ));
          }			

          Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);

          if(Mage::getStoreConfig('bss_fastorder/general_settings/fastorder_subproduct_enable'))
          {
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
          }

          Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

          $result = array();
          foreach($collection as $item)
          {
            $prod = array();
            $prod['name'] = $item->getName();
            $prod['sku'] = $item->getSku();
            $prod['thumbnail'] = Mage::helper('catalog/image')->init($item, 'thumbnail')->__toString();
            $prod['price'] = Mage::helper('core')->currency($item->getPrice(),true,true);
            $prod['url'] = $item->getProductUrl();

            $result[] = $prod;
          }

          echo json_encode($result);

        }
        
        
        public function cartAction()
        {
          $this->loadLayout();
          $this->renderLayout();
        }

        public function addAction() {
          parse_str($_POST['items'], $items);
          $h = 0;
          for($i=0; $i < count($items['sku']) ;$i++) {
            if(!empty($items['sku'][$i])) {
              $h++;
              $sku = $items['sku'][$i];
              $j= $i+1;
              $name = 'fastorder-ref-'.$j;
              $qty = $items[$name];
              $id = Mage::getModel('catalog/product')->getIdBySku($sku);
              if ($id == '') {
                Mage::getSingleton('checkout/session')->addError($this->__("<strong>Product not added</strong><br />The SKU you entered ($sku) was not found.")); return;
              }
              else
              {
                $cart = Mage::getModel('checkout/cart');

                try {
                  $cart->addProduct(Mage::getModel('catalog/product')->load($id), $qty);
                  $cart->save();
                } catch (Mage_Core_Exception $e) {
                  Mage::getSingleton('checkout/session')->addError($e->getMessage()); return;
                }
              }
            }
          };
          if($h != 0) {
            echo '0';
          }else {
            echo '1';
          }
        }

      }
