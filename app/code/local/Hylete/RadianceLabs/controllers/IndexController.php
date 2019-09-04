<?php
class Hylete_RadianceLabs_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {

		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$cartItems = $quote->getAllVisibleItems();

		$discountTotal = 0;

		foreach ($cartItems as $item) {
			$products = [];

			$products['variant_id'] = $item->getSku();
			$products['itemid'] = $item->getProductId();
			$products['quantity'] = $item->getQty();
			$products['properties'] = $item->getName();

			$discountTotal += $item->getDiscountAmount();

			array_push($return, $products);
		}

		$rlData = array(
			'token' => $quote->getId(),
			'total_discount'  => $discountTotal,
			'items' => $products
		);

		$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
		$this->getResponse()->setBody(json_encode($rlData));
	}

}