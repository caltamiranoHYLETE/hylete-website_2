<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Parameter renderer
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Block_Adminhtml_Landedcost_Parameter_Edit_Form_Renderer_Parameter
    extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Form element instance
     *
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_element;

    /**
     * Initialize block
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);

        $this->setTemplate('onepica/avatax/parameter/renderer.phtml');
    }

    /**
     * @return \Varien_Object[]
     */
    public function getAvalaraParameterTypes()
    {
        try {
            /** @var \OnePica_AvaTax_Model_Action_BagItems $bagItems */
            $bagItems = Mage::getSingleton('avatax/action_bagItems');
            $items = $bagItems->getAllParameterBagItems();

            return $items->getItems();
        } catch (Exception $exception) {
            Mage::logException($exception);
            return array();
        }
    }

    /**
     * @return false|\Mage_Core_Model_Abstract|\OnePica_AvaTax_Model_Records_HsCode
     */
    protected function _getHsCodeModel()
    {
        return Mage::getModel('avatax_records/hsCode');
    }

    /**
     * Render HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    /**
     * Set form element instance
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return \OnePica_AvaTax_Block_Adminhtml_Landedcost_Parameter_Edit_Form_Renderer_Parameter
     */
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;

        return $this;
    }

    /**
     * Retrieve form element instance
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->_element;
    }
}
