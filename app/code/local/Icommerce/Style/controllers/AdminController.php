<?php

class Icommerce_Style_AdminController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed()
    {
        return true;
    }

    public function indexAction() {
    }
    public function getProduct() {
        if (!$this->_product) {
            $this->_product = Mage::registry('product');
        }
        return $this->_product;
    }
    /**
     *
     */
    public function saveRelatedAction() {
        $params = $this->getRequest()->getParams();
        if(isset($params['ss']) && isset($params['id'])) {

        }

    }
    public function related2Action()
    {
        $req = $this->getRequest()->getParams();
        $productId  = (int) $this->getRequest()->getParam('id');
        $product    = Mage::getModel('catalog/product')->setStoreId($this->getRequest()->getParam('store', 0));

        if (!$productId) {
            if ($setId = (int) $this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
        }


        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('style/adminhtml_style_grid')
                ->toHtml()
        );
    }
}
