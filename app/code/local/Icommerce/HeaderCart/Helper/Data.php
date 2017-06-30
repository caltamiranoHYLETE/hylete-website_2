<?php
class Icommerce_HeaderCart_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_isUseAddToCartAjax = null;
    public function useAddToCartAjax()
    {
        if ($this->_isUseAddToCartAjax !== null) {
            return $this->_isUseAddToCartAjax;
        }
        $this->_isUseAddToCartAjax = false;
        if (Icommerce_Default::isModuleActive('Icommerce_AddToCartAjax') && Mage::getStoreConfig('addtocartajax/settings/use_headercart_xml')) {

            $this->_isUseAddToCartAjax = true;
        }

        return $this->_isUseAddToCartAjax;
    }

    public function getCartQty($summaryCount)
    {
        return ($summaryCount > 0) ? $summaryCount : 0;
    }

    public function getTotals()
    {
        $totals = Mage::helper('checkout/cart')->getCart()->getQuote()->getTotals();

        $subtotal = $totals['subtotal'];
        $grandTotal = $totals['grand_total'];

        return array(
            'subtotal'      => $subtotal->getData('value'),
            'grand_total'   => $grandTotal->getData('value')
        );
    }

    public function getQtyOfRecentItems($items)
    {
        $qty = 0;

        foreach ($items as $item) {
            $qty = $qty + $item->getQty();
        }

        return $qty;
    }
}