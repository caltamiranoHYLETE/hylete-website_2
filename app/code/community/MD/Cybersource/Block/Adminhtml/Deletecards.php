<?php
/**
* Magedelight
* Copyright (C) 2015 Magedelight <info@magedelight.com>
*
* NOTICE OF LICENSE
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
*
* @category MD
* @package MD_Cybersource
* @copyright Copyright (c) 2015 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
class MD_Cybersource_Block_Adminhtml_Deletecards extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('md/cybersource/system/config/button.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
    
    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getButtonUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/deletecards');
    }
 
    
    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $url = $this->getButtonUrl();
        $js = "var exportConfirm = confirm('Are You sure? You will lose all customers saved card details.');
                if (exportConfirm == true) {
                    setLocation('$url')
                }";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'cybersource_deletecards',
            'label'     => $this->helper('adminhtml')->__('Delete Cards'),
            'onclick'   => $js
        ));
 
        return $button->toHtml();
    }
}
