<?php
/**
 * Copyright © 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
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
 * @category    Icommerce
 * @package     Icommerce_Enhancedgrid
 * @copyright   Copyright © 2009-2012 Icommerce Nordic AB
 */
class Icommerce_Enhancedgrid_Block_System_Html extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
	protected $_dummyElement;
	protected $_fieldRenderer;
	protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		return "";
    }

    protected function _getDummyElement()
    {
    	if (empty($this->_dummyElement)) {
    		$this->_dummyElement = new Varien_Object(array('show_in_default'=>1, 'show_in_website'=>1));
    	}
    	return $this->_dummyElement;
    }

    protected function _getFieldRenderer()
    {
    	if (empty($this->_fieldRenderer)) {
    		$this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
    	}
    	return $this->_fieldRenderer;
    }

	protected function _getFieldHtml($fieldset, $moduleName)
    {
		$configData = $this->getConfigData();
    	$path = 'advanced/modules_disable_output/'.$moduleName; //TODO: move as property of form
    	$data = isset($configData[$path]) ? $configData[$path] : array();

    	$e = $this->_getDummyElement();

		$moduleKey = substr($moduleName, strpos($moduleName,'_')+1);
		$ver = (Mage::getConfig()->getModuleConfig($moduleName)->version);

	
		if($ver){
			$field = $fieldset->addField($moduleName, 'label',
				array(
					'name'          => 'unused',
					'label'         => $moduleName,
					'value'         => $ver
				))->setRenderer($this->_getFieldRenderer());
			return $field->toHtml();
		}
		return '';
		
    }
}
