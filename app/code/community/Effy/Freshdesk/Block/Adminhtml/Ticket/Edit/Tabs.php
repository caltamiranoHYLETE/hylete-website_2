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
 * Ticket admin edit tabs
 *
 * @category    Freshdesk
 * @package     Effy_Freshdesk
 * @author      Ultimate Module Creator
 */
class Effy_Freshdesk_Block_Adminhtml_Ticket_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('ticket_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('freshdesk')->__('Ticket'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Effy_Freshdesk_Block_Adminhtml_Ticket_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_ticket',
            array(
                'label'   => Mage::helper('freshdesk')->__('Ticket'),
                'title'   => Mage::helper('freshdesk')->__('Ticket'),
                'content' => $this->getLayout()->createBlock(
                    'effy_freshdesk/adminhtml_ticket_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_ticket',
                array(
                    'label'   => Mage::helper('freshdesk')->__('Store views'),
                    'title'   => Mage::helper('freshdesk')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'effy_freshdesk/adminhtml_ticket_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve ticket entity
     *
     * @access public
     * @return Effy_Freshdesk_Model_Ticket
     * @author Ultimate Module Creator
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }
}
