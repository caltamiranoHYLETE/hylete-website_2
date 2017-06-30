<?php

class Icommerce_EmailAttachments_Model_Order_Pdf_Items_Order_Grouped extends Mage_Sales_Model_Order_Pdf_Items_Order_Default
{
    public function draw()
    {
        $type = $this->getItem()->getRealProductType();
        $renderer = $this->getRenderedModel()->getRenderer($type);
        $renderer->setOrder($this->getOrder());
        $renderer->setItem($this->getItem());
        $renderer->setPdf($this->getPdf());
        $renderer->setPage($this->getPage());

        $renderer->draw();
    }
}