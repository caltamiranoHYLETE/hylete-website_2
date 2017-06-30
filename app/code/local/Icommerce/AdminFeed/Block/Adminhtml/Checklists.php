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
class Icommerce_AdminFeed_Block_Adminhtml_Checklists extends Mage_Adminhtml_Block_Template
{           
    public function getProjects()
    {
    	return Mage::getModel('adminfeed/checklists')->getProjects();   		
    }
    
    public function getProject()
    {
    	return Mage::getModel('adminfeed/checklists')->getProject($this->getProjectId());   		
    }
    
    public function getProjectId()
    {
    	$params = $this->getRequest()->getParams();
		$id = (int)$params['id'];
		return $id;
    }
    
    public function getProjectName()
    {
    	return Mage::getModel('adminfeed/checklists')->getProjectName($this->getProjectId());  
    }   
    
    public function getChecklistItem()
    {
    	return Mage::getModel('adminfeed/checklists')->getChecklistItem($this->getProjectId()); 
    }
    
    public function getCheckboxes($listId)
    {
    	return Mage::getModel('adminfeed/checklists')->getCheckboxes($listId); 
    }
    
    public function getComments($checkboxId)
    {
    	return Mage::getModel('adminfeed/checklists')->getComments($checkboxId); 
    }
    
    public function hasComments($checkboxId)
    {
    	if($this->getComments($checkboxId))
    		return true;
    	else
    		return false;
    }
    
    public function getCheckboxStatus($checkboxId)
    {
    	return Mage::getModel('adminfeed/checklists')->getCheckboxStatus($checkboxId); 
    }
    
    public function getProjectStatus($projectId, $getStatusTitle=null)
    {
    	return Mage::getModel('adminfeed/checklists')->getProjectStatus($projectId, $getStatusTitle);
    }
    
    public function getChecklistItemStatus($itemId)
    {
    	return Mage::getModel('adminfeed/checklists')->getChecklistItemStatus($itemId);
    }
    
    public function getCommentSubject($checkboxId)
    {
    	return Mage::getModel('adminfeed/checklists')->getCommentSubject($checkboxId);
    }
    
    public function isStatusApproved($checkboxId)  
    {
    	return Mage::getModel('adminfeed/checklists')->isStatusApproved($checkboxId);
    }
    
    public function isAdmin()
    {
    	$username = strtolower(Mage::getSingleton('admin/session')->getUser()->getUsername());
    	if($username == "jambi")
    		return true;
    	else
    		return false; 
    }
    
    public function utf8($string)
    {
    	#$string = utf8_decode($string);
    	#$string = str_replace("Ã?", Mage::helper("adminfeed")->__('OE'), $string);
    	return $string;
    }
}