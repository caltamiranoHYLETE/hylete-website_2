<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_OrderSku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if($row->getProductId()) {
            if($row->getError())
                $href = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit/back/edit/tab/product_info_tabs_inventory', array('id' => $row->getProductId()));
            else
                $href = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getProductId()));

            $result = '<a href="' . $href . '" target="_blank">' . $row->getSku() . '</a>';
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
        return $row->getSku();
    }

}