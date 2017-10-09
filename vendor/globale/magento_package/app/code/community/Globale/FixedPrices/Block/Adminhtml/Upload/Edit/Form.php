<?php

class Globale_FixedPrices_Block_Adminhtml_Upload_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('fixedPricesForm');
        $this->setTitle(Mage::helper('core')->__('Fixed Prices Information'));
    }

    /**
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {

        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('core')->__('Fixed Prices Information')
        ));

        $fieldset->addField('csv', 'file', array(
            'label'     => Mage::helper('core')->__('CSV File'),
            'required'  => true,
            'class'     => 'required-entry',
            'name'      => 'csv',
        ));

        $field = $fieldset->addField('delete', 'checkbox', array(
            'label'     => Mage::helper('core')->__('Delete Missing Rows?'),
            'onclick'   => 'deleteConfirmation(this);',
            'name'      => 'delete'
        ));

        $field->setAfterElementHtml('<small>Prices missing in the CSV file will be deleted</small><script>
            //<![CDATA[
            function deleteConfirmation(el) {
                if(el.checked){
                    var r = confirm("You Are About To DELETE All Rows NOT Existing In The CSV File!");
                    if (r == true) {
                        el.checked = true;
                        el.value   = 1;
                    } else {
                        el.checked = false;
                        el.value   = 0;
                    }
                }
                else{
                    el.checked = false;
                    el.value   = 0;                  
                }
            }
            //]]>
            </script>'
        );

        $form->setAction($this->getUrl('*/fixedprices/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
