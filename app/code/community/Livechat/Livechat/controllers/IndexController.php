<?php

class Livechat_Livechat_IndexController extends Mage_Core_Controller_Front_Action
{

    public function getCartAction()
    {
        $items = array();
        $grandTotal = 0;
        $cart = Mage::getModel('checkout/cart')->getQuote();
        
        $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
        
        $items['grand_total'] = '0'.' '.$currency_code;
        $items['qty_total'] = '0';
        
        foreach ($cart->getAllItems() as $key=>$item) {
            $items['products'][$key]['url'] = $item->getProduct()->getUrlInStore();
        }

        $quote = Mage::getModel('checkout/session')->getQuote();
        
        foreach ($quote->getAllItems() as $key=>$item) {
            $grandTotal += $item->getPriceInclTax()*$item->getQty();
            $items['products'][$key]['name'] = $item->getName();
            $items['products'][$key]['price'] = round($item->getPrice(), 2).' '.$currency_code;
            $items['products'][$key]['qty'] = $item->getQty();
            $items['grand_total'] = round($grandTotal, 2).' '.$currency_code;
            $items['qty_total'] += $item->getQty();
        }
        
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($items));
    }

}
