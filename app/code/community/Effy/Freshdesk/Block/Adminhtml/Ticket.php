<?php
/**
 * Effy Freshdesk extension
 *
 * @category    Effy_Freshdesk
 * @package     Effy_Freshdesk
 * @copyright   Copyright (c) 2014 Effy. (http://www.effy.com)
 * @license     http://www.effy.com/disclaimer.html
 */

/**
 * Class Effy_Freshdesk_Block_Adminhtml_Ticket
 */
class Effy_Freshdesk_Block_Adminhtml_Ticket extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	protected $_blockGroup = 'effy_freshdesk';
	protected $_controller = 'adminhtml_ticket';
    
	/**
	 * Constructor for Adminhtml Block
	 */
	public function __construct()
	{
		$this->_addButtonLabel = $this->__('Create Ticket');

        $this->_addButton('refresh', array(
            'label'     => $this->__('Refresh'),
            'onclick'   => 'setLocation(\'' . $this->getRefreshUrl() .'\')',
            //'class'     => 'add',
        ));

		parent::__construct();
	}

    public function getCreateUrl()
    {
        return $this->getUrl('*/freshdesk_ticket/create');
    }

    public function getRefreshUrl()
    {
        return $this->getUrl('*/freshdesk_ticket/refresh');
    }

	public function getHeaderText()
	{
		return $this->__('Tickets');
	}
	
	public function getHeaderCssClass()
	{
		return 'icon-head head-ticket-page';
	}
}