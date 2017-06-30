<?php

/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category      Vaimo
 * @package       Icommerce_Adwords
 * @copyright     Copyright (c) 2009-2015 Vaimo AB
 * @author        Branislav Jovanovic <branislav.jovanovic@vaimo.com>
 */

/**
 * Class Icommerce_Adwords_Block_Adminhtml_Form_Field_Googletagparams
 */
class Icommerce_Adwords_Block_Adminhtml_Form_Field_Googletagparams
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_prepareToRender();
        parent::__construct();
    }

    /**
     * Get the grid and scripts contents
     *
     * This is added to solve system.xml 'depends' issue with frontend model.
     * When frontend model is not used the javascript to show/hide dependent field will look like this:
     * new FormElementDependenceController({"your_dependent_field_id":{"you_dependency_field id":"1"}});
     * and show/hide will work fine because both ids are present in html.
     *
     * But, when custom frontend module is used the value for the second field is rendered by your custom block
     * module/adminhtml_form_field_test and does not contain id of the dependent field and javascript just does not
     * know what to hide.
     *
     * So, this solution wrap element into div with specified id for it
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::_getElementHtml($element);
        return '<div id="adwords_settings_google_tag_params">' . $html . '</div>';
    }

    /**
     * Had to add this because of Jenkins complaining about this method not existing - even if it is in one of the
     * parent classes
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return parent::getLayout();
    }

    /**
     * Renders the HTML.
     */
    public function _prepareToRender()
    {
        $this->addColumn('google_tag_param_name', array('label' => Mage::helper('adwords')
            ->__('Google Tag Param Name'), 'style' => 'width:120px',));
        $this->addColumn('google_tag_param_value', array('label' => Mage::helper('adwords')
            ->__('Google Tag Param Value'), 'style' => 'width:120px',));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adwords')
            ->__('Add Param');
    }
}
