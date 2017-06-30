<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_ActivityType extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if($row->getType()) {
            $type = $row->getType();

            if($type == Ebizmarts_BakerlooRestful_Model_Activity::TYPE_TRANSACTION) {

                $pay = $row->getPaymentMethod();
                $pay = preg_replace("^\n^", '<br />', $pay);

                $result = $pay;
            }
            else
                $result = '<b>' . ucfirst(preg_replace("/_/", ' ', $type)) . '</b>';
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
        return $row->getType();
    }

}