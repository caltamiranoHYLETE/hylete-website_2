<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
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
 * @package     Icommerce_AdminFeed
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 */
class Icommerce_AdminFeed_Block_Adminhtml_Tickets extends Mage_Adminhtml_Block_Template
{       
    public function getUserEmail()
    {
    	return Mage::getSingleton('admin/session')->getUser()->getEmail();
    }
    
    /**
     * Get support tickets from Cerberus.
     * If the ticket has been updated in Cerberus, store the ticket in Magento DB.
     * Always return tickets stored in Magento, in case of downtime in Cerberus.
     */
    public function getTickets()
    {	
    	$tickets = file("http://internal.icommerce.se/cerberus5/test.php?mail=".$this->getUserEmail());
    	foreach($tickets as $ticket)
    	{
    		$ticket = explode(";|", $ticket);
    		$ticketId = $ticket[0];
    		$subject = $ticket[1]; 		// Subject
    		$createdDate = $ticket[2]; 	// Date Created
    		$updatedDate = $ticket[3]; 	// Date Updated
    		$createdBy = $ticket[4]; 	// Created By
     		
     		$storedUpdatedDate = Icommerce_Db::getDbSingleton("SELECT updated_date 
     													FROM icommerce_support_tickets WHERE ticket_id='$ticketId' ORDER BY id ASC LIMIT 1");
     		if($storedUpdatedDate != $updatedDate)
     			Mage::getModel('adminfeed/tickets')->storeThisMessage($ticketId, $updatedDate, $subject, $createdDate, $createdBy);	
    	}
    	return Mage::getModel('adminfeed/tickets')->getTickets($this->getUserEmail());    	
    }
    
    public function getMessageContent($ticketId, $updatedDate)
    {
    	$content = Mage::getModel('adminfeed/tickets')->getMessageContent($ticketId, $updatedDate);
    	return $content;
    }	
}