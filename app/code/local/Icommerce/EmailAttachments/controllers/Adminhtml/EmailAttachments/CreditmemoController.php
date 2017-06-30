<?php

class Icommerce_EmailAttachments_Adminhtml_EmailAttachments_CreditmemoController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/creditmemo');
    }
}