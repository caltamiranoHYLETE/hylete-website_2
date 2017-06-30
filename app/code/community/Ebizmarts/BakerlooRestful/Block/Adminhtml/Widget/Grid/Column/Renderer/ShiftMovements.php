<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_ShiftMovements extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if($row->getMovements()) {
            $result = '';

            foreach($row->getMovements() as $_mov) {
                $_currency = Mage::app()->getLocale()->currency($_mov['currency_code']);

                if($_mov['amount'] != 0 or $row->getType() == Ebizmarts_BakerlooRestful_Model_Activity::TYPE_TRANSACTION)
                    $result .= '<strong>Amount: </strong>' . $_currency->toCurrency($_mov['amount']) . '<br />';
                if($_mov['refunds'] > 0)
                    $result .= '<strong>Refunds: </strong>' . $_currency->toCurrency($_mov['refunds']) . '<br />';

                if($row->getType() != Ebizmarts_BakerlooRestful_Model_Activity::TYPE_TRANSACTION)
                    $result .= '<strong>Balance: </strong>' . $_currency->toCurrency($_mov['balance']) . '<br />';

                $result .= '<br />';
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
        return $row->getType();
    }

}