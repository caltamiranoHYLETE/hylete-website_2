<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Block_Adminhtml_Config_Feedbackwidget
 */
class Effy_Freshdesk_Block_Adminhtml_Config_Feedbackwidget extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';
        $html .= '<td class="" colspan="4">';
        $html .= '<div class="comment"><textarea>' . $element->getComment() . '</textarea></div>';
        $html .= '</td>';

        return $this->_decorateRowHtml($element, $html);
    }
}
