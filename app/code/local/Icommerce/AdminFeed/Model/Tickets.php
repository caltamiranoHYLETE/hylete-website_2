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

class Icommerce_AdminFeed_Model_Tickets
{
	public function storeThisMessage($ticketId, $updatedDate, $subject, $createdDate, $createdBy)
	{
		$wr = Icommerce_Db::getDbWrite();
		$ticketExists = Icommerce_Db::getDbSingleton("SELECT id FROM icommerce_support_tickets WHERE ticket_id='$ticketId'");
		if($ticketExists)
			$sql = "UPDATE icommerce_support_tickets SET updated_date='$updatedDate', content='' WHERE ticket_id='$ticketId'";
		else	
			$sql = "INSERT INTO icommerce_support_tickets (ticket_id, updated_date, created_date, subject, created_by) VALUES('$ticketId' , '$updatedDate', '$createdDate', '$subject', '$createdBy')";
		$wr->query( $sql );
	}
	
	public function getTickets($createdBy)
	{
		$r = Icommerce_Db::getDbRead();
        $rows = $r->query( "SELECT * FROM icommerce_support_tickets WHERE created_by='$createdBy' ORDER BY id ASC" );
		$returnArray = array();
		
		foreach($rows as $key => $value)
			$returnArray[$key] = $value;
		
		return $returnArray;
	}
	
	public function getMessageContent($ticketId, $updatedDate)
	{
		$content = Icommerce_Db::getDbSingleton("SELECT content FROM icommerce_support_tickets WHERE ticket_id='$ticketId' AND updated_date='$updatedDate'");
												
		if($content)
			return $content;
		else
		{										
    		$content = file("http://internal.icommerce.se/cerberus5/test.php?ticketid=".$ticketId);
    		$content = implode("\n",$content);
    		$wr = Icommerce_Db::getDbWrite();
			$sql = "UPDATE icommerce_support_tickets SET content='$content' 
					WHERE ticket_id='$ticketId' AND updated_date='$updatedDate'";
			$wr->query( $sql );
   			return self::getMessageContent($ticketId, $updatedDate); //Do this in order to not get strange content when writing to DB.
		}
	}
}
