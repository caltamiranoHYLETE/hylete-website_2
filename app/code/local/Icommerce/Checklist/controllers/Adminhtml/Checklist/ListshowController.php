<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Checklist
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Icommerce_Checklist_Adminhtml_Checklist_ListshowController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system/checklist');
        $this->renderLayout();
    }

    public function addAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system/checklist');
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $params = $this->getRequest()->getParams();

        try
        {

            $id = (int)$params['id'];

            if(!preg_match("/^[0-9]+$/", $id ))
            {
                throw new Exception('Project id is not valid.');
                return;
            }

            $helper = Mage::helper('checklist');


            // Delete from database
            $wr = Icommerce_Db::getDbWrite();
            $sql = "DELETE FROM icommerce_checklist WHERE id = $id;";
            $sql .= "DELETE FROM icommerce_checklist_item WHERE project_id = $id;";
            $sql .= "DELETE FROM icommerce_checklist_item_checkbox WHERE project_id = $id;";

            $r = $wr->query( $sql );

            $message = $this->__('Your project has been deleted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('checklist/adminhtml_listshow/index/');
    }

    public function editAction()
    {
        $params = $this->getRequest()->getParams();

        $id = (int)$params['id'];

        if(!preg_match("/^[0-9]+$/", $id ))
        {
            throw new Exception('Project id is not valid.');
            return;
        }

        $listshow = Mage::getModel('checklist/listshow')->getProject($id);

        $this->loadLayout();
        $editBlock = $this->getLayout()->getBlock("edit");
        $editBlock->setData('listshow', $listshow);

        $this->_setActiveMenu('system/checklist');
        $this->renderLayout();
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();

        try
        {
            if (empty($post))
                Mage::throwException($this->__('Invalid form data.'));

            $params = array();
            $params['name'] = $post['addform']['name'];
            $params['pm_email'] = $post['addform']['pm_email'];
            $params['other_url'] = $post['addform']['other_url'];
            $params['customer_email'] = $post['addform']['customer_email'];
            $params['status'] = $post['addform']['status'];
            $params['position'] = $post['addform']['position'];
            $params['created_on'] = date('Y-m-d h:m:s', Mage::getModel('core/date')->timestamp(time()));

            $session = Mage::getSingleton('admin/session');
            $userId = $session->getUser()->getUserId();

            $params['created_by'] = $userId;

            // Insert
            $wr = Icommerce_Db::getDbWrite();

            $vals = implode( ",", Icommerce_Db::wrapQueryValues($params) );

            $sql = "INSERT INTO icommerce_checklist (" .
               implode(", ",array_keys($params)) .
               ") VALUES ( $vals );";

            $r = $wr->query( $sql );

            $message = $this->__('Your project has been saved successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }


    public function updateAction()
    {
        $post = $this->getRequest()->getPost();

        try
        {
            if (empty($post))
                Mage::throwException($this->__('Invalid form data.'));

            $params = array();
            $params['name'] = "'".$post['addform']['name']."'";
            $params['pm_email'] = "'".$post['addform']['pm_email']."'";
            $params['other_url'] = "'".$post['addform']['other_url']."'";
            $params['customer_email'] = "'".$post['addform']['customer_email']."'";
            $params['status'] = "'".$post['addform']['status']."'";
            $params['position'] = $post['addform']['position'];

            $id = (int)$post['addform']['project_id'];

            if(!preg_match("/^[0-9]+$/", $id ))
            {
                throw new Exception('Project id is not valid.');
                return;
            }

            // Insert
            $wr = Icommerce_Db::getDbWrite();

            $vals = implode( ",", Icommerce_Db::wrapQueryValues($params) );

            $sql = "UPDATE icommerce_checklist SET ";
            $i = 1;
            $length = count($params);
            foreach($params as $key => $value)
            {
                if($i < $length)
                    $sql .= "$key = $value, ";
                else
                    $sql .= "$key = $value ";

                $i++;
            }
            $sql .= "WHERE id = $id";

            $r = $wr->query( $sql );

            $message = $this->__('Your project has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }

    public function massUpdateAction()
    {
        $post = $this->getRequest()->getPost();

        try
        {
            if (empty($post))
                Mage::throwException($this->__('Invalid form data.'));

            $action = $post['edit_form']['mass_update_action'];

            if($action == 'delete')
            {

                $ids = $post['edit_form']['mass_update_id'];

                if(is_array($ids))
                    $ids = implode(',', $ids);

                // Delete
                $wr = Icommerce_Db::getDbWrite();
                $sql = "DELETE FROM icommerce_checklist WHERE id IN ($ids)";

                $r = $wr->query( $sql );

            }
            else if($action == 'enable')
            {

                $ids = $post['edit_form']['mass_update_id'];

                if(is_array($ids))
                    $ids = implode(',', $ids);

                $wr = Icommerce_Db::getDbWrite();

                $sql = "UPDATE icommerce_checklist SET status = 1 WHERE id IN ($ids)";

                $r = $wr->query( $sql );
            }
            else if($action == 'disable')
            {

                $ids = $post['edit_form']['mass_update_id'];

                if(is_array($ids))
                    $ids = implode(',', $ids);

                $wr = Icommerce_Db::getDbWrite();

                $sql = "UPDATE icommerce_checklist SET status = 0 WHERE id IN ($ids)";

                $r = $wr->query( $sql );
            }
            else if($action == 'update')
            {

                $position = $post['edit_form']['position'];

                $wr = Icommerce_Db::getDbWrite();

                $sql = "";

                foreach($position as $key => $value)
                    $sql .= "UPDATE icommerce_checklist SET position = $value WHERE id = $key;";

                $r = $wr->query( $sql );
            }

            $message = $this->__('Your project has been updated successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);

        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }
}
