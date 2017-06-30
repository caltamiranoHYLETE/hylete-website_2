<?php

class Icommerce_PageManager_Adminhtml_PagerowController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
    	if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
	    	$this->loadLayout();
	    	$this->_setActiveMenu('cms/pagemanager');
        	$this->renderLayout();
        }
    }

    public function addAction()
    {
        if(!$_SESSION['page_id']){
        	$this->_redirect('*/pagemanager');
        }
        else{
	    	$this->loadLayout();
	    	$this->_setActiveMenu('cms/pagemanager');
        	$this->renderLayout();
        }
    }

	public function deleteAction()
	{
    	$params = $this->getRequest()->getParams();

		try {

            $id = (int)$params['id'];

			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Row id is not valid.');
				return;
			}

			// Delete the image
			$row = Mage::getModel('pagemanager/row')->getRow($id);
			$helper = Mage::helper('pagemanager');

			// Delete from database
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->delete( 'icommerce_pagemanager_row', $where );

            $message = $this->__('Your image has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
	}

    public function editAction()
    {
    	if(!$_SESSION['page_id']){
        	$this->_redirect('*/pagemanager');
        }
        else{
	    	$params = $this->getRequest()->getParams();
			$id = (int)$params['id'];

			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Row id is not valid.');
				return;
			}

			$row = Mage::getModel('pagemanager/row')->getRow($id);

			$this->loadLayout();
			$editBlock = $this->getLayout()->getBlock("edit");
			$editBlock->setData('row', $row);

		    $this->_setActiveMenu('cms/pagemanager');
	        $this->renderLayout();
	    }
    }

    public function saveAction()
	{

		$post = $this->getRequest()->getPost();

		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();
            $params['page_id'] = $_SESSION['page_id'];
            $params['status'] = $post['addform']['status'];
            $params['position'] = $post['addform']['position'];
            $params['type'] = $post['addform']['type'];
			$params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            /* Visibility/Classnames */
            $classnames = '';

            if (isset($post['addform']['predefined_classnames'])) {
                $visibility = $post['addform']['predefined_classnames'];

                foreach ($visibility as $v) {
                    $classnames .= $v . ' ';
                }
            }

            if (isset($post['addform']['classnames'])) {
                $classnames .= $post['addform']['classnames'];
            }

            $params['classnames'] = $classnames;
            // End of visibility/classnames

			$session = Mage::getSingleton('admin/session');
			$userId = $session->getUser()->getUserId();

			$params['created_by'] = $userId;

			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$r = $wr->insert( 'icommerce_pagemanager_row', $params );

            $message = $this->__('Your page has been saved successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/pagerow/add/');
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);

    }

    public function updateAction()
	{
        $post = $this->getRequest()->getPost();

		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

			$id = $post['editform']['id'];

            $params = array();

			$params['page_id'] = $_SESSION['page_id'];
            $params['status'] = $post['editform']['status'];
            $params['position'] = $post['editform']['position'];
            $params['type'] = $post['editform']['type'];

            /* Predefined classnames */
            $classnames = '';
            if (isset($post['editform']['predefined_classnames'])) {
                $predefined = $post['editform']['predefined_classnames'];

                foreach ($predefined as $p) {
                    $classnames .= $p . ' ';
                }
            }

            if (isset($post['editform']['classnames'])){
                $classnames .= $post['editform']['classnames'];
            }

            $params['classnames'] = $classnames;

			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->update( 'icommerce_pagemanager_row', $params, $where );

            $message = $this->__('Your image has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

			$this->_redirect('*/pagerow/edit/id/'. $id);
			return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function addBlockAction(){

        $post = $this->getRequest()->getPost();

        $rowid = $post['editform']['row_id'];
        $itemtype = $post['editform']['itemtype'];

		$this->_redirect('*/pageitem/add'.$itemtype.'/id/'.$rowid);
    }

    public function massUpdateAction(){

        $post = $this->getRequest()->getPost();
        $helper = Mage::helper('pagemanager');

		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

			$action = $post['editform']['mass_update_action'];
			$itemType = $post['editform']['itemtype'];
			$rowId = $post['editform']['row_id'];

			if($itemType != "" && $itemType != null){
				if($itemType == 'edit_row'){
					$this->_redirect('*/pagerow/edit/id/'.$rowId);
				}
				else{
			    	$this->_redirect('*/pageitem/add'.$itemType.'/id/'.$rowId);
			    }
			}
			if($action != "" && $action != null){
				if($action == 'delete'){

					$rowIds = isset($post['editform']['mass_update_row_id']) ? $post['editform']['mass_update_row_id'] : null;
					$itemIds = isset($post['editform']['mass_update_item_id']) ? $post['editform']['mass_update_item_id'] : null;

					if(is_array($itemIds)){

						// Delete the items
						foreach($itemIds as $id){
							$item = Mage::getModel('pagemanager/item')->getItem($id);
							$oldFileName = $item['filename'];
                            if($oldFileName != null){
                                $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;

                                if(file_exists($oldTargetPath)){
                                    unlink($oldTargetPath);
                                }
                            }
						}
					}
					if(is_array($rowIds)){

						// Delete the items
						foreach($rowIds as $id){
							$item = Mage::getModel('pagemanager/item')->getItem($id);
							$oldFileName = $item['filename'];
                            if($oldFileName != null){
                                $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;

                                if(file_exists($oldTargetPath)){
                                    unlink($oldTargetPath);
                                }
                            }
						}
					}

					// Delete from database
					$wr = Icommerce_Db::getDbWrite();
					$sql = "";
					if(is_array($rowIds)){
						$rowIds = implode(',', $rowIds);
						$sql .= "DELETE FROM icommerce_pagemanager_row WHERE id IN ($rowIds);";
					}
					if(is_array($itemIds)){
						$itemIds = implode(',', $itemIds);
						$sql .= "DELETE FROM icommerce_pagemanager_item WHERE id IN ($itemIds);";
					}

					$r = $wr->query( $sql );

				}
				else if($action == 'enable') {

				   $rowIds = isset($post['editform']['mass_update_row_id']) ? $post['editform']['mass_update_row_id'] : null;
                   $itemIds = isset($post['editform']['mass_update_item_id']) ? $post['editform']['mass_update_item_id'] : null;


					$wr = Icommerce_Db::getDbWrite();

					$sql = "";
					if(is_array($rowIds)){
						$rowIds = implode(',', $rowIds);
						$sql .= "UPDATE icommerce_pagemanager_row SET status = 1 WHERE id IN ($rowIds);";
					}
					if(is_array($itemIds)){
						$itemIds = implode(',', $itemIds);
						$sql .= "UPDATE icommerce_pagemanager_item SET status = 1 WHERE id IN ($itemIds);";
					}

					$r = $wr->query( $sql );
				}
				else if($action == 'disable'){

					$rowIds = isset($post['editform']['mass_update_row_id']) ? $post['editform']['mass_update_row_id'] : null;
					$itemIds = isset($post['editform']['mass_update_item_id']) ? $post['editform']['mass_update_item_id'] : null;

					$wr = Icommerce_Db::getDbWrite();
					$sql = "";
					if(is_array($rowIds)){
						$rowIds = implode(',', $rowIds);
						$sql .= "UPDATE icommerce_pagemanager_row SET status = 0 WHERE id IN ($rowIds);";
					}
					if(is_array($itemIds)){
						$itemIds = implode(',', $itemIds);
						$sql .= "UPDATE icommerce_pagemanager_item SET status = 0 WHERE id IN ($itemIds);";
					}

					$r = $wr->query( $sql );

				}
				else if($action == 'update'){

					$rowPosition = $post['editform']['row_position'];
					$itemPosition = $post['editform']['item_position'];

					$wr = Icommerce_Db::getDbWrite();

					$sql = "";

					foreach($rowPosition as $key => $value){
						$sql .= "UPDATE icommerce_pagemanager_row SET position = $value WHERE id = $key;";
					}
					foreach($itemPosition as $key => $value){
						$sql .= "UPDATE icommerce_pagemanager_item SET position = $value WHERE id = $key;";
					}

					$r = $wr->query( $sql );
				}

	            $message = $this->__('Your rows and items has been updated successfully.');
	            Mage::getSingleton('adminhtml/session')->addSuccess($message);
	    	}

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
		if($action != "" && $action != null){
        	$this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
        }
    }


}
