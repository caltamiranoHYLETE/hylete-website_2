<?php
class Icommerce_AddToCartAjax_IndexController extends Mage_Core_Controller_Front_Action
{
	private $_cartBlock = null;

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');

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

    /**
     * Get cart sidebar block
     *
     * @return String
     */
    protected function getCartSidebarBlock(){

		$this->loadLayout();
		$block = $this->getLayout()->createBlock('checkout/cart_sidebar');

		return $block;
    }

    /**
     * Get cart sidebar block html
     *
     * @return String
     */
	public function getCartSidebarBlockHtml(){

		$this->loadLayout();

        $blockName = Mage::helper('addtocartajax')->getCartSidebarBlockName();

		$block = $this->getLayout()->getBlock($blockName);
		$result = $block->toHtml();

		return $result;
	}


    /**
     * Get quick checkout cart html
     *
     * @return String
     */
	public function getQuickCheckoutCartHtml(){

		$this->loadLayout();

		$block = $this->getLayout()->getBlock('quickcheckoutcart.cart');
		$result = $block->toHtml();

		return $result;
	}


	/**
     * Get related products block html
     *
     * @return String
     */
	public function getRelatedProductsBlockHtml($product){

		$this->loadLayout();
		$block = $this->getLayout()->getBlock('catalog.product.related')->setData('product', Mage::register('product', $product));
		$result = $block->toHtml();

		return $result;
	}

    public function removeBySkuAction() {

        $result = array();


        $sku = (int) $this->getRequest()->getParam('sku');
        $routeName = (int) $this->getRequest()->getParam('routeName');

        $itemId = false;

        $session= Mage::getSingleton('checkout/session');

        foreach($session->getQuote()->getAllItems() as $item)
        {

            $productSKU = $item->getSku();

            if($productSKU=='giftwrappingproduct') {
                $itemId = $item->getItemId();
                break;
            }

        }


        if ($itemId) {
            try {
                $this->_getCart()->removeItem($itemId)
                    ->save();

                $result['success'] = array(
                    'message' => $this->__('Item was removed successfully.'),
                    'html' => $this->getCartSidebarBlockHtml()
                );

                if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckoutCart')){
                    $result['success']['html_quickcheckoutcart'] = $this->getQuickCheckoutCartHtml();
                }

            } catch (Exception $e) {
                $result['error'] = $this->__('Cannot remove the item.');
            }
        }

        /* 	If cart is emptied when standing on cart or checkout
            page we want to redirect to cart. */

        if($this->getCartSidebarBlock()->getSummaryCount() < 1 && $this->isUrlCartOrCheckout($this->_getRefererUrl()) ){
            $result['redirect'] = array(
                'url' => Mage::getBaseUrl().'checkout/cart',
                'message' => $this->__('Your cart has been emptied. Please wait...')
            );
        }

        if($this->getCartSidebarBlock()->getSummaryCount() < 1){
            $result['cart_is_empty'] = '1';
        }

        // Build json response
        $json = Zend_Json::encode($result);
        $this->getResponse()->setBody($json);
    }

    /**
     * Add item to the cart
     *
     * @return json
     */
	public function addAction(){

        $cart = $this->_getCart();
        $params = $this->getRequest()->getParams();

        $result = array();
        $customErrorMessage = New Varien_Object();

        $cartHelper = Mage::helper('checkout/cart');
        $addToCartAjaxHelper = Mage::helper('addtocartajax/data');
        $product = null;
        $request = null;

        try {

            //if buyReleated not set
            if(!isset($params['buyRelated'])){
                $params['buyRelated'] = 'false';
            }

            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();

            Mage::dispatchEvent('icommerce_addtocartajax_before', array('params' => $params, 'message' => $customErrorMessage, 'product' => $product, 'cart' => $cart));

            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                throw new Exception($this->__('Cannot add product to cart, this product does not exist.'));
            }

            $customError = $customErrorMessage->getMessage();
            if (!empty($customError)) {
                throw new Exception($this->__($customError));
            }

            /**
             * Check if product has custom options and if there are required options.
             * This a safety check because core function prepareForCart() doesn't check required fields with type file.
             * This is needed for using AddToCartAjax button outside of product pages.
             * 
             * Added a check based on end for URLs with categories in them. Giorgos
             */
            $_urlArray = explode('/', $_SERVER['HTTP_REFERER']);
            $isProductPage = Mage::getUrl(end($_urlArray)) === $product->getProductUrl() . '/';
            if (($product->getTypeId() != Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) &&
                ($addToCartAjaxHelper->hasRequiredCustomOptions($product) ||
                ($product->getRequiredOptions() && !array_key_exists('options', $params) && !array_key_exists('super_attribute', $params)))) {
                if (!$isProductPage) {
                    $result['redirect'] = array(
                        'message' => $this->__('This product has mandatory options, please wait while redirecting to product page...'),
                        'url' => $product->getProductUrl()
                    );
                } else {
                    $result['error'] = array(
                        'message' => $this->__('This product has mandatory options'),
                        'html' => $this->__('This product has mandatory options')
                    );
                }
            } else {

                $cart->addProduct($product, $params);

                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }

                $cart->save();

                /**
                 * @todo remove wishlist observer processAddToCart
                 */
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                if (!$cart->getQuote()->getHasError()){
                    if ($params['buyRelated'] == 'false') {
                        if(!Mage::helper('addtocartajax')->showRelatedProducts()){
                            $result['success'] = array(
                                'message' => $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName())),
                                'html' => $this->getCartSidebarBlockHtml(),
                                'itemCount' => $cartHelper->getItemsCount(),
                                'itemQty' => $cartHelper->getItemsQty()
                            );
                        }
                        else{
                            $result['success'] = array(
                                'message' => $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName())),
                                'html' => $this->getCartSidebarBlockHtml(),
                                'buyRelated' => $this->getRelatedProductsBlockHtml($product),
                                'itemCount' => $cartHelper->getItemsCount(),
                                'itemQty' => $cartHelper->getItemsQty()
                            );
                        }
                    }
                    else{
                        $result['successRelated'] = array(
                                'message' => $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName())),
                                'html' => $this->getCartSidebarBlockHtml(),
                                'itemCount' => $cartHelper->getItemsCount(),
                                'itemQty' => $cartHelper->getItemsQty()
                         );
                    }

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
                    if(Mage::helper('addtocartajax')->showRelatedProducts() && $params['buyRelated'] == 'true') {
                        $result['errorRelated'] = array(
                            'message' => implode('<br/>',$msg),
                            'html' => $this->getCartSidebarBlockHtml(),
                        );
                    }
                    else{
                        $result['error'] = array(
                            'message' => implode('<br/>',$msg),
                            'html' => $this->getCartSidebarBlockHtml(),
                        );
                    }
                }
            }
        }
        catch (Mage_Core_Exception $e) {

			/* @todo go through this functionality and make it better... */

			// Begin Qty check (not found better way of doing this to give user proper message...)
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

                            if (empty($params['qty'])) {
                                $params['qty'] = 1;
                            }
							$requestedQty = $cartQty + $params['qty'];

							if($requestedQty < $stockItem->getMinSaleQty()){
                                if(Mage::helper('addtocartajax')->showRelatedProducts() && $params['buyRelated'] == 'true') {
                                    $result['errorRelated'] = array(
                                        'message' => $this->__('It was not possible to add the desired quantity to shopping cart for product %s since minimum quantity is %s.', $candidate->getName(), $stockItem->getMinQty())
                                    );
                                    break;
                                }
                                else{
                                    $result['error'] = array(
                                        'message' => $this->__('It was not possible to add the desired quantity to shopping cart for product %s since minimum quantity is %s.', $candidate->getName(), $stockItem->getMinQty())
                                    );
                                    break;
                                }
							}

							if((int)$stockItem->getMaxSaleQty() != 0 && $requestedQty > $stockItem->getMaxSaleQty()){
                                if(Mage::helper('addtocartajax')->showRelatedProducts() && $params['buyRelated'] == 'true') {
                                    $result['errorRelated'] = array(
                                        'message' => $this->__('It was not possible to add the desired quantity to shopping cart for product %s since maximum quantity is %s.', $candidate->getName(), $stockItem->getMaxSaleQty())
                                    );
                                    break;
                                }
                                else{
                                    $result['error'] = array(
                                        'message' => $this->__('It was not possible to add the desired quantity to shopping cart for product %s since maximum quantity is %s.', $candidate->getName(), $stockItem->getMaxSaleQty())
                                    );
                                    break;
                                }
							}

							if(!$stockItem->checkQty($requestedQty)){
								if(Mage::helper('addtocartajax')->showRelatedProducts() && $params['buyRelated'] == 'true') {
					                $result['errorRelated'] = array(
					                    'message' => $this->__('It was not possible to add the desired quantity to shopping cart.')
					                );
					                break;
				                }
				                else{
									$result['error'] = array(
										'message' => $this->__('It was not possible to add the desired quantity to shopping cart.')
									);
									break;
								}
							}
						}
					}
				}
			}else{
                $result['error'] = array(
                    'message' => $this->__('It was not possible to add the desired quantity to shopping cart.')
                );
            }
			// End of qty check

			if(!array_key_exists('error', $result) && !array_key_exists('errorRelated', $result)){

				if ($this->_getSession()->getUseNotice(true)) {
					$this->_getSession()->addNotice($e->getMessage());
				}

				$url = $this->_getSession()->getRedirectUrl(true);

				if ($url) {
					if(Mage::helper('addtocartajax')->showRelatedProducts() && $params['buyRelated'] == 'true') {
						$result['redirectRelated'] = array(
							'message' => $this->__('This product has mandatory options, please wait while redirecting to product page...'),
							'url' => $url
						);
					}
				    else{
				    	$result['redirect'] = array(
							'message' => $this->__('This product has mandatory options, please wait while redirecting to product page...'),
							'url' => $url
						);
					}
				}
				else {
					if(Mage::helper('addtocartajax')->showRelatedProducts() && $params['buyRelated'] == 'true') {
		                $result['errorRelated'] = array(
		                    'message' => $e->getMessage()
		                );
				    }
				    else{
						$result['error'] = array(
							'message' => $e->getMessage()
						);
					}
				}
			}
        }
        catch (Exception $e) {
			if(Mage::helper('addtocartajax')->showRelatedProducts() && $params['buyRelated'] == 'true') {
                $result['errorRelated'] = array(
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()

                );
			}
			else{
				$result['error'] = array(
					'message' => $e->getMessage(),
                    'code' => $e->getCode()
				);
			}
        }

        Mage::dispatchEvent('icommerce_addtocartajax_after', array('result' => $result, 'cart' => $cart));

		// Build json response
		$json = Zend_Json::encode($result);
		$this->getResponse()->setBody($json);
	}

    /**
     * Copied from Mage_Checkout_Cart since it's protected there
     *
     * @return json
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object();
            $request->setQty($requestInfo);
        } else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }
        return $request;
    }

    /**
     * Remove shopping cart item
     *
     * @return json
     */
    public function removeAction()
    {
        $result = array();
        $cartHelper = Mage::helper('checkout/cart');

        $id = (int) $this->getRequest()->getParam('id');
        $routeName = (int) $this->getRequest()->getParam('routeName');

        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();

				$result['success'] = array(
					'message' => $this->__('Item was removed successfully.'),
					'html' => $this->getCartSidebarBlockHtml(),
                    'itemCount' => $cartHelper->getItemsCount(),
                    'itemQty' => $cartHelper->getItemsQty()
				);

                if(Icommerce_Default_Helper_Data::isModuleActive('Icommerce_QuickCheckoutCart')){
                	$result['success']['html_quickcheckoutcart'] = $this->getQuickCheckoutCartHtml();
                }

            } catch (Exception $e) {
                $result['error'] = $this->__('Cannot remove the item.');
            }
        }

		/* 	If cart is emptied when standing on cart or checkout
			page we want to redirect to cart. */

		if($this->getCartSidebarBlock()->getSummaryCount() < 1 && $this->isUrlCartOrCheckout($this->_getRefererUrl()) ){
			$result['redirect'] = array(
				'url' => Mage::getBaseUrl().'checkout/cart',
				'message' => $this->__('Your cart has been emptied. Please wait...')
			);
		}

		if($this->getCartSidebarBlock()->getSummaryCount() < 1){
			$result['cart_is_empty'] = '1';
		}

		// Build json response
		$json = Zend_Json::encode($result);
		$this->getResponse()->setBody($json);
    }

	public function isUrlCartOrCheckout($url){

		$baseUrl = Mage::getBaseUrl();

		if($url == $baseUrl.'cart'){
			return true;
		}
		else if($url == $baseUrl.'cart/'){
			return true;
		}
		else if($url == $baseUrl.'checkout/onepage'){
			return true;
		}
		else if($url == $baseUrl.'checkout/onepage/'){
			return true;
		}
		else {
			return false;
		}
	}

    /**
     * Retrieve wishlist object
     *
     * @return false|Mage_Wishlist_Model_Wishlist|false
     */
    protected function _getWishlist()
    {
        $wishlist = false;
        try {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer->getId()) {
                $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);
            }

        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addException($e,
                Mage::helper('wishlist')->__('Cannot create wishlist.')
            );
        }
        if ($wishlist) {
            Mage::register('wishlist', $wishlist);
        }
        return $wishlist;
    }

    /**
     * Processes localized qty (entered by user at frontend) into internal php format
     *
     * @param string $qty
     * @return float|int|null
     */
    protected function _processLocalizedQty($qty)
    {
        if (!$this->_localFilter) {
            $this->_localFilter = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
        }
        $qty = $this->_localFilter->filter($qty);
        if ($qty < 0) {
            $qty = null;
        }
        return $qty;
    }

    /**
     * Get cart sidebar block html
     *
     * @return String
     */
	public function getWishlistHtml(){

		$this->loadLayout();

		if(Icommerce_Default::getMagentoVersion()>=1899){

			$block = $this->getLayout()->createBlock(
				'enterprise_giftregistry/wishlist_view',
				'giftregistry.customer.wishlist',
				array('template' => 'giftregistry/wishlist/view.phtml')
			);

			$result = $block->toHtml();
		}
		else {
			// @todo Add functionality for < 1900 editions
			$result = '';
		}

		return $result;
	}


    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     *
     */
    public function addFromWishlistAction()
    {

		$result = array();

    	try {

	        $wishlist = $this->_getWishlist();
	        if (!$wishlist) {
	            throw new Exception($this->__("No wishlist was found."));
	        }

	        $itemId = (int) $this->getRequest()->getParam('item');

	        /* @var $item Mage_Wishlist_Model_Item */
	        $item = Mage::getModel('wishlist/item')->load($itemId);

	        if (!$item->getId()){
	        	 throw new Exception($this->__("Item id is missing."));
	        }

	        if($item->getWishlistId() != $wishlist->getId()) {
				 throw new Exception($this->__("Item was not found in the wishlist."));
	        }

	        // Set qty
	        $qtys = $this->getRequest()->getParam('qty');
	        if (isset($qtys[$itemId])) {
	            $qty = $this->_processLocalizedQty($qtys[$itemId]);
	            if ($qty) {
	                $item->setQty($qty);
	            }
	        }

	        /* @var $session Mage_Wishlist_Model_Session */
	        $session    = Mage::getSingleton('wishlist/session');
	        $cart       = Mage::getSingleton('checkout/cart');


	        $options = Mage::getModel('wishlist/item_option')->getCollection()
	                ->addItemFilter(array($itemId));
	        $item->setOptions($options->getOptionsByItem($itemId));

	        $item->addToCart($cart, true);
	        $cart->save()->getQuote()->collectTotals();
	        $wishlist->save();

			$product = Mage::getModel('catalog/product')->load($item->getProductId());

			$result['success'] = array(
				'message' => $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName())),
				'html' => $this->getCartSidebarBlockHtml(),
				'wishlist_html' => $this->getWishlistHtml()
			);

			 Mage::helper('wishlist')->calculate();

        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
				$result['error'] = array(
					'message' => $this->__('It was not possible to add the desired quantity to shopping cart.')
				);
            } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getBaseUrl().'wishlist/index/configure/id/'. $item->getId() . '/';

				$result['redirect'] = array(
					'message' => $this->__('This product has mandatory options, please wait while redirecting to product page...'),
					'url' => $redirectUrl
				);


            } else {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getBaseUrl().'wishlist/index/configure/id/'. $item->getId() . '/';

				$result['redirect'] = array(
					'message' => $this->__('This product has mandatory options, please wait while redirecting to product page...'),
					'url' => $redirectUrl
				);

            }
        } catch (Exception $e) {
			$result['error'] = array(
				'message' => $this->__('Cannot add item to shopping cart.')
			);
        }

		// Build json response
		$json = Zend_Json::encode($result);
		$this->getResponse()->setBody($json);
    }

}
