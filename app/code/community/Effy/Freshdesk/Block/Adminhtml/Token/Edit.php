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
 * Token admin edit form
 *
 * @category    Freshdesk
 * @package     Effy_Freshdesk
 * @author      Ultimate Module Creator
 */
class Effy_Freshdesk_Block_Adminhtml_Token_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'effy_freshdesk';
        $this->_controller = 'adminhtml_token';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('freshdesk')->__('Save Token')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('freshdesk')->__('Delete Token')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('freshdesk')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_token') && Mage::registry('current_token')->getId()) {
            return Mage::helper('freshdesk')->__(
                "Edit Token '%s'",
                $this->escapeHtml(Mage::registry('current_token')->getHash())
            );
        } else {
            return Mage::helper('freshdesk')->__('Add Token');
        }
    }
}
