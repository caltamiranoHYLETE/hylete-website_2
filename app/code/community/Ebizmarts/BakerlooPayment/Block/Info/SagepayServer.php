<?php

class Ebizmarts_BakerlooPayment_Block_Info_SagepayServer extends Ebizmarts_BakerlooPayment_Block_Info_Default {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('bakerloo_restful/payment/info/sagepay_server.phtml');
    }

    public function getVendorTxCode() {
    	return $this->getInfo()->getPoNumber();
    }

    public function detailLink($vendorTxCode) {
        $title = $this->__('Click here to view Transaction detail.');
        return sprintf('<a title="%s" id="%s" class="trn-detail-modal" href="%s">%s</a>', $title, $vendorTxCode, $this->_getDetailUrl($vendorTxCode), $vendorTxCode);
    }

    protected function _getDetailUrl($identifier) {
        return $this->helper('adminhtml')->getUrl('sagepayreporting/adminhtml_sagepayreporting/transactionDetailModal/', array('vendortxcode' => $identifier));
    }

}