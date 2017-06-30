<?php

class Icommerce_PageManager_Adminhtml_PageitemController extends Mage_Adminhtml_Controller_Action
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

    public function addimageAction()
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

    public function addimagewithoverlayAction()
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

    public function addhtmlAction()
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

    public function addslideshowAction()
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

    public function addheadingAction()
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

    public function addtoplistAction()
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

    public function addcategoryAction()
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

    public function addwidgetAction()
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

    public function deleteimageAction()
    {
        $params = $this->getRequest()->getParams();

        try {

            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            // Delete the image
            $item = Mage::getModel('pagemanager/item')->getItem($id);
            $oldFileName = $item['filename'];
            $helper = Mage::helper('pagemanager');
            $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
            if(file_exists($oldTargetPath)){
                unlink($oldTargetPath);
            }

            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->delete( 'icommerce_pagemanager_item', $where );

            $message = $this->__('Your image has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
    }

    public function deleteimagewithoverlayAction()
    {
        $params = $this->getRequest()->getParams();

        try {

            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            // Delete the image
            $item = Mage::getModel('pagemanager/item')->getItem($id);
            $oldFileName = $item['filename'];
            $oldFileNameBig = $item['big_filename'];
            $helper = Mage::helper('pagemanager');
            $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
            $oldTargetPathBig = $helper->getAbsoluteTargetPath() . $oldFileNameBig;
            if(file_exists($oldTargetPath)){
                unlink($oldTargetPath);
            }
            if(file_exists($oldTargetPathBig)){
                unlink($oldTargetPathBig);
            }

            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->delete( 'icommerce_pagemanager_item', $where );

            $message = $this->__('Your image has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
    }

    public function deleteslideshowAction()
    {
        $params = $this->getRequest()->getParams();

        try {

            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            // Delete the image
            $item = Mage::getModel('pagemanager/item')->getItem($id);
            $helper = Mage::helper('pagemanager');

            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->delete( 'icommerce_pagemanager_item', $where );

            $message = $this->__('Your image has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
    }

    public function deletetoplistAction()
    {
        $params = $this->getRequest()->getParams();

        try {

            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            // Delete the image
            $item = Mage::getModel('pagemanager/item')->getItem($id);
            $helper = Mage::helper('pagemanager');

            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->delete( 'icommerce_pagemanager_item', $where );

            $message = $this->__('Your image has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
    }

    public function deletecategoryAction()
    {
        $params = $this->getRequest()->getParams();

        try {

            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            // Delete the image
            $item = Mage::getModel('pagemanager/item')->getItem($id);
            $helper = Mage::helper('pagemanager');

            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->delete( 'icommerce_pagemanager_item', $where );

            $message = $this->__('Your category has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
    }

    public function deleteheadingAction()
    {
        $params = $this->getRequest()->getParams();

        try {

            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            // Delete the image
            $item = Mage::getModel('pagemanager/item')->getItem($id);
            $helper = Mage::helper('pagemanager');

            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->delete( 'icommerce_pagemanager_item', $where );

            $message = $this->__('Your image has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
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
            $item = Mage::getModel('pagemanager/item')->getItem($id);
            $oldFileName = $item['filename'];
            $helper = Mage::helper('pagemanager');
            $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
            if(file_exists($oldTargetPath)){
                unlink($oldTargetPath);
            }

            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->delete( 'icommerce_pagemanager_item', $where );

            $message = $this->__('Your item has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
    }

    public function deletewidgetAction()
    {
        $params = $this->getRequest()->getParams();

        try {

            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            // Delete the image
            $item = Mage::getModel('pagemanager/item')->getItem($id);
            $oldFileName = $item['filename'];
            $helper = Mage::helper('pagemanager');
            $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
            if(file_exists($oldTargetPath)){
                unlink($oldTargetPath);
            }

            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->delete( 'icommerce_pagemanager_item', $where );

            $message = $this->__('Your item has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/pagemanager/edit/id/'.$_SESSION['page_id']);
    }

    public function editimageAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("editimage");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function editimagewithoverlayAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("editimagewithoverlay");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function edittoplistAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("edittoplist");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function editcategoryAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("editcategory");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function editslideshowAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("editslideshow");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }


    public function editheadingAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("editheading");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function edithtmlAction()
    {

        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("edithtml");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function editwidgetAction()
    {

        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("editwidget");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function copyheadingAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("copyheading");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function copytoplistAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("copytoplist");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function copycategoryAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("copycategory");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function copyslideshowAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("copyslideshow");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function copyhtmlAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("copyhtml");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function copyimageAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("copyimage");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }


    public function copyimagewithoverlayAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("copyimagewithoverlay");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function copywidgetAction()
    {
        if(!$_SESSION['page_id']){
            $this->_redirect('*/pagemanager');
        }
        else{
            $params = $this->getRequest()->getParams();
            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id )){
                throw new Exception('Item id is not valid.');
                return;
            }

            $item = Mage::getModel('pagemanager/item')->getItem($id);

            $this->loadLayout();
            $editBlock = $this->getLayout()->getBlock("copyhtml");
            $editBlock->setData('item', $item);

            $this->_setActiveMenu('cms/pagemanager');
            $this->renderLayout();
        }
    }

    public function saveimageAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            // File handling
            $file = $_FILES['file'];
            $fileName = strtolower( $file['name'] );
            $fileExtension = preg_split("/[\.]+/", $fileName);
            if( is_array($fileExtension) && count($fileExtension) > 1){
                $fileExtension = $fileExtension[count($fileExtension) - 1];
            }
            $type = $file['type'];
            $tmpName = $file['tmp_name'];
            $error = $file['error'];
            $size = $file['size'];
            $helper = Mage::helper('pagemanager');


            if( !$helper->isFileExtensionAllowed($fileExtension) ) {
                throw new Exception( $this->__('File extension is not allowed. Only .jpeg, .jpg, .gif and .png is allowed.') );
            }

            if( !$helper->isFileTypeAllowed($type) ) {
                throw new Exception( $this->__('File type is not allowed. Only jpeg, gif and png is allowed.') );
            }

            if( $size > $helper->getMaxAllowedFileSize() ){
                throw new Exception( $this->__('Maximum allowed file size is %s KB. Please resize image and try again.', ($helper->getMaxAllowedFileSize() / 1024) ) );
            }

            $fileName = date('Y-m-d-H-i-s', Mage::getModel('core/date')->timestamp(time()));
            $fileName .= rand();
            $fileName .= '.'.$fileExtension;

            $targetPath = $helper->getAbsoluteTargetPath() . $fileName;

            if(file_exists($targetPath)){
                throw new Exception( $this->__('The file %s already exist.', $helper->getTargetPath().$fileName ) );
            }

            if(!move_uploaded_file($tmpName, $targetPath)) {
                throw new Exception( $this->__('File could not be moved. Please check write permissions on folder %s.', $helper->getTargetPath() ) );
            }

            // End of file handling

            $params['filename'] = $fileName;
            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['addimageform']['row_id'];
            $params['type'] = $post['addimageform']['type'];
            $params['title'] = $post['addimageform']['title'];
            $params['image_text'] = $post['addimageform']['image_text'];
            $params['image_alt'] = $post['addimageform']['image_alt'];
            $params['link'] = $post['addimageform']['link'];
            $params['link_target'] = $post['addimageform']['link_target'];
            $params['status'] = $post['addimageform']['status'];
            $params['position'] = $post['addimageform']['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your image has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addimage/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function copyandsaveimageAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();


            // End of file handling

            $params['filename'] =  $post['addimageform']['filename'];
            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['addimageform']['row_id'];
            $params['type'] = $post['addimageform']['type'];
            $params['title'] = $post['addimageform']['title'];
            $params['image_text'] = $post['addimageform']['image_text'];
            $params['image_alt'] = $post['addimageform']['image_alt'];
            $params['link'] = $post['addimageform']['link'];
            $params['link_target'] = $post['addimageform']['link_target'];
            $params['status'] = $post['addimageform']['status'];
            $params['position'] = $post['addimageform']['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your image has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addimage/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function saveimagewithoverlayAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            // File handling
            $file = $_FILES['file'];
            $fileName = strtolower( $file['name'] );
            $fileExtension = preg_split("/[\.]+/", $fileName);
            if( is_array($fileExtension) && count($fileExtension) > 1){
                $fileExtension = $fileExtension[count($fileExtension) - 1];
            }
            $type = $file['type'];
            $tmpName = $file['tmp_name'];
            $error = $file['error'];
            $size = $file['size'];
            $helper = Mage::helper('pagemanager');


            if( !$helper->isFileExtensionAllowed($fileExtension) ) {
                throw new Exception( $this->__('File extension is not allowed. Only .jpeg, .jpg, .gif and .png is allowed.') );
            }

            if( !$helper->isFileTypeAllowed($type) ) {
                throw new Exception( $this->__('File type is not allowed. Only jpeg, gif and png is allowed.') );
            }

            if( $size > $helper->getMaxAllowedFileSize() ){
                throw new Exception( $this->__('Maximum allowed file size is %s KB. Please resize image and try again.', ($helper->getMaxAllowedFileSize() / 1024) ) );
            }

            $fileName = date('Y-m-d-H-i-s', Mage::getModel('core/date')->timestamp(time()));
            $fileName .= rand();
            $fileName .= '.'.$fileExtension;

            $targetPath = $helper->getAbsoluteTargetPath() . $fileName;

            if(file_exists($targetPath)){
                throw new Exception( $this->__('The file %s already exist.', $helper->getTargetPath().$fileName ) );
            }

            if(!move_uploaded_file($tmpName, $targetPath)) {
                throw new Exception( $this->__('File could not be moved. Please check write permissions on folder %s.', $helper->getTargetPath() ) );
            }

            // File handling big image
            $fileBig = $_FILES['file_big'];
            $fileNameBig = strtolower( $fileBig['name'] );
            $fileExtensionBig = preg_split("/[\.]+/", $fileNameBig);
            if( is_array($fileExtensionBig) && count($fileExtensionBig) > 1){
                $fileExtensionBig = $fileExtensionBig[count($fileExtensionBig) - 1];
            }
            $typeBig = $fileBig['type'];
            $tmpNameBig = $fileBig['tmp_name'];
            $errorBig = $fileBig['error'];
            $sizeBig = $fileBig['size'];


            if( !$helper->isFileExtensionAllowed($fileExtensionBig) ) {
                throw new Exception( $this->__('File extension is not allowed. Only .jpeg, .jpg, .gif and .png is allowed.') );
            }

            if( !$helper->isFileTypeAllowed($typeBig) ) {
                throw new Exception( $this->__('File type is not allowed. Only jpeg, gif and png is allowed.') );
            }

            if( $sizeBig > $helper->getMaxAllowedFileSize() ){
                throw new Exception( $this->__('Maximum allowed file size is %s KB. Please resize image and try again.', ($helper->getMaxAllowedFileSize() / 1024) ) );
            }

            $fileNameBig = date('Y-m-d-H-i-s', Mage::getModel('core/date')->timestamp(time()));
            $fileNameBig .= rand();
            $fileNameBig .= '.'.$fileExtension;

            $targetPathBig = $helper->getAbsoluteTargetPath() . $fileNameBig;

            if(file_exists($targetPathBig)){
                throw new Exception( $this->__('The file %s already exist.', $helper->getTargetPath().$fileNameBig ) );
            }

            if(!move_uploaded_file($tmpNameBig, $targetPathBig)) {
                throw new Exception( $this->__('File could not be moved. Please check write permissions on folder %s.', $helper->getTargetPath() ) );
            }

            // End of file handling

            $params['filename'] = $fileName;
            $params['filename_big'] = $fileNameBig;
            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['addimagewithoverlayform']['row_id'];
            $params['type'] = $post['addimagewithoverlayform']['type'];
            $params['title'] = $post['addimagewithoverlayform']['title'];
            $params['image_text'] = $post['addimagewithoverlayform']['image_text'];
            $params['image_alt'] = $post['addimagewithoverlayform']['image_alt'];
            $params['link'] = $post['addimagewithoverlayform']['link'];
            $params['link_target'] = $post['addimagewithoverlayform']['link_target'];
            $params['status'] = $post['addimagewithoverlayform']['status'];
            $params['position'] = $post['addimagewithoverlayform']['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your image has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addimagewithoverlay/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function copyandsaveimagewithoverlayAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            // End of file handling

            $params['filename'] = $post['addimagewithoverlayform']['filename'];
            $params['filename_big'] = $post['addimagewithoverlayform']['filename_big'];
            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['addimagewithoverlayform']['row_id'];
            $params['type'] = $post['addimagewithoverlayform']['type'];
            $params['title'] = $post['addimagewithoverlayform']['title'];
            $params['image_text'] = $post['addimagewithoverlayform']['image_text'];
            $params['image_alt'] = $post['addimagewithoverlayform']['image_alt'];
            $params['link'] = $post['addimagewithoverlayform']['link'];
            $params['link_target'] = $post['addimagewithoverlayform']['link_target'];
            $params['status'] = $post['addimagewithoverlayform']['status'];
            $params['position'] = $post['addimagewithoverlayform']['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your image has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addimagewithoverlay/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }


    public function savetoplistAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();
            $paramsKeys = array('row_id', 'type', 'title', 'status', 'category_id', 'total_products', 'toplist', 'sort_by', 'position');
            $addtoplistform = $post['addtoplistform'];

            $params['page_id'] = $_SESSION['page_id'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
            foreach($paramsKeys as $key){
                if(isset($addtoplistform[$key])){
                    $params[$key] = $addtoplistform[$key];
                }
            }
            $params['page_content'] = isset($addtoplistform['product_ids']) ? $addtoplistform['product_ids'] : ""; //reusing field page_content for ids

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addtoplist/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function savecategoryAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['addcategoryform']['row_id'];
            $params['type'] = $post['addcategoryform']['type'];
            $params['title'] = $post['addcategoryform']['title'];
            $params['status'] = $post['addcategoryform']['status'];
            $params['category_id'] = $post['addcategoryform']['category_id'];
            $params['position'] = $post['addcategoryform']['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addcategory/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function saveslideshowAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['addslideshowform']['row_id'];
            $params['type'] = $post['addslideshowform']['type'];
            $params['title'] = $post['addslideshowform']['title'];
            $params['status'] = $post['addslideshowform']['status'];
            //$params['visibility'] = $post['addslideshowform']['visibility'];
            $params['slideshow'] = $post['addslideshowform']['slideshow'];
            $params['position'] = $post['addslideshowform']['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            if( $post['addslideshowform']['slideshow'] == "" ){
                throw new Exception( $this->__('You must choose a slideshow.' ) );
            }

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addslideshow/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function saveheadingAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['addheadingform']['row_id'];
            $params['type'] = $post['addheadingform']['type'];
            $params['title'] = $post['addheadingform']['title'];
            $params['heading'] = $post['addheadingform']['heading'];
            $params['status'] = $post['addheadingform']['status'];
            //$params['visibility'] = $post['addheadingform']['visibility'];
            $params['position'] = $post['addheadingform']['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addheading/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function savehtmlAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['row_id'];
            $params['type'] = $post['type'];
            $params['title'] = $post['title'];
            $params['page_content'] = $post['page_content'];
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addhtml/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function savewidgetAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['row_id'];
            $params['type'] = $post['type'];
            $params['title'] = $post['title'];
            $params['page_content'] = $this->_getWidgetDeclaration($post['widget_type'], isset($post['parameters']) ? $post['parameters'] : array());
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/addwidget/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function copyandsavehtmlAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }



            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['copyandsavehtmlform']['row_id'];
            $params['type'] = $post['copyandsavehtmlform']['type'];
            $params['title'] = $post['copyandsavehtmlform']['title'];
            $params['page_content'] = $post['copyandsavehtmlform']['page_content'];
            $params['status'] = $post['copyandsavehtmlform']['status'];
            $params['position'] = $post['copyandsavehtmlform']['position'];
            $params['created_on'] = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $r = $wr->insert( 'icommerce_pagemanager_item', $params );

            $message = $this->__('Your item has been saved successfully.');

            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/copyhtml/');
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }


    public function updateimageAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['editimageform']['id'];

            $params = array();

            // File handling
            $file = $_FILES['file'];
            $fileName = strtolower( $file['name'] );

            if($fileName != ''){

                // The user uploads a new image
                $fileExtension = preg_split("/[\.]+/", $fileName);
                if( is_array($fileExtension) && count($fileExtension) > 1){
                    $fileExtension = $fileExtension[count($fileExtension) - 1];
                }
                $type = $file['type'];
                $tmpName = $file['tmp_name'];
                $error = $file['error'];
                $size = $file['size'];
                $helper = Mage::helper('pagemanager');

                if( !$helper->isFileExtensionAllowed($fileExtension) ) {
                    throw new Exception( $this->__('File extension is not allowed. Only .jpeg, .jpg, .gif and .png is allowed.') );
                }

                if( !$helper->isFileTypeAllowed($type) ) {
                    throw new Exception( $this->__('File type is not allowed. Only jpeg, gif and png is allowed.') );
                }

                if( $size > $helper->getMaxAllowedFileSize() ){
                    throw new Exception( $this->__('Maximum allowed file size is %s KB. Please resize image and try again.', ($helper->getMaxAllowedFileSize() / 1024) ) );
                }

                $fileName = date('Y-m-d-H-i-s', Mage::getModel('core/date')->timestamp(time()));
                $fileName .= rand();
                $fileName .= '.'.$fileExtension;

                $targetPath = $helper->getAbsoluteTargetPath() . $fileName;

                if(file_exists($targetPath)){
                    throw new Exception( $this->__('The file %s already exist.', $helper->getTargetPath().$fileName ) );
                }

                if(!move_uploaded_file($tmpName, $targetPath)) {
                    throw new Exception( $this->__('File could not be moved. Please check write permissions on folder %s.', $helper->getTargetPath() ) );
                }

                // Delete the old image
                $item = Mage::getModel('pagemanager/item')->getItem($id);
                $oldFileName = $item['filename'];
                $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
                if(file_exists($oldTargetPath)){
                    unlink($oldTargetPath);
                }

                $params['filename'] = $fileName;
                // End of file handling
            }

            $params['title'] = $post['editimageform']['title'];
            $params['type'] = $post['editimageform']['type'];
            $params['image_text'] = $post['editimageform']['image_text'];
            $params['image_alt'] = $post['editimageform']['image_alt'];
            $params['link'] = $post['editimageform']['link'];
            $params['link_target'] = $post['editimageform']['link_target'];
            $params['status'] = $post['editimageform']['status'];
            $params['position'] = $post['editimageform']['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_pagemanager_item', $params, $where );

            $message = $this->__('Your image has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/editimage/id/'. $id);
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function updateimagewithoverlayAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['editimagewithoverlayform']['id'];

            $params = array();

            // File handling
            $file = $_FILES['file'];
            $fileName = strtolower( $file['name'] );

            $fileBig = $_FILES['file_big'];
            $fileNameBig = strtolower( $fileBig['name'] );
            $helper = Mage::helper('pagemanager');

            if($fileName != ''){

                // The user uploads a new image
                $fileExtension = preg_split("/[\.]+/", $fileName);
                if( is_array($fileExtension) && count($fileExtension) > 1){
                    $fileExtension = $fileExtension[count($fileExtension) - 1];
                }
                $type = $file['type'];
                $tmpName = $file['tmp_name'];
                $error = $file['error'];
                $size = $file['size'];


                if( !$helper->isFileExtensionAllowed($fileExtension) ) {
                    throw new Exception( $this->__('File extension is not allowed. Only .jpeg, .jpg, .gif and .png is allowed.') );
                }

                if( !$helper->isFileTypeAllowed($type) ) {
                    throw new Exception( $this->__('File type is not allowed. Only jpeg, gif and png is allowed.') );
                }

                if( $size > $helper->getMaxAllowedFileSize() ){
                    throw new Exception( $this->__('Maximum allowed file size is %s KB. Please resize image and try again.', ($helper->getMaxAllowedFileSize() / 1024) ) );
                }

                $fileName = date('Y-m-d-H-i-s', Mage::getModel('core/date')->timestamp(time()));
                $fileName .= rand();
                $fileName .= '.'.$fileExtension;

                $targetPath = $helper->getAbsoluteTargetPath() . $fileName;

                if(file_exists($targetPath)){
                    throw new Exception( $this->__('The file %s already exist.', $helper->getTargetPath().$fileName ) );
                }

                if(!move_uploaded_file($tmpName, $targetPath)) {
                    throw new Exception( $this->__('File could not be moved. Please check write permissions on folder %s.', $helper->getTargetPath() ) );
                }

                // Delete the old image
                $item = Mage::getModel('pagemanager/item')->getItem($id);
                $oldFileName = $item['filename'];
                $oldTargetPath = $helper->getAbsoluteTargetPath() . $oldFileName;
                if(file_exists($oldTargetPath)){
                    unlink($oldTargetPath);
                }

                $params['filename'] = $fileName;
                // End of file handling
            }

            if($fileNameBig != ''){

                // The user uploads a new image
                $fileExtensionBig = preg_split("/[\.]+/", $fileNameBig);
                if( is_array($fileExtensionBig) && count($fileExtensionBig) > 1){
                    $fileExtensionBig = $fileExtensionBig[count($fileExtensionBig) - 1];
                }
                $typeBig = $fileBig['type'];
                $tmpNameBig = $fileBig['tmp_name'];
                $errorBig = $fileBig['error'];
                $sizeBig = $fileBig['size'];

                if( !$helper->isFileExtensionAllowed($fileExtensionBig) ) {
                    throw new Exception( $this->__('File extension is not allowed. Only .jpeg, .jpg, .gif and .png is allowed.') );
                }

                if( !$helper->isFileTypeAllowed($typeBig) ) {
                    throw new Exception( $this->__('File type is not allowed. Only jpeg, gif and png is allowed.') );
                }

                if( $sizeBig > $helper->getMaxAllowedFileSize() ){
                    throw new Exception( $this->__('Maximum allowed file size is %s KB. Please resize image and try again.', ($helper->getMaxAllowedFileSize() / 1024) ) );
                }

                $fileNameBig = date('Y-m-d-H-i-s', Mage::getModel('core/date')->timestamp(time()));
                $fileNameBig .= rand();
                $fileNameBig .= '.'.$fileExtensionBig;

                $targetPathBig = $helper->getAbsoluteTargetPath() . $fileNameBig;

                if(file_exists($targetPathBig)){
                    throw new Exception( $this->__('The file %s already exist.', $helper->getTargetPath().$fileNameBig ) );
                }

                if(!move_uploaded_file($tmpName, $targetPathBig)) {
                    throw new Exception( $this->__('File could not be moved. Please check write permissions on folder %s.', $helper->getTargetPath() ) );
                }

                // Delete the old image
                $item = Mage::getModel('pagemanager/item')->getItem($id);
                $oldFileNameBig = $item['filename_big'];
                $oldTargetPathBig = $helper->getAbsoluteTargetPath() . $oldFileNameBig;
                if(file_exists($oldTargetPathBig)){
                    unlink($oldTargetPathBig);
                }

                $params['filename_big'] = $fileNameBig;
                // End of file handling
            }

            $params['title'] = $post['editimagewithoverlayform']['title'];
            $params['type'] = $post['editimagewithoverlayform']['type'];
            $params['image_text'] = $post['editimagewithoverlayform']['image_text'];
            $params['image_alt'] = $post['editimagewithoverlayform']['image_alt'];
            $params['link'] = $post['editimagewithoverlayform']['link'];
            $params['link_target'] = $post['editimagewithoverlayform']['link_target'];
            $params['status'] = $post['editimagewithoverlayform']['status'];
            $params['position'] = $post['editimagewithoverlayform']['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_pagemanager_item', $params, $where );

            $message = $this->__('Your image has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/editimagewithoverlay/id/'. $id);
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
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
            $params['page_content'] = $post['page_content'];
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_pagemanager_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/edithtml/id/'. $id);
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function updatetoplistAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['edittoplistform']['id'];

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['edittoplistform']['row_id'];
            $params['type'] = $post['edittoplistform']['type'];
            $params['title'] = $post['edittoplistform']['title'];
            $params['status'] = $post['edittoplistform']['status'];
            //$params['visibility'] = $post['edittoplistform']['visibility'];
            $params['category_id'] = $post['edittoplistform']['category_id'];
            //$params['products_per_row'] = $post['edittoplistform']['products_per_row'];
            $params['total_products'] = $post['edittoplistform']['total_products'];
            $params['toplist'] = $post['edittoplistform']['toplist'];
            $params['sort_by'] = $post['edittoplistform']['sort_by'];
            $params['page_content'] = isset($post['edittoplistform']['product_ids']) ? $post['edittoplistform']['product_ids'] : ""; //reused for manual product ids
            $params['position'] = $post['edittoplistform']['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_pagemanager_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/edittoplist/id/'. $id);
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function updatecategoryAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['editcategoryform']['id'];

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['editcategoryform']['row_id'];
            $params['type'] = $post['editcategoryform']['type'];
            $params['title'] = $post['editcategoryform']['title'];
            $params['status'] = $post['editcategoryform']['status'];
            $params['category_id'] = $post['editcategoryform']['category_id'];
            $params['position'] = $post['editcategoryform']['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_pagemanager_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/editcategory/id/'. $id);
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function updateslideshowAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['editslideshowform']['id'];

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['editslideshowform']['row_id'];
            $params['type'] = $post['editslideshowform']['type'];
            $params['title'] = $post['editslideshowform']['title'];
            $params['status'] = $post['editslideshowform']['status'];
            //$params['visibility'] = $post['editslideshowform']['visibility'];
            $params['slideshow'] = $post['editslideshowform']['slideshow'];
            $params['position'] = $post['editslideshowform']['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_pagemanager_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/editslideshow/id/'. $id);
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function updateheadingAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = $post['editheadingform']['id'];

            $params = array();

            $params['page_id'] = $_SESSION['page_id'];
            $params['row_id'] = $post['editheadingform']['row_id'];
            $params['type'] = $post['editheadingform']['type'];
            $params['title'] = $post['editheadingform']['title'];
            $params['heading'] = $post['editheadingform']['heading'];
            $params['status'] = $post['editheadingform']['status'];
            //$params['visibility'] = $post['editheadingform']['visibility'];
            $params['position'] = $post['editheadingform']['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_pagemanager_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/editheading/id/'. $id);
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    public function updatewidgetAction()
    {
        $post = $this->getRequest()->getPost();

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $id = !empty($post['id']) ? $post['id'] : $post['row_id'];

            $params = array();

            $params['type'] = $post['type'];
            $params['title'] = $post['title'];
            $params['page_content'] = $this->_getWidgetDeclaration($post['widget_type'], isset($post['parameters']) ?  $post['parameters'] : array());
            $params['status'] = $post['status'];
            $params['position'] = $post['position'];

            // Insert
            $wr = Icommerce_Db::getDbWrite();
            $where = $wr->quoteInto('id=?', (int)$id);
            $r = $wr->update( 'icommerce_pagemanager_item', $params, $where );

            $message = $this->__('Your item has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

            $this->_redirect('*/pageitem/editwidget/id/'. $id);
            return;
        }

        $this->_redirect('*/pagemanager/edit/id/'. $_SESSION['page_id']);
    }

    private function _getWidgetDeclaration($type, $params = array())
    {
        $directive = '{{widget type="' . $type . '"';

        try {
            foreach ($params as $name => $value) {
                // Retrieve default option value if pre-configured
                if (is_array($value)) {
                    $value = implode(',', $value);
                } elseif (trim($value) == '') {
                    $widget = Mage::getModel("widget/widget")->getConfigAsObject($type);
                    $parameters = $widget->getParameters();
                    if (isset($parameters[$name]) && is_object($parameters[$name])) {
                        $value = $parameters[$name]->getValue();
                    }
                }
                if ($value || $value == '0') {
                    $directive .= sprintf(' %s="%s"', $name, str_replace('"', '&quot;', $value));
                }
            }

        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($this->__('Saved failed, please fill in all parameters.'));
        }

        $directive .= '}}';
        return $directive;

    }




}
