<?php
class TroopID_Connect_Model_Order_Observer {

    protected $options = array();

    public function addColumn(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();

        if (!isset($block))
            return;

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {

            $block->addColumnAfter(
                "troopid_scope",
                array(
                    "header"        => "ID.me Affiliation",
                    "index"         => "troopid_scope",
                    "type"          => "options",
                    "filter"        => "TroopID_Connect_Block_Widget_Grid_Column_Filter_Scope",
                    "renderer"      => "TroopID_Connect_Block_Widget_Grid_Column_Renderer_Scope"
                ),
                "shipping_name"
            );
        }
    }

}