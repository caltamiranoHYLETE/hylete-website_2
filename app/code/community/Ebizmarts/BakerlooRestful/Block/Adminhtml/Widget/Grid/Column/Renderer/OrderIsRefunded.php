<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderIsRefunded extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = Mage::helper('bakerloo_restful')->__('No');

        $orderId = (int)$row->getOrderId();

        if($orderId) {
            $nrOfCreditNotes = (int)Mage::getResourceModel('sales/order_creditmemo_grid_collection')
                                ->addFieldToFilter('order_id', $orderId)
                                ->getSize();

            if($nrOfCreditNotes)
                $result = Mage::helper('bakerloo_restful')->__('Yes (%s)', $nrOfCreditNotes);
        }

        return $result;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row) {
        return $this->render($row);
    }

}