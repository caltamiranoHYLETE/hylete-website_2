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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_CustomCss
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
class Vaimo_CustomCss_Adminhtml_CustomcssController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/customcss');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system');
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Custom CSS Management'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id', null);

        $labelState = $id ? Mage::helper('customcss')->__('Edit CSS') : Mage::helper('customcss')->__('New CSS');
        $this->_title($this->__('Custom CSS Management'))->_title($labelState);

        $model = Mage::getModel('customcss/customcss');
        if ($id) {
            $model->load((int) $id);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customcss')->__('CSS does not exist'));
                $this->_redirect('*/*/');
            }
        }

        Mage::register('customcss_data', $model);

        $this->_initAction()
            ->_addBreadcrumb($labelState, $labelState)
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost())
        {
            $model = Mage::getModel('customcss/customcss');
            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            Mage::getSingleton('adminhtml/session')->setFormData($data);

            try {
                if ($id) {
                    $model->setId($id);
                }

                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('customcss')->__('Error saving CSS file'));
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customcss')->__('CSS File was successfully saved.'));

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customcss')->__('No data found to save'));

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('customcss/customcss');
                $model->load($id);
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customcss')->__('The css file has been deleted.'));

                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('customcss_id' => $id));
                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customcss')->__('Unable to find a css file to delete.'));

        $this->_redirect('*/*/');
    }
}