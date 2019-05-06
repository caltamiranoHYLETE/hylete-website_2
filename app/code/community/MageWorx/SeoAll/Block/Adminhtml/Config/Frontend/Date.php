<?php
/**
 * MageWorx
 * MageWorx SeoAll Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoAll
 * @copyright  Copyright (c) 2019 MageWorx (https://www.mageworx.com/)
 */

class MageWorx_SeoAll_Block_Adminhtml_Config_Frontend_Date extends MageWorx_SeoAll_Block_Adminhtml_Config_Frontend_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @throws Exception
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $date   = new Varien_Data_Form_Element_Date;
        $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $data = array(
            'name'    => $element->getName(),
            'html_id' => $element->getId(),
            'image'   => $this->getSkinUrl('images/grid-cal.gif'),
        );

        $date->setData($data);
        $date->setValue($element->getValue(), $format);
        $date->setFormat($format);
        $date->setClass($element->getFieldConfig()->validate->asArray());
        $date->setForm($element->getForm());

        return $date->getElementHtml();
    }
}
