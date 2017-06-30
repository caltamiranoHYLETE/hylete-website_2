<?php

class Ebizmarts_BakerlooRestful_Block_Adminhtml_Widget_Grid_Column_Renderer_Comment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if($row->getComments()) {

            $result = $row->getComments();

            if(preg_match('^Orders:^', $result) == 1) {

                $result = preg_replace("^Orders:^", '', $result);
                $result = preg_replace("/,/", '<br />', $result);

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
        return $row->getComments();
    }

}