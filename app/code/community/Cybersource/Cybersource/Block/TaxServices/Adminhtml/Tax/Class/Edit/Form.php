<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_TaxServices_Adminhtml_Tax_Class_Edit_Form extends Mage_Adminhtml_Block_Tax_Class_Edit_Form
{
    /**
     * Adds the Cybersource tax code field to the adminhtml product tax class form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $fieldset = $this->getForm()->getElement('base_fieldset');
        $model = Mage::registry('tax_class');
        $fieldset->addField(
            'cybersource_code', 'text', array(
                'name'  => 'cs_tax_code',
                'label' => Mage::helper('cybersource_taxservices')->__('Cybersource Tax Code'),
                'value' => $model->getCsTaxCode(),
            )
        );

        return $this;
    }
}
