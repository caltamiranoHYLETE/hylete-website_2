<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @category    Vaimo
 * @package     Vaimo_CustomCss
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
class Vaimo_CustomCss_Block_Adminhtml_Customcss_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customcss_form');
        $this->setTitle(Mage::helper('customcss')->__('Custom CSS Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('customcss_data');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));

        $form->setHtmlIdPrefix('customcss_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('customcss')->__('CSS Information'), 'class' => 'fieldset-wide'));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('customcss')->__('Status'),
            'title'     => Mage::helper('customcss')->__('Status'),
            'name'      => 'is_active',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('customcss')->__('Enabled'),
                '0' => Mage::helper('customcss')->__('Disabled'),
            ),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('customcss')->__('Store View'),
                'title'     => Mage::helper('customcss')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('filename', 'hidden', array(
            'name'  => 'filename'
        ));

        $fieldset->addField('code', 'textarea', array(
            'name'      => 'code',
            'label'     => Mage::helper('customcss')->__('CSS Code'),
            'title'     => Mage::helper('customcss')->__('CSS Code'),
            'style'     => 'display:none',
            'required'  => false,
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        $form->setValues($model->getData());

        return parent::_prepareForm();
    }
}