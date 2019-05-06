<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_XSitemap_Model_Adminhtml_System_Config_Source_Xsitemap_Sortorder
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'position', 'label' => Mage::helper('mageworx_xsitemap')->__('Position')),
            array('value' => 'name', 'label' => Mage::helper('mageworx_xsitemap')->__('Name')),
        );
    }

}
