<?php
class Icommerce_PdfCustomiser_Block_View extends Icommerce_EmailAttachments_Block_View {

    public function getPrintUrl()
    {
        return $this->getUrl('pdfcustomiser/adminhtml_sales_order/print', array(
            'order_id' => $this->getOrder()->getId()
        ));
    }
}