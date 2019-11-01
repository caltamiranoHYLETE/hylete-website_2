<?php
/**
 * Effy_Freshdesk extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Freshdesk
 * @package        Effy_Freshdesk
 * @copyright      Copyright (c) 2018
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Ticket edit form tab
 *
 * @category    Freshdesk
 * @package     Effy_Freshdesk
 * @author      Ultimate Module Creator
 */
class Effy_Freshdesk_Block_Adminhtml_Ticket_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Effy_Freshdesk_Block_Adminhtml_Ticket_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('ticket_');
        $form->setFieldNameSuffix('ticket');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'ticket_form',
            array('legend' => Mage::helper('freshdesk')->__('Ticket'))
        );

        $fieldset->addField(
            'ticketid',
            'text',
            array(
                'label' => Mage::helper('freshdesk')->__('Ticket Id'),
                'name'  => 'ticketid',
                'required'  => true,
                'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('freshdesk')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('freshdesk')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('freshdesk')->__('Disabled'),
                    ),
                ),
            )
        );
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                )
            );
            Mage::registry('current_ticket')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_ticket')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getTicketData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getTicketData());
            Mage::getSingleton('adminhtml/session')->setTicketData(null);
        } elseif (Mage::registry('current_ticket')) {
            $formValues = array_merge($formValues, Mage::registry('current_ticket')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
