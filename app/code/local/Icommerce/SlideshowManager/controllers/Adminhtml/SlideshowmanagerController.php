<?php

class Icommerce_SlideshowManager_Adminhtml_SlideshowmanagerController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('cms/slideshowmanager_adminform');
    }

    public function indexAction()
    {
	    $this->loadLayout();
	    $this->_setActiveMenu('cms/slideshowmanager');
        $this->renderLayout();
    }

    public function addAction()
	{
	    $this->loadLayout();
	    $this->_setActiveMenu('cms/slideshowmanager');
        $this->renderLayout();
    }

    public function deleteAction()
	{
    	$params = $this->getRequest()->getParams();

		try {

            $id = (int)$params['id'];

			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Slideshow id is not valid.');
				return;
			}

			$helper = Mage::helper('slideshowmanager');

			// Read all existing images in this slideshow and delete them
			$r = Icommerce_Db::getDbRead();
			$rows = $r->query( "SELECT * FROM icommerce_slideshow_item WHERE slideshow_id = $id" );

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
			$r = $wr->delete( 'icommerce_slideshow', $where );

            $message = $this->__('Your slideshow has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/slideshowmanager/index/');
	}

    public function editAction()
	{
    	$params = $this->getRequest()->getParams();

		$id = (int)$params['id'];

		if(!preg_match("/^[0-9]+$/", $id )){
			throw new Exception('Slideshow id is not valid.');
			return;
		}

		$slideshow = Mage::getModel('slideshowmanager/slideshow')->getSlideshow($id);

		$this->loadLayout();
		$editBlock = $this->getLayout()->getBlock("edit");
		$editBlock->setData('slideshow', $slideshow);

	    $this->_setActiveMenu('cms/slideshowmanager');
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
            $params['width'] = $post['addform']['width'];
            $params['height'] = $post['addform']['height'];
            $params['thumbnails'] = $post['addform']['thumbnails'];
            $params['status'] = $post['addform']['status'];
            $params['position'] = $post['addform']['position'];
			$params['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));
            $validFrom = date('Y-m-d', strtotime($post['addform']['valid_from']));
            $validTo   = date('Y-m-d', strtotime($post['addform']['valid_to']));

            if($validFrom == '1970-01-01' || $validTo == '1970-01-01') {
                    $validFrom = null;
                    $validTo   = null;
            }

            $params['valid_from'] = $validFrom;
            $params['valid_to'] = $validTo;

			$session = Mage::getSingleton('admin/session');
			$userId = $session->getUser()->getUserId();

			$params['created_by'] = $userId;

			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$r = $wr->insert( 'icommerce_slideshow', $params );

            $message = $this->__('Your slideshow has been saved successfully.');
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
			$params['width'] = $post['addform']['width'];
            $params['height'] = $post['addform']['height'];
            $params['thumbnails'] = $post['addform']['thumbnails'];
            $params['status'] = $post['addform']['status'];
            $params['position'] = $post['addform']['position'];

            $validFrom = date('Y-m-d', strtotime($post['addform']['valid_from']));
            $validTo   = date('Y-m-d', strtotime($post['addform']['valid_to']));

            if($validFrom == '1970-01-01' || $validTo == '1970-01-01') {
                $validFrom = null;
                $validTo   = null;
            }

            $params['valid_from'] = $validFrom;
            $params['valid_to'] = $validTo;

			$id = (int)$post['addform']['slideshow_id'];

			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Slideshow id is not valid.');
				return;
			}

			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->update( 'icommerce_slideshow', $params, $where);

            $message = $this->__('Your slideshow has been updated successfully.');
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
				$sql = "DELETE FROM icommerce_slideshow WHERE id IN ($ids)";

				$r = $wr->query( $sql );

			}
			else if($action == 'enable') {

				$ids = $post['edit_form']['mass_update_id'];

				if(is_array($ids)){
					$ids = implode(',', $ids);
				}

				$wr = Icommerce_Db::getDbWrite();

				$sql = "UPDATE icommerce_slideshow SET status = 1 WHERE id IN ($ids)";

				$r = $wr->query( $sql );
			}
			else if($action == 'disable'){

				$ids = $post['edit_form']['mass_update_id'];

				if(is_array($ids)){
					$ids = implode(',', $ids);
				}

				$wr = Icommerce_Db::getDbWrite();

				$sql = "UPDATE icommerce_slideshow SET status = 0 WHERE id IN ($ids)";

				$r = $wr->query( $sql );
			}
			else if($action == 'update'){

				$position = $post['edit_form']['position'];

				$wr = Icommerce_Db::getDbWrite();

				$sql = "";

				foreach($position as $key => $value){
					$sql .= "UPDATE icommerce_slideshow SET position = $value WHERE id = $key;";
				}

				$r = $wr->query( $sql );
			}

            $message = $this->__('Your slideshows has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }

    protected function _duplicateSlideShowItemFile($filename)
    {
        $helper = Mage::helper('slideshowmanager');

        $newFilename = $helper->getUniqFilename($filename);
        $basePath = $helper->getAbsoluteTargetPath();

        if (!copy($basePath . DS . $filename, $basePath . DS . $newFilename)) {
            Mage::throwException('Could not duplicate files');
        }

        return $newFilename;
    }

    public function duplicateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $dataForNew = Mage::getModel('slideshowmanager/slideshow')->getSlideshow($id);

        $userId = Mage::getSingleton('admin/session')->getUser()->getUserId();

        // Reset some params
        unset($dataForNew['id']);
        $dataForNew['created_by'] = $userId;
        $dataForNew['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));

        $model = Mage::getModel('slideshowmanager/slideshow');
        try {
            $model->setData($dataForNew);
            $model->save();

            $oldItems = Mage::getModel('slideshowmanager/item')->getItems($id);
            foreach ($oldItems as $oldItem) {
                $itemModel = Mage::getModel('slideshowmanager/item');

                unset($oldItem['id']);
                $oldItem['created_by'] = $userId;
                $oldItem['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));
                $oldItem['slideshow_id'] = $model->getId();

                foreach (array('filename', 'backgroundimage', 'backgroundimage_tablet', 'backgroundimage_mobile') as $image) {
                    if (!empty($oldItem[$image])) {
                        $oldItem[$image] = $this->_duplicateSlideShowItemFile($oldItem[$image]);
                    }
                }

                $itemModel->setData($oldItem);
                $itemModel->save();
            }

            $message = $this->__('Your slideshow has been saved successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
            $this->_redirect('*/*/edit', array('id' => $model->getId()));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/index');
        }
    }
}
