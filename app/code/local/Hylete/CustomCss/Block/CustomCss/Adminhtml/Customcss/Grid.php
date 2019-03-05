<?php
/**
 * @author    Mediotype Developement <diveinto@mediotype.com>
 * @copyright 2019 Mediotype. All rights reserved.
 */

/**
 * Responsible for creating the admin grid
 */
class Hylete_CustomCss_Block_CustomCss_Adminhtml_Customcss_Grid extends Vaimo_CustomCss_Block_Adminhtml_Customcss_Grid
{
    /**
     * @return Vaimo_CustomCss_Block_Adminhtml_Customcss_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('name', array(
            'header'    => Mage::helper('customcss')->__('Name'),
            'align'     => 'left',
            'index'     => 'name',
            'width'     => '150px',
            'after'     => 'filename'
        ), 'filename');
        return parent::_prepareColumns();
    }
}
