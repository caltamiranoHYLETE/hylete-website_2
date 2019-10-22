<?php
include_once('app/code/local/Icommerce/AddToCartAjax/controllers/IndexController.php');
class Icommerce_Style_CartController extends Icommerce_AddToCartAjax_IndexController {

	protected function _getCart()
	{
		return Mage::getSingleton('style/cart');
	}
	
    public function addMultipleAction() {
        $paramsFromStyle = $this->getRequest()->getParams();
        $cart = $this->_getCart();
        $cartHelper = Mage::helper('checkout/cart');
        $result = array();

        $itemsOnCartBefore = $cartHelper->getItemsQty();
        try {
            if(!empty($paramsFromStyle['styleProduct']) && !empty($paramsFromStyle['addToCartProducts'])) {
                foreach($paramsFromStyle['addToCartProducts'] as $productId) {
                    if($productId > 0) {
                        $product = $this->initProductById($productId);

                        $params = array();
                        $params['qty'] = 1;
						$params['buyRelated'] = false;
                        //$params['additional_ids'] = array();
                        //$params['request'] = $this->getRequest();
                        //$params['response'] = $this->getResponse();
                        $params['product'] = $productId;
                        
                        if (isset($paramsFromStyle['super_attribute'][$productId])){
                            $superAttributes = array();
                        	foreach ($paramsFromStyle['super_attribute'][$productId] as $code => $value) {
                        		$params['super_attribute'][$code] = $value;
                        	}
                        	//$params['super_attribute'] = $superAttributes;
                        }

                        if (!$product) {
                            throw new Exception($this->__('Cannot add product to cart, this product does not exist.'));
                        }

                        Mage::dispatchEvent('checkout_cart_before_add', $params);
                        
                        try {
                        	$additionResponse = $cart->addOneOfManyProducts($product, $params);
                        } catch (Exception $ex){
                        	Mage::logException($ex);
                        }
                        
                        Mage::dispatchEvent('checkout_cart_after_add', $params);

                        Mage::dispatchEvent('checkout_cart_add_product_complete',
                                        array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                                    );
                        Mage::dispatchEvent('checkout_cart_add_product', array('product'=>$product));
                    }
                }

                $cart->save();



                if (!$cart->getQuote()->getHasError()){
                	// @TODO This is a very strange utf-8 error.
					//$html = utf8_encode($this->getCartSidebarBlockHtml());
					$some_string = $this->getCartSidebarBlockHtml();
					//reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ?
					$some_string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
							'|[\x00-\x7F][\x80-\xBF]+'.
							'|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
							'|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
							'|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
							'?', $some_string );
					
					//reject overly long 3 byte sequences and UTF-16 surrogates and replace with ?
					$some_string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
							'|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $some_string );
					
					$html = $some_string;
					//TODO
					$itemsAdded = $cartHelper->getItemsQty() - $itemsOnCartBefore;
                    $result['success'] = array(
                        'altmessage' => $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName())),
                        'message' => $this->__('%s product(s) added to your shopping cart.', $itemsAdded),
                        'html' => $html,
                        'itemCount' => $cartHelper->getItemsCount(),
                        'itemQty' => $cartHelper->getItemsQty()
                    );

                    if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckoutCart')){
                        $result['success']['html_quickcheckoutcart'] = $this->getQuickCheckoutCartHtml();
                    }

                } else {
                    $msg = array();
                    foreach ($cart->getQuote()->getMessages() as $message) {
                        if ($message) {
                            /* @var $message Mage_Core_Model_Message_Error */
                            $msg[] = $message->getCode();
                        }
                    }
                    if (empty($msg)) {
                        $msg[] = $this->__('It was not possible to add the product to the shopping cart.');
                    }
                    $result['error'] = array(
                        'message' => implode('<br/>',$msg),
                        'html' => $this->getCartSidebarBlockHtml(),
                    );
                }
            }


			// Begin Qty check (not found better way of doing this to give user proper message...)
			//

            $request = $this->_getProductRequest($params);

			$cartCandidates = $product->getTypeInstance(true)
				->prepareForCart($request, $product);

			$quote = Mage::getSingleton('checkout/session')->getQuote();
			$items = $quote->getAllVisibleItems();

			// If something goes wrong in prepareForCart (see above) a string is returned instead of an array
			if(is_array($cartCandidates)){

				foreach($cartCandidates as $candidate){
					if($candidate->getTypeId() != 'configurable'){
						if($stockItem = $candidate->getStockItem()){

							// Get how many we already have in cart
							$cartItemQty = 0;

							foreach($items as $cartItem) {
								if($cartItem->getSku() == $candidate->getSku()){
									$cartQty = $cartItem->getQty();
									break;
								}
							}

							$qtyInStock = round($stockItem->getQty(), 2);

							$qtyInStock = $qtyInStock - $cartQty;

							$requestedQty = $cartQty + $params['qty'];

							if($requestedQty < $stockItem->getMinSaleQty()){
								throw new Exception($this->__('It was not possible to add the desired quantity to shopping cart for product %s since minimum quantity is %s.', $candidate->getName(), $stockItem->getMinQty()));
							}

							if((int)$stockItem->getMaxSaleQty() != 0 && $requestedQty > $stockItem->getMaxSaleQty()){
								throw new Exception($this->__('It was not possible to add the desired quantity to shopping cart for product %s since maximum quantity is %s.', $candidate->getName(), $stockItem->getMaxSaleQty()));
							}

							if($qtyInStock < $params['qty']){
								$result['error'] = array(
									'message' => $this->__('Unfortunately, the size selected is not currently in stock. We apologize for the inconvenience.')
								);
								break;
							}
						}
					}
				}
			}
			// End of qty check

			if(!array_key_exists('success', $result)){

				if ($this->_getSession()->getUseNotice(true)) {
					$this->_getSession()->addNotice($e->getMessage());
				}

				$url = $this->_getSession()->getRedirectUrl(true);

				if ($url) {
					$result['redirect'] = array(
						'message' => $this->__('This product has mandatory options, please wait while redirecting to product page...'),
						'url' => $url
					);
				}
				else {
					$result['error'] = array(
						'message' => $e->getMessage()
					);
				}
			}
        }
        catch (Exception $e) {
			$result['error'] = array(
				'message' => $e->getMessage()
			);
        }

		// Build json response
		$json = Zend_Json::encode($result);
		$this->getResponse()->setBody($json);
    }
    
    protected function initProductById($productId)
    {
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }


}