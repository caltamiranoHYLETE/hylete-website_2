<?php
class Icommerce_Style_Model_Observer
{
    protected $_isSaleableCheck = false;
    public function catalogProductLoadAfter(Varien_Event_Observer $observer)
    {
        return $this;
        $product = $observer->getEvent()->getProduct();
        if($product->getData('store_id') != 0) { //store_id = admin
            if($product Instanceof Mage_Catalog_Model_Product && $product->getTypeId() == 'styleproduct') {
                $helper = Mage::helper("jsonproductinfo");

                $this->_linkCollection = $product->getRelatedProductCollection();
                $result = array();
                foreach($this->_linkCollection as $prod) {
                    if($prod->getIsSalable() == 1 || $this->_isSaleableCheck == false) {
                        //$prod->load();
                        //$_prod = Mage::getModel('catalog/product')->load($prod->getId()); // Bad solution, needs a workaround
                        $result[] = array('name' => $_prod->getName(),
                                          'images' => $_prod->getName(),
                                          'simple' => $helper->getSimpleValues($prod)
                                        );
                    }
                }
                $product->setStyleProducts($result);
            }
        }
    }

    public function update_defaultstyle($observer)
    {
        /*
         * This condition was added because there is no time to rework this. It is also
         * hard to know what is needed and what not in case attribute default_style_id
         * doesn't exist.
         * update_defaultstyle was removed and restored again, because default_style_id
         * attribute may be created manually for some other projects.
         * If default_style_id doesn't exist there is no reason to use current bugged logic.
         * Please, note the code below can cause performance issues and deadlocks.
         * This is very old part of code and it should be reworked without using load/save.
         * Update of attributes should be processed by one query.
         * If there are no projects where update_defaultstyle attribute is still used, then
         * related logic should be removed.
         * */
        if (!Mage::helper('style')->canUseDefaultStyleIdAttribute()) {
            return $this;
        }

        $_product = $observer->getEvent()->getProduct();

        if($_product->getTypeId() == 'style'){
        $related = $_product->getRelatedProductCollection();
            foreach($related  as $p){
                $_prod = Mage::getModel('catalog/product')->load($p->getId());
                $_prod->setDefaultStyleId($_product->getId());
                $_prod->save();
            }
        }
        else
            return;
    }

    public function save_product_arguments(Varien_Event_Observer $observer)
    {
        /*
         * This condition was added because there is no time to rework this.
         * save_product_arguments was removed and restored again, because default_style_id
         * attribute may be created manually for some other projects.
         * If default_style_id doesn't exist there is no reason to use current bugged logic.
         * If there are no projects where update_defaultstyle attribute is still used, then
         * related logic should be removed.
         * */
        if (!Mage::helper('style')->canUseDefaultStyleIdAttribute()) {
            return $this;
        }

        $defaultStyleId = $observer->getRequest()->getParam('defaultbox');
        $_product = $observer->getEvent()->getProduct();
        $_product->setDefaultStyleId($defaultStyleId);
    }
}
