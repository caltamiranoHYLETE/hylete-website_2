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

class Icommerce_AdminFeed_Adminhtml_ChecklistsController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('icommerce/checklists');
    }

    public function indexAction()
    {        
        $this->loadLayout();
    	$this->_setActiveMenu('icommerce/adminfeed');
    	$this->renderLayout();
    }
    
    public function viewAction()
	{
    	$params = $this->getRequest()->getParams();
		$id = (int)$params['id'];
		
		if(!preg_match("/^[0-9]+$/", $id ))
		{
			throw new Exception('Project id is not valid.');
			return;
		}
        
        $this->loadLayout();
    	$this->_setActiveMenu('icommerce/adminfeed');
    	$this->renderLayout();
    }
    
    public function saveAction()
    {
    	$post = $this->getRequest()->getPost();
    	$project_id = $post['edit_form']['project_id'];
        $created_by = Mage::getSingleton('admin/session')->getUser()->getEmail();
        $created_on = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        
        try 
        {
            if (empty($project_id)) 
                Mage::throwException($this->__('Invalid form data.'));
 
			/* Here's our form processing */
			$i = 0;
			$j = 0;
			$k = 0;
			$l = 0;
			$m = 0;
			$wr = Icommerce_Db::getDbWrite();   
			$paramsComment = array();  
			foreach ($post['edit_form'] as $value)
			{
				/* Check if current post (key) contains a comment */
				$key = array_search($value, $post['edit_form']);
				
				if(strstr($key, "comment"))
				{
					/* If this comment contains something, continue */
					if($value)
					{
						$checkbox_id = str_replace("comment", "", $key);
						if($checkbox_id > 0)
						{
							$paramsComment['item_checkbox_id'] = $checkbox_id;
							$paramsComment['text'] = $value;
							$paramsComment['created_on'] = $created_on;
							$paramsComment['created_by'] = $created_by;
							
							$wr->insert( 'icommerce_checklist_item_checkbox_comment', $paramsComment );
							$i++;
						}
					}
				}
				
				if(strstr($key, "check"))
				{ 
					$params = array();
					$checkbox_id = str_replace("check", "", $key);
					if(strstr($value, "notok"))
					{
						if(Mage::getModel('adminfeed/checklists')->getCheckboxStatus($checkbox_id))
						{
							$params['checked'] = '0';
							$where = $wr->quoteInto('id=?', $checkbox_id);
							$r = $wr->update( 'icommerce_checklist_item_checkbox', $params, $where );
							$j++;
						}						
					}
					elseif(!Mage::getModel('adminfeed/checklists')->getCheckboxStatus($checkbox_id))
					{
					    $params['checked'] = '1';
					    $params['updated_on'] = $created_on;
					    $params['updated_by'] = $created_by;
					    $where = $wr->quoteInto('id=?', $checkbox_id);
					    $r = $wr->update( 'icommerce_checklist_item_checkbox', $params, $where );
					    $j++;
					}					
				}
				
				if(strstr($key, "estimate"))
				{
					if($value)
					{
						$comment_id = str_replace("estimate", "", $key);
						$params = array();
						$params['status_estimate'] = $value;
						$params['status_updated_on'] = $created_on;
						$params['status_updated_by'] = $created_by;
						
						$where = $wr->quoteInto('id=?', $comment_id);
   						$r = $wr->update( 'icommerce_checklist_item_checkbox_comment', $params, $where );
						$k++;
					}
				}
				
				if(strstr($key, "status"))
				{	
					if($value)
					{
						$comment_id = str_replace("status", "", $key);
						$params = array();
						$params['status'] = $value;
						$params['status_updated_on'] = $created_on;
						$params['status_updated_by'] = $created_by;
						
						$where = $wr->quoteInto('id=?', $comment_id);
   						$r = $wr->update( 'icommerce_checklist_item_checkbox_comment', $params, $where );
						$l++;
					}
				}
			}
 			
 			if($i>0 || $j>0 || $k>0 || $l>0)
 			{	
 				if($i>0 || $j>0)
 				{
 					$paramsLast = array();
 					$paramsLast['updated_on'] = $created_on;
					$paramsLast['updated_by'] = $created_by;
 					$where = $wr->quoteInto('id=?', $project_id);
   					$r = $wr->update( 'icommerce_checklist', $paramsLast, $where );
 				}
 			
				$message = $this->__('Your checklist has been saved.');
				Mage::getSingleton('adminhtml/session')->addSuccess($message);
			}
			else
			{
				$message = $this->__('There were nothing to save.');
				Mage::getSingleton('adminhtml/session')->addError($message);
			}
            /* Here's our form processing */
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('adminfeed/adminhtml_checklists/view/id/'.$project_id);
    }
}