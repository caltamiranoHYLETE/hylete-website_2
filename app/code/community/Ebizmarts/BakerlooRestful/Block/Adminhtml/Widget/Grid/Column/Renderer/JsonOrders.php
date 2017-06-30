<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_JsonOrders extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        $orderArray = json_decode($row->getJsonOrders());

        if(is_array($orderArray)) {
            $result = '';

            foreach($orderArray as $orderId) {

                $order = Mage::getModel('sales/order')->load($orderId, 'increment_id');

                if($order->getId()) {
                    $href = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId()));
                    $result .= '<a href="' . $href . '" target="_blank">' . $orderId . '</a><br />';
                }
                else {
                    $href = Mage::helper('adminhtml')->getUrl('adminhtml/bakerlooorders/edit', array('id' => $orderId));
                    $result .= '<a href="' . $href . '" target="_blank">' . $orderId . '</a><br />';
                }
            }

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
        return $row->getOrderIncrementId();
    }

}