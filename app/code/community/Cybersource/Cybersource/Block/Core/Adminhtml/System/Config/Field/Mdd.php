<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_Core_Adminhtml_System_Config_Field_Mdd extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('mdd', array(
            'label' => Mage::helper('adminhtml')->__('MDD Field Number:'),
            'style' => 'width:100px',
            'class' => 'required-entry input-text validate-number'
        ));

        $this->addColumn('value', array(
            'label' => Mage::helper('adminhtml')->__('Value:'),
            'style' => 'width:100px',
            'type' => 'select',
            'options' => Mage::getModel('cybersource_core/source_merchantFields')->toOptionArray(),
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Custom Field');
        parent::__construct();

        $this->setTemplate('cybersourcecore/system/config/field/mdd.phtml');
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     */
    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($columnName === 'value') {
            $html = '<select name="' . $inputName . '" />';
            foreach (Mage::getModel('cybersource_core/source_merchantFields')->toOptionArray() as $option) {
                $html .= '<option value="' . $option["value"] . '">' . $option["label"] . '</option>';
            }
            $html .= '</select>';
        } else {
            $html = '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
                ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
                (isset($column['class']) ? $column['class'] : 'input-text') . '"' .
                (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
        }
        return $html;
    }
}
