<?php

class Icommerce_PageManager_Adminhtml_PagemanagerController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
	    $this->loadLayout();
	    $this->_setActiveMenu('cms/pagemanager');
        $this->renderLayout();
    }
    
    public function addAction()
	{
	    $this->loadLayout();
	    $this->_setActiveMenu('cms/pagemanager');
        $this->renderLayout();
    }
	
    public function deleteAction()
	{
    	$params = $this->getRequest()->getParams();
		
		try {
            
            $id = (int)$params['id'];
			
			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Page id is not valid.');
				return;
			}
			
			$helper = Mage::helper('pagemanager');
			
			// Read all existing images in this page and delete them
			$r = Icommerce_Db::getDbRead();
			$rows = $r->query( "SELECT * FROM icommerce_pagemanager_item WHERE page_id = $id" );
			
			foreach($rows as $row){
				$oldFileName = $row['filename'];
				
				$oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
				if(file_exists($oldTargetPath)){
					unlink($oldTargetPath);
				}
			}
			
			// Delete from database
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->delete( 'icommerce_pagemanager', $where );

            $message = $this->__('Your page has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
			
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/pagemanager/index/');
	}
    
    public function editAction()
	{
    	$params = $this->getRequest()->getParams();
		
		$id = (int)$params['id'];
		
		if(!preg_match("/^[0-9]+$/", $id )){
			throw new Exception('Page id is not valid.');
			return;
		}
		
		$page = Mage::getModel('pagemanager/page')->getPage($id);
		
		$this->loadLayout();
		$editBlock = $this->getLayout()->getBlock("edit");
		$editBlock->setData('page', $page);
    	
	    $this->_setActiveMenu('cms/pagemanager');
        $this->renderLayout();
    }
    
    public function saveAction()
	{
        $post = $this->getRequest()->getPost();
        
		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
            $params = array();
			$params['name'] = $post['addform']['name'];
            $params['status'] = $post['addform']['status'];
            $params['position'] = $post['addform']['position'];
			$params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
            $params['quickview_enabled'] = isset($post['addform']['quickview_enabled']) ? $post['addform']['quickview_enabled'] : "";
			
			$session = Mage::getSingleton('admin/session');
			$userId = $session->getUser()->getUserId();
			
			$params['created_by'] = $userId;
			
			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$r = $wr->insert( 'icommerce_pagemanager', $params );
            
            $message = $this->__('Your page has been saved successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
			
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
		
        $this->_redirect('*/*');	
    }
	
    public function updateAction()
	{
        $post = $this->getRequest()->getPost();
        
		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
            $params = array();
			$params['name'] = $post['addform']['name'];
            $params['status'] = $post['addform']['status'];
            $params['position'] = $post['addform']['position'];

            if(Mage::getStoreConfig("pagemanager/settings/show_quickview_setting", 0) == 1){
                $params['quickview_enabled'] = isset($post['addform']['quickview_enabled']) ? $post['addform']['quickview_enabled'] : "";
            }else{
                $params['quickview_enabled'] = 1;
            }
			
			$id = (int)$post['addform']['page_id'];
			
			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Page id is not valid.');
				return;
			}
			
			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->update( 'icommerce_pagemanager', $params, $where);
            
            $message = $this->__('Your page has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
			
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
		
        $this->_redirect('*/*');	
    }
    
    public function massUpdateAction()
	{
        $post = $this->getRequest()->getPost();
        
		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
			$action = $post['edit_form']['mass_update_action'];
			
			if($action == 'delete'){
			
				$ids = $post['edit_form']['mass_update_id'];
			
				if(is_array($ids)){
					$ids = implode(',', $ids);
				}
				
				// Delete
				$wr = Icommerce_Db::getDbWrite();
				$sql = "DELETE FROM icommerce_pagemanager WHERE id IN ($ids)";
				
				$r = $wr->query( $sql );
				
			}
			else if($action == 'enable') {
			
				$ids = $post['edit_form']['mass_update_id'];
				
				if(is_array($ids)){
					$ids = implode(',', $ids);
				}
				
				$wr = Icommerce_Db::getDbWrite();
				
				$sql = "UPDATE icommerce_pagemanager SET status = 1 WHERE id IN ($ids)";
				
				$r = $wr->query( $sql );
			}
			else if($action == 'disable'){
				
				$ids = $post['edit_form']['mass_update_id'];
				
				if(is_array($ids)){
					$ids = implode(',', $ids);
				}
				
				$wr = Icommerce_Db::getDbWrite();
				
				$sql = "UPDATE icommerce_pagemanager SET status = 0 WHERE id IN ($ids)";
				
				$r = $wr->query( $sql );
			}
			else if($action == 'update'){

				$position = $post['edit_form']['position'];
				
				$wr = Icommerce_Db::getDbWrite();
				
				$sql = "";
				
				foreach($position as $key => $value){
					$sql .= "UPDATE icommerce_pagemanager SET position = $value WHERE id = $key;";	
				}
				
				$r = $wr->query( $sql );
			}
            
            $message = $this->__('Your pages has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
            
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
		
        $this->_redirect('*/*');	
    }

    public function copyAction()
    {
        $params = $this->getRequest()->getParams();

        $pageId = (int)$params['id'];

        try {

            if (!preg_match("/^[0-9]+$/", $pageId)) {
                Mage::throwException($this->__('Page id is not valid.'));
            }

            /* Copy page */
            $page = Mage::getModel('pagemanager/page')->getPage($pageId);
            $this->sanitizeCopy($page);
            $page['name'] .= ' copy';

            // Insert page
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert('icommerce_pagemanager', $page);
            $lastInsertPageId = (int)$wr->lastInsertId();

            /* Copy rows */
            $rows = Mage::getModel('pagemanager/row')->getRows($pageId);
            foreach ($rows as $row) {
                $rowId = $row['id'];

                $this->sanitizeCopy($row);
                $row['page_id'] = $lastInsertPageId;

                // Insert row
                $r = $wr->insert('icommerce_pagemanager_row', $row);
                $lastInsertRowId = (int)$wr->lastInsertId();

                /* Copy items */
                $items = Mage::getModel('pagemanager/item')->getItems($rowId);
                foreach ($items as $item) {
                    $this->sanitizeCopy($item);
                    $item['page_id'] = $lastInsertPageId;
                    $item['row_id'] = $lastInsertRowId;

                    // Insert item
                    $r = $wr->insert('icommerce_pagemanager_item', $item);
                }
            }

            $message = $this->__('Your page has been copied successfully to <a href="' . $this->getUrl('*/*/edit', array('id' => $lastInsertPageId)) . '">ID ' . $lastInsertPageId . '</a>.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }

    private function sanitizeCopy(&$tableRow) {
        unset($tableRow['id']);
        $tableRow['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $tableRow['created_by'] = Mage::getSingleton('admin/session')->getUser()->getUserId();

        return $tableRow;
    }
}
