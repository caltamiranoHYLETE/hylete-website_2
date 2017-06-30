<?php

class Ebizmarts_BakerlooRestful_Model_Api_Invoices extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = "sales/order_invoice";

    public function _createDataObject($id = null, $data = null) {
        $result = array();

        if(is_null($data)) {
            $invoice = Mage::getModel($this->_model)->load($id);
        }
        else {
            $invoice = $data;
        }

        if($invoice->getId()) {

            $invoiceItems = array();

            foreach($invoice->getItemsCollection() as $item) {

                $invoiceItems[]= array(
                    'product_id' => (int)$item->getProductId(),
                    'qty'        => ($item->getQty() * 1),
                    'price'      => (float)$item->getPrice(),
                    'name'       => $item->getName(),
                    'sku'        => $item->getSku(),
                );

            }

            $result = array(
                            "entity_id"            => (int)$invoice->getId(),
                            "increment_id"         => $invoice->getIncrementId(),
                            "state"                => $invoice->getStateName(),
                            "created_at"           => $invoice->getCreatedAt(),
                            "updated_at"           => $invoice->getUpdatedAt(),
                            "store_id"             => (int)$invoice->getStoreId(),
                            "base_grand_total"     => (float)$invoice->getBaseGrandTotal(),
                            "base_total_paid"      => (float)$invoice->getBaseTotalPaid(),
                            "base_currency_code"   => $invoice->getBaseCurrencyCode(),
                            "order_currency_code"  => $invoice->getOrderCurrencyCode(),
                            "grand_total"          => (float)$invoice->getGrandTotal(),
                            "total_paid"           => (float)$invoice->getTotalPaid(),
                            "tax_amount"           => (float)$invoice->getTaxAmount(),
                            "products"             => $invoiceItems,
        	);
        }

        return $result;
    }

}