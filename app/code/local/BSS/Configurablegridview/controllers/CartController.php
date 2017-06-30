<?php

require_once 'Mage/Checkout/controllers/CartController.php';

class BSS_Configurablegridview_CartController extends Mage_Checkout_CartController {

  public function addAction() {

    try {
      if($this->getRequest()->getParam('configurable_grid_table') == 'Yes') {


        $params = $this->getRequest()->getParams();
        $config_super_attributes = $params['super_attribute_quickshop'];
		$cart   = $this->_getCart();
        $config_table_qty = $params['config_table_qty'];
        $options = isset($params['options']) ? $params['options'] : null;

        $qty_config = array();
        foreach($config_table_qty as $sup_qty => $_super_qty) {
            $qty_config[$sup_qty] =$_super_qty;
        }
        $config_table_qty = $qty_config;

        foreach($config_super_attributes as $sId => $config_attribute) {

          if(isset($config_table_qty[$sId]) && $config_table_qty[$sId]!='' && $config_table_qty[$sId] > 0) {

            $product= $this->_initProduct();
            $related= $this->getRequest()->getParam('related_product');

            if (!$product) {
              die(var_dump($this->getRequest()->getParams()));
              $this->_goBack();
              return;
            }
            if(isset($config_table_qty[$sId])) {
              $params = array();
              $params['qty'] = $config_table_qty[$sId];
              $params['super_attribute'] = $config_attribute;
              if($options != null) $params['options'] = $options;

              if($params['qty'] > 0 && $params['qty']!='') {

                $cart->addProduct($product, $params);
                if (!empty($related)) {
                  $cart->addProductsByIds(explode(',', $related));
                }

              }
            }
          }
        }


	} else {

        $params = $this->getRequest()->getParams();
		$cart   = $this->_getCart();

        $product= $this->_initProduct();
        $related= $this->getRequest()->getParam('related_product');
        /**
         * Check product availability
         */
        if (!$product) {
          $this->_goBack();
          return;
        }
        $cart->addProduct($product, $params);
        if (!empty($related)) {
          $cart->addProductsByIds(explode(',', $related));
        }
      }

	   $cart->save();
		// set the cart as updated
		Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
		$message = $this->__('%s was successfully added to your shopping cart.', $product['name']);
		if (!$this->_getSession()->getNoCartRedirect(true)) {
			$this->_getSession()->addSuccess($message);
			$this->_goBack();
      	}
		if ($this->getRequest()->isXmlHttpRequest()) {
			exit('1');
		}
		$this->_redirect('checkout/cart');

 		 } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }
            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
  }
}
?>
