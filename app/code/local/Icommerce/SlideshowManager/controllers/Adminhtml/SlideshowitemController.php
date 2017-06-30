<?php

class Icommerce_SlideshowManager_Adminhtml_SlideshowitemController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('cms/slideshowmanager_adminform');
    }

    public function indexAction()
    {
    	try{
	    	$this->loadLayout();
	    	$this->_setActiveMenu('cms/slideshowmanager');
        	$this->renderLayout();
        }
        catch (Exception $e) {
        	$this->_redirect('*/slideshowmanager');
        }
    }

    public function addAction()
    {
       try{
	    	$this->loadLayout();
	    	$this->_setActiveMenu('cms/slideshowmanager');
        	$this->renderLayout();
        }
        catch (Exception $e) {
        	$this->_redirect('*/slideshowmanager');
        }
    }

    public function addhtmlAction()
    {
        try{
	    	$this->loadLayout();
	    	$this->_setActiveMenu('cms/slideshowmanager');
        	$this->renderLayout();
        }
        catch (Exception $e) {
        	$this->_redirect('*/slideshowmanager');
        }
    }

    public function addeasyAction()
    {
        try{
            $this->loadLayout();
            $this->_setActiveMenu('cms/slideshowmanager');
            $this->renderLayout();
        }
        catch (Exception $e) {
            $this->_redirect('*/slideshowmanager');
        }
    }

    public function editeasyAction()
    {
        try{
            $this->loadLayout();
            $this->_setActiveMenu('cms/slideshowmanager');
            $this->renderLayout();
        }
        catch (Exception $e) {
            $this->_redirect('*/slideshowmanager');
        }
    }

    public function addlayeredhtmlAction()
    {
        try{
            $this->loadLayout();
            $this->_setActiveMenu('cms/slideshowmanager');
            $this->renderLayout();
        }
        catch (Exception $e) {
        	$this->_redirect('*/slideshowmanager');
        }
    }

    public function addproductAction()
    {
        try{
            $this->loadLayout();
            $this->_setActiveMenu('cms/slideshowmanager');
            $this->renderLayout();
        }
        catch (Exception $e) {
            $this->_redirect('*/slideshowmanager');
        }
    }

	public function deleteAction()
	{
    	$params = $this->getRequest()->getParams();

		try {

            $id = (int)$params['id'];

			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Item id is not valid.');
				return;
			}

			// Delete the image
			$item = Mage::getModel('slideshowmanager/item')->getItem($id);
			$oldFileName = $item['filename'];
			$helper = Mage::helper('slideshowmanager');
			$oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
			if(file_exists($oldTargetPath)){
				unlink($oldTargetPath);
			}

			// Delete from database
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->delete( 'icommerce_slideshow_item', $where );

            $message = $this->__('Your image has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/slideshowmanager/edit/id/'.$_SESSION['slideshow_id']);
	}

	public function deletehtmlAction()
	{
    	$params = $this->getRequest()->getParams();

		try {

            $id = (int)$params['id'];

			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Item id is not valid.');
				return;
			}

			// Delete the image
			$item = Mage::getModel('slideshowmanager/item')->getItem($id);
			$oldFileName = $item['filename'];
			$helper = Mage::helper('slideshowmanager');
			$oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
			if(file_exists($oldTargetPath)){
				unlink($oldTargetPath);
			}

			// Delete from database
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->delete( 'icommerce_slideshow_item', $where );

            $message = $this->__('Your item has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/slideshowmanager/edit/id/'.$_SESSION['slideshow_id']);
	}

    public function editAction()
    {
    	try{
	    	$params = $this->getRequest()->getParams();
			$id = (int)$params['id'];

			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Item id is not valid.');
				return;
			}

			$item = Mage::getModel('slideshowmanager/item')->getItem($id);

			$this->loadLayout();
			$editBlock = $this->getLayout()->getBlock("edit");
			$editBlock->setData('item', $item);

		    $this->_setActiveMenu('cms/slideshowmanager');
	        $this->renderLayout();
	    }
	    catch (Exception $e) {
        	$this->_redirect('*/slideshowmanager');
        }
    }

    public function editlayeredhtmlAction()
    {
        try{

            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('slideshowmanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("editlayeredhtml");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/slideshowmanager');
            $this->renderLayout();
        }
        catch (Exception $e) {
        	$this->_redirect('*/slideshowmanager');
        }
    }

    public function editproductAction()
    {
        try{

            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('slideshowmanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("editproduct");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/slideshowmanager');
            $this->renderLayout();
        }
        catch (Exception $e) {
            $this->_redirect('*/slideshowmanager');
        }
    }

    public function edithtmlAction()
    {

    	try{
	    	$params = $this->getRequest()->getParams();
			$id = (int)$params['id'];

			if(!preg_match("/^[0-9]+$/", $id )){
				throw new Exception('Item id is not valid.');
				return;
			}

			$item = Mage::getModel('slideshowmanager/item')->getItem($id);

			$this->loadLayout();
			$editBlock = $this->getLayout()->getBlock("edithtml");
			$editBlock->setData('item', $item);

		    $this->_setActiveMenu('cms/slideshowmanager');
	        $this->renderLayout();
	   }
	   catch (Exception $e) {
        	$this->_redirect('*/slideshowmanager');
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

			// File handling
            $fileName = $this->_uploadImage('file');

            $tabletFileName = $this->_uploadImage('backgroundimage_tablet');
            if ($tabletFileName != null){
                $params['backgroundimage_tablet'] = $tabletFileName;
            }
            $mobileFileName = $this->_uploadImage('backgroundimage_mobile');
            if ($mobileFileName != null){
                $params['backgroundimage_mobile'] = $mobileFileName;
            }
			// End of file handling

			$params['filename'] = $fileName;
			$params['slideshow_id'] = $_SESSION['slideshow_id'];
			$params['type'] = $post['addform']['type'];
			$params['title'] = $post['addform']['title'];
			$params['image_text'] = $post['addform']['image_text'];
			$params['image_text_tablet'] = $post['addform']['image_text_tablet'];
			$params['image_text_phone'] = $post['addform']['image_text_phone'];
			$params['image_alt'] = $post['addform']['image_alt'];
			$params['link'] = $post['addform']['link'];
			$params['link_target'] = $post['addform']['link_target'];
            $params['status'] = $post['addform']['status'];
            $params['position'] = $post['addform']['position'];
			$params['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));

			$session = Mage::getSingleton('admin/session');
			$userId = $session->getUser()->getUserId();

			$params['created_by'] = $userId;

			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$r = $wr->insert( 'icommerce_slideshow_item', $params );

            $message = $this->__('Your image has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/slideshowitem/add/');
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }


    public function savelayeredhtmlAction()
    {


        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            // File handling
            $backgroundFileName = $this->_uploadImage('backgroundimage');
            $backgroundTabletFileName = $this->_uploadImage('backgroundimage_tablet');
            $backgroundMobileFileName = $this->_uploadImage('backgroundimage_mobile');
            // End of file handling

            /* New layered parameters */

            $params['backgroundimage'] = $backgroundFileName;
            $params['backgroundimage_tablet'] = $backgroundTabletFileName;
            $params['backgroundimage_mobile'] = $backgroundMobileFileName;
            $params['positiontop'] = $post['positiontop'];
            $params['positiontoptype'] = $post['positiontoptype'];
            $params['positionleft'] = $post['positionleft'];
            $params['positionlefttype'] = $post['positionlefttype'];
            $params['align'] = $post['align'];

            /* /New layered parameters */

            $params['slideshow_id'] = $_SESSION['slideshow_id'];
            $params['type'] = $post['type'];
            $params['title'] = $post['title'];
            $params['slideshow_content'] = $post['slideshow_content'];
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];
            $params['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_slideshow_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/slideshowitem/addlayeredhtml/');
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }


    public function saveproductAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            $title = Mage::getModel('catalog/product')->load($post['product_id'])->getName();

            /* New layered parameters */

            // General
            $params['slideshow_id'] = $_SESSION['slideshow_id'];
            $params['type'] = $post['type'];
            $params['title'] = $title;
            $params['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];

            // Type specific
            $params['product_id'] = $post['product_id'];

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert('icommerce_slideshow_item', $params);

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/slideshowitem/addproduct/');
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }

    public function savehtmlAction()
	{
        $post = $this->getRequest()->getPost();

		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

			$params['slideshow_id'] = $_SESSION['slideshow_id'];
			$params['type'] = $post['type'];
			$params['title'] = $post['title'];
			$params['slideshow_content'] = $post['slideshow_content'];
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];
			$params['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));

			$session = Mage::getSingleton('admin/session');
			$userId = $session->getUser()->getUserId();

			$params['created_by'] = $userId;

			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$r = $wr->insert( 'icommerce_slideshow_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/slideshowitem/addhtml/');
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }


    private function _uploadImage($imageName) {
        if (!isset($_FILES[$imageName])) {
            return false;
        }
        // File handling
        $file = $_FILES[$imageName];
        $fileName = strtolower($file['name']);

        if (empty($fileName)) {
            return null;
        }

        $fileExtension = preg_split("/[\.]+/", $fileName);
        if (is_array($fileExtension) && count($fileExtension) > 1) {
            $fileExtension = $fileExtension[count($fileExtension) - 1];
        }
        $type = $file['type'];
        $tmpName = $file['tmp_name'];
        $error = $file['error'];
        $size = $file['size'];
        $helper = Mage::helper('slideshowmanager');


        if (!$helper->isFileExtensionAllowed($fileExtension)) {
            throw new Exception( $this->__('File extension is not allowed. Only .jpeg, .jpg, .gif and .png is allowed.') );
        }

        if (!$helper->isFileTypeAllowed($type)) {
            throw new Exception( $this->__('File type is not allowed. Only jpeg, gif and png is allowed.') );
        }

        if ($size > $helper->getMaxAllowedFileSize()) {
            throw new Exception( $this->__('Maximum allowed file size is %s KB. Please resize image and try again.', ($helper->getMaxAllowedFileSize() / 1024) ) );
        }

        $targetPath = $helper->getAbsoluteTargetPath() . $helper->getUniqFilename($fileName);

        if (file_exists($targetPath)) {
            throw new Exception( $this->__('The file %s already exist.', $helper->getTargetPath() . $fileName ) );
        }

        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new Exception( $this->__('File could not be moved. Please check write permissions on folder %s.', $helper->getTargetPath() ) );
        }

        return $fileName;

    }


    private function _deleteOldImage($oldImageName) {

        $helper = Mage::helper('slideshowmanager');
        $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldImageName;
        if ($oldImageName && file_exists($oldTargetPath)) {
            unlink($oldTargetPath);
        }
    }


    public function saveeasyAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            $params['slideshow_id'] = $_SESSION['slideshow_id'];
            $params['type'] = $post['type'];
            $params['title'] = $post['title'];
            $params['backgroundimage'] = $this->_uploadImage('backgroundimage');
            $params['title_link'] = $post['title_link'];
            $params['title_position'] = $post['title_position'];
            $params['text_color'] = $post['text_color'];
            $params['subtitle'] = $post['subtitle'];
            $params['button_1_title'] = $post['button_1_title'];
            $params['button_2_title'] = $post['button_2_title'];
            $params['button_3_title'] = $post['button_3_title'];
            $params['button_1_title_link'] = $post['button_1_title_link'];
            $params['button_2_title_link'] = $post['button_2_title_link'];
            $params['button_3_title_link'] = $post['button_3_title_link'];
            $params['button_color'] = $post['button_color'];
            $params['border'] = $post['border'];

            $params['status'] = $post['status'];
            $params['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_slideshow_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/slideshowitem/addeasy/');
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }

    public function updateAction()
	{
        $post = $this->getRequest()->getPost();

		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

			$id = $post['editform']['id'];
            $item = Mage::getModel('slideshowmanager/item')->getItem($id);

            $params = array();

			// File handling

			$fileName = $this->_uploadImage('file');
			if ($fileName != null){
				$params['filename'] = $fileName;
                $this->_deleteOldImage($item['filename']);
			}

			$tabletFileName = $this->_uploadImage('backgroundimage_tablet');
			if ($tabletFileName != null){
				$params['backgroundimage_tablet'] = $tabletFileName;
                $this->_deleteOldImage($item['backgroundimage_tablet']);
			}

			$mobileFileName = $this->_uploadImage('backgroundimage_mobile');
			if ($mobileFileName != null){
				$params['backgroundimage_mobile'] = $mobileFileName;
                $this->_deleteOldImage($item['backgroundimage_mobile']);
			}
            // End of file handling

			$params['title'] = $post['editform']['title'];
			$params['type'] = $post['editform']['type'];
			$params['image_text'] = $post['editform']['image_text'];
			$params['image_text_tablet'] = $post['editform']['image_text_tablet'];
			$params['image_text_phone'] = $post['editform']['image_text_phone'];
			$params['image_alt'] = $post['editform']['image_alt'];
			$params['link'] = $post['editform']['link'];
			$params['link_target'] = $post['editform']['link_target'];
            $params['status'] = $post['editform']['status'];
            $params['position'] = $post['editform']['position'];
            $params['hotspots'] = $post['editform']['hotspots'];

			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->update( 'icommerce_slideshow_item', $params, $where );

            $message = $this->__('Your image has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

			$this->_redirect('*/slideshowitem/edit/id/'. $id);
			return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }

    public function updatehtmlAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['id'];

            $params = array();

            $params['type'] = $post['type'];
            $params['title'] = $post['title'];
            $params['slideshow_content'] = $post['slideshow_content'];
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_slideshow_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/slideshowitem/edithtml/id/'. $id);
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }

    public function updatelayeredhtmlAction()
	{

        $post = $this->getRequest()->getPost();

		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

			$id = $post['id'];
            $item = Mage::getModel('slideshowmanager/item')->getItem($id);

            $params = array();

            // File handling
            $backgroundImageFileName = $this->_uploadImage('backgroundimage');
            if ($backgroundImageFileName != null) {
                $params['backgroundimage'] = $backgroundImageFileName;
                $this->_deleteOldImage($item['backgroundimage']);
            }

            $backgroundImageTabletFileName = $this->_uploadImage('backgroundimage_tablet');
            if ($backgroundImageTabletFileName != null) {
                $params['backgroundimage_tablet'] = $backgroundImageTabletFileName;
                $this->_deleteOldImage($item['backgroundimage_tablet']);
            }

            $backgroundImageMobileFileName = $this->_uploadImage('backgroundimage_mobile');
            if ($backgroundImageMobileFileName != null) {
                $params['backgroundimage_mobile'] = $backgroundImageMobileFileName;
                $this->_deleteOldImage($item['backgroundimage_mobile']);
            }

            // End of file handling

            $params['align'] = $post['align'];
            $params['positiontop'] = $post['positiontop'];
            $params['positiontoptype'] = $post['positiontoptype'];
            $params['positionleft'] = $post['positionleft'];
            $params['positionlefttype'] = $post['positionlefttype'];

            /* /New layered parameters */

			$params['type'] = $post['type'];
			$params['title'] = $post['title'];
			$params['slideshow_content'] = $post['slideshow_content'];
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];

			// Insert
			$wr = Icommerce_Db::getDbWrite();
			$where = $wr->quoteInto('id=?', (int)$id);
			$r = $wr->update( 'icommerce_slideshow_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

			$this->_redirect('*/slideshowitem/editlayeredhtml/id/'. $id);
			return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }

    public function updateproductAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['id'];

            $params = array();

            $title = Mage::getModel('catalog/product')->load($post['product_id'])->getName();

            $params['type'] = $post['type'];
            $params['title'] = $title;
            $params['product_id'] = $post['product_id'];
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_slideshow_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/slideshowitem/editproduct/id/'. $id);
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }

    public function updateeasyAction()
    {

        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['id'];

            $params = array();

            // File handling
            $backgroundImageFileName = $this->_uploadImage('backgroundimage');
            if ($backgroundImageFileName != null) {
                $params['backgroundimage'] = $backgroundImageFileName;
            }

            $backgroundImageTabletFileName = $this->_uploadImage('backgroundimage_tablet');
            if ($backgroundImageTabletFileName != null) {
                $params['backgroundimage_tablet'] = $backgroundImageTabletFileName;
            }

            $backgroundImageMobileFileName = $this->_uploadImage('backgroundimage_mobile');
            if ($backgroundImageMobileFileName != null) {
                $params['backgroundimage_mobile'] = $backgroundImageMobileFileName;
            }
            // End of file handling

            $params['title'] = $post['title'];
            $params['title_link'] = $post['title_link'];
            $params['title_position'] = $post['title_position'];
            $params['text_color'] = $post['text_color'];
            $params['subtitle'] = $post['subtitle'];
            $params['button_1_title'] = $post['button_1_title'];
            $params['button_2_title'] = $post['button_2_title'];
            $params['button_3_title'] = $post['button_3_title'];
            $params['button_1_title_link'] = $post['button_1_title_link'];
            $params['button_2_title_link'] = $post['button_2_title_link'];
            $params['button_3_title_link'] = $post['button_3_title_link'];
            $params['button_color'] = $post['button_color'];
            $params['border'] = $post['border'];
            $params['status'] = $post['status'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_slideshow_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/slideshowitem/editeasy/id/'. $id);
            return;
        }

        $this->_redirect('*/slideshowmanager/edit/id/'. $_SESSION['slideshow_id']);
    }

    public function massUpdateAction(){

        $post = $this->getRequest()->getPost();
        $helper = Mage::helper('slideshowmanager');

		try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

			$action = $post['edit_form']['mass_update_action'];

			if($action == 'delete'){

				$ids = $post['edit_form']['mass_update_id'];

				if(is_array($ids)){

					// Delete the images
					foreach($ids as $id){
						$item = Mage::getModel('slideshowmanager/item')->getItem($id);
						$oldFileName = $item['filename'];
						$oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;

						if(file_exists($oldTargetPath) && is_file($oldTargetPath)){
							unlink($oldTargetPath);
						}
					}

					// Prep for SQL
					$ids = implode(',', $ids);
				}
				else {
					// Delete the image
					$item = Mage::getModel('slideshowmanager/item')->getItem($ids);
					$oldFileName = $item['filename'];
					$oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;

					if(file_exists($oldTargetPath)){
						unlink($oldTargetPath);
					}
				}

				// Delete from database
				$wr = Icommerce_Db::getDbWrite();
				$sql = "DELETE FROM icommerce_slideshow_item WHERE id IN ($ids)";

				$r = $wr->query( $sql );

			}
			else if($action == 'enable') {

				$ids = $post['edit_form']['mass_update_id'];

				if(is_array($ids)){
					$ids = implode(',', $ids);
				}

				$wr = Icommerce_Db::getDbWrite();

				$sql = "UPDATE icommerce_slideshow_item SET status = 1 WHERE id IN ($ids)";

				$r = $wr->query( $sql );
			}
			else if($action == 'disable'){

				$ids = $post['edit_form']['mass_update_id'];

				if(is_array($ids)){
					$ids = implode(',', $ids);
				}

				$wr = Icommerce_Db::getDbWrite();

				$sql = "UPDATE icommerce_slideshow_item SET status = 0 WHERE id IN ($ids)";

				$r = $wr->query( $sql );

			}
			else if($action == 'update'){

				$position = $post['edit_form']['position'];

				$wr = Icommerce_Db::getDbWrite();

				$sql = "";

				foreach($position as $key => $value){
					$sql .= "UPDATE icommerce_slideshow_item SET position = $value WHERE id = $key;";
				}

				$r = $wr->query( $sql );
			}

            $message = $this->__('Your images has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/slideshowmanager/edit/id/'.$_SESSION['slideshow_id']);
    }
}
