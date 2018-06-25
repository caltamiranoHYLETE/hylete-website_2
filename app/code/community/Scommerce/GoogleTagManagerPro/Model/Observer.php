<?php
/**
 * GoogleTagManagerPro Observer
 *
 * @category   Scommerce
 * @package    Scommerce_GoogleTagManagerPro
 */
class Scommerce_GoogleTagManagerPro_Model_Observer
{
	private $_helper;
	
	public function __construct()
	{
		$this->_helper = Mage::helper('scommerce_googletagmanagerpro');
	}
	
	/**
     * Triggers on customer registration
     * @return void|Varien_Event_Observer
     */
    public function CustomerRegister() {
		if (!$this->_helper->isEnabled()) return;
        Mage::getSingleton('core/session')->setRegistered('1');
    }

    /**
     * Triggers on contact us form    
     * @return void|Varien_Event_Observer   
     */
    public function ContactPost() {
		if (!$this->_helper->isEnabled()) return;
        Mage::getSingleton('core/session')->setContactPost('1');
    }
	
	/**
     * Store data in cookie for every add to basket product so that it can retrieve later to send to GA
     *
     * @return void
    */
    public function addProductCookie(Varien_Event_Observer $observer)
    {
		if (!$this->_helper->isEnabled()) return;
		
        $event = $observer->getEvent();
        $product = $event->getProduct();
		$quoteItem = $event->getQuoteItem();

        $productToBasket = array(
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'category' => $this->_helper->getProductCategoryName($product),
            'brand' => $this->_helper->getBrand($product),
            'price' => $product->getFinalPrice(),
            'qty' => $quoteItem->getQty()
        );

        Mage::getModel('core/session')->setProductToBasket(json_encode($productToBasket));
    }
	
	/**
     * Store data in cookie for every remove from basket product so that it can retrieve later to send to GA
     *
     * @return void
    */
    public function removeProductCookie(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) return;
		
		$quoteItem = $observer->getQuoteItem();
        $product = $quoteItem->getProduct();
        
        $productOutBasket = array(
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'category' => $this->_helper->getProductCategoryName($product),
            'brand' => $this->_helper->getBrand($product),
            'price' => $product->getFinalPrice(),
            'qty' => $observer->getQuoteItem()->getQty()
        );

        Mage::getModel('core/session')->setProductOutBasket(json_encode($productOutBasket));
    }
	
	/**
     * Store data in cookie for refunded order so that it can retrieve later to send to GA
     *
     * @return void
    */
    public function refundOrderInventory(Varien_Event_Observer $observer)
    {
		if (!$this->_helper->isEnabled()) return;
		
        /**
         * @var Mage_Sales_Model_Order_Creditmemo $creditmemo
         * @var $item Mage_Sales_Model_Order_Creditmemo_Item
         */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $orderId = $order->getIncrementId();
		$storeId = $order->getStoreId();
        $products = array();
		$fullRefund = false;
		
		if (count($order->getAllItems())==count($creditmemo->getAllItems())){
			$fullRefund = true;
		}
			
        //if ($order->canCreditmemo())
        //{
		foreach ($creditmemo->getAllItems() as $item)
		{
			if($item->getBasePrice()<=0) continue;
			$products[] = array('id' => $item->getSku(), 'qty' => $item->getQty());
		}
        //}

        $response = array(
            'orderId'   => $orderId,
			'storeId'   => $storeId,
            'products'  => $products,
			'fullrefund'=> $fullRefund,
        );
		
		Mage::getModel('core/session')->setRefundOrder(json_encode($response));
		
    }
	
	/**
     * Store data in cookie for phone order so that it can retrieve later to send to GA
     *
     * @return void
    */
    public function sendTransactionToGA(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) return;
		
		$order = $observer->getEvent()->getOrder();
		$storeId = $order->getStoreId();
        $products = array();
		
		if (!$this->_helper->sendPhoneOrderTransaction($storeId) || !$this->isAdmin()) return;
		
		if ($this->_helper->sendBaseData()):
			$orderCurrency 		= $order->getBaseCurrencyCode();
			$orderGrandTotal 	= $order->getBaseGrandTotal();
			$orderShippingTotal	= $order->getBaseShippingAmount();
			$orderTax			= $order->getBaseTaxAmount();
		else:
			$orderCurrency 		= $order->getOrderCurrencyCode();
			$orderGrandTotal 	= $order->getGrandTotal();
			$orderShippingTotal	= $order->getShippingAmount();
			$orderTax			= $order->getTaxAmount();
		endif;
		
		foreach ($order->getAllItems() as $item){
			if($item->getParentItemId()) continue;
			$products[] = array(
								'name'	=> $item->getName(),
								'id' 	=> $item->getSku(), 
								'price'	=> ($this->_helper->sendBaseData()==true ? $item->getBasePrice() : $item->getPrice()),
								'brand' => $this->_helper->getBrand($item->getProduct()),
								'category' => $this->_helper->getQuoteCategoryName($item),
								'quantity' 	=> $item->getQtyOrdered()
							);
		}
        
        $response = array(
            'id'   			=> $order->getIncrementId(),
			'affiliation'   => $order->getAffiliation(),
			'revenue'   	=> $orderGrandTotal,
			'tax'   		=> $orderTax,
			'shipping'   	=> $orderShippingTotal,
			'coupon'   		=> $order->getCouponCode(),
			'storeId'   	=> $storeId,
			'products'  	=> $products,
        );
		
		Mage::getModel('core/session')->setOrderData(json_encode($response));
    }
	
	public function beforeCollectionLoad(Varien_Event_Observer $observer)
    {
        if (!$this->_helper->isEnabled()) return;
		
		$collection = $observer->getCollection();

        if (!isset($collection))
        {
            return;
        }

        if ($collection instanceof Mage_Catalog_Model_Resource_Product_Collection)
        {
            if ($attribute = $this->_helper->getBrandDropdown())
            {
                $collection->addAttributeToSelect($attribute);
            }
        }
    }
	
	public function isAdmin()
    {
		Mage::getSingleton('core/session', array('name'=>'adminhtml'));
		if(Mage::getSingleton('admin/session')->isLoggedIn()){
			if(Mage::app()->getStore()->isAdmin()){
				return true;
			}

			if(Mage::getDesign()->getArea() == 'adminhtml'){
				return true;
			}
		}
        return false;
    }
}