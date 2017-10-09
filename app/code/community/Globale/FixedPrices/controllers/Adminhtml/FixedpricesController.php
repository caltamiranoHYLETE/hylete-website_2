<?php

class Globale_FixedPrices_Adminhtml_FixedpricesController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/globale_fixedprices');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog')
            ->_title( $this->__('Catalog') )
            ->_title( $this->__('Mass Update Global-e Fixed Prices') )
            ->_addContent( $this->getLayout()->createBlock('globale_fixedprices/adminhtml_upload_edit')
                ->setData('action', $this->getUrl('*/fixedprices/save') )
            );

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function saveAction()
    {
        /** @var  $Update Globale_FixedPrices_Model_Update */
        $Update = Mage::getModel('globale_fixedprices/update');
        $FormData = $this->getRequest()->getPost();
        $Delete = (!empty($FormData['delete']))? $FormData['delete'] : 0;
        $Update->saveFile($Delete);
        $this->_redirectReferer();
    }

}