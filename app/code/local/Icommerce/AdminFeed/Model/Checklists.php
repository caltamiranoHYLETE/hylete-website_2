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

class Icommerce_AdminFeed_Model_Checklists
{
	public function getProjects()
    {
    	$r = Icommerce_Db::getDbRead();
		$sql = "SELECT * FROM icommerce_checklist WHERE status='1' ";
		$sql .= "ORDER BY position ASC";
        $rows = $r->query( $sql );
		
		foreach($rows as $key => $value)
			$projects[$key] = $value;
		
		return $projects;   		
    }
    
    public function getProject($id)
    {	
    	return Icommerce_Db::getRow("SELECT * FROM icommerce_checklist WHERE id='$id'");
    }
    
    public function getProjectName($id)
    {
    	return Icommerce_Db::getDbSingleton("SELECT name FROM icommerce_checklist WHERE id='$id'");  		
    }
    
   	public function getChecklistItem($projectId)
   	{
   		$r = Icommerce_Db::getDbRead();
		$sql = "SELECT * FROM icommerce_checklist_item ";
		$sql .= "WHERE project_id = $projectId AND status='1' ";
		$sql .= "ORDER BY position ASC";
        $rows = $r->query( $sql );
		
		foreach($rows as $key => $value)
			$lists[$key] = $value;
		
		return $lists;
	}
	
	public function getCheckboxes($listId)
    {
    	$r = Icommerce_Db::getDbRead();
		$sql = "SELECT * FROM icommerce_checklist_item_checkbox ";
		$sql .= "WHERE item_id = $listId AND status='1' ";
		$sql .= "ORDER BY position ASC";
        $rows = $r->query( $sql );
		
		foreach($rows as $key => $value)
			$checkboxes[$key] = $value;
		
		return $checkboxes;			
    }
    
    public function getComments($checkboxId)
    {
    	$r = Icommerce_Db::getDbRead();
		$sql = "SELECT * FROM icommerce_checklist_item_checkbox_comment ";
		$sql .= "WHERE item_checkbox_id = $checkboxId ";
		$sql .= "ORDER BY id ASC";
        $rows = $r->query( $sql );
		
		$comments = array();		
		foreach($rows as $key => $value)
			$comments[$key] = $value;
		
		return $comments;
    }
    
    public function getCheckboxStatus($checkboxId)
    {
    	return Icommerce_Db::getDbSingleton("SELECT checked FROM icommerce_checklist_item_checkbox WHERE id='$checkboxId'");
    }
    
    public function getProjectStatus($projectId, $getStatusTitle=null)
    {
    	$r = Icommerce_Db::getDbRead();
		$sql = "SELECT * FROM icommerce_checklist_item_checkbox ";
		$sql .= "WHERE project_id = $projectId AND status='1' AND checked='1' ";
        $rows = $r->query( $sql );
        $checked = array();
        foreach($rows as $key => $value) {
            $checked[$key] = $value;
        }

		$sql = "SELECT * FROM icommerce_checklist_item_checkbox ";
		$sql .= "WHERE project_id = $projectId AND status='1' ";
        $rows = $r->query( $sql );
        $total = array();
        foreach ($rows as $key => $value) {
            $total[$key] = $value;
        }
        if (empty($total)) {
            $ready = $percent = 0;
        } else {
            $ready = count($checked)."/".count($total);
            $percent = round(count($checked) / count($total) * 100, 1);
        }

		if($getStatusTitle)	
		{	
			if($percent < 33)
				return "status-red";
			elseif($percent < 100)
				return "status-orange";
			elseif($percent == 100)
				return "status-green";
		}
		else
			return $percent. "% ($ready)";
    } 
    
    public function getChecklistItemStatus($itemId)
    {			
    	$r = Icommerce_Db::getDbRead();
		$sql = "SELECT * FROM icommerce_checklist_item_checkbox ";
		$sql .= "WHERE item_id = $itemId AND status='1' AND checked='1' ";
        $rows = $r->query( $sql );
        $checked = array();
		foreach($rows as $key => $value)
			$checked[$key] = $value;
			
		$sql = "SELECT * FROM icommerce_checklist_item_checkbox ";
		$sql .= "WHERE item_id = $itemId AND status='1' ";
        $rows = $r->query( $sql );
		foreach($rows as $key => $value)
			$total[$key] = $value;
			
		$percent = round(count($checked) / count($total) * 100, 1);
		
		if($percent < 33)
		    return "status-red";
		elseif($percent < 100)
		    return "status-orange";
		elseif($percent==100)
		    return "status-green";
    }  
    
    public function getCommentSubject($checkboxId)  
    {
    	$subject = Icommerce_Db::getDbSingleton("SELECT text FROM icommerce_checklist_item_checkbox WHERE id='$checkboxId'");
    	$subject = explode("\n", $subject);
    	return $subject[0];
    }
    
    public function isStatusApproved($checkboxId) 
    {
    	return Icommerce_Db::getDbSingleton("SELECT status_approved FROM icommerce_checklist_item_checkbox_comment WHERE id='$checkboxId'");
    }
}