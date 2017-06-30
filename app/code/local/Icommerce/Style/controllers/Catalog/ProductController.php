<?php
include_once("Mage/Adminhtml/controllers/Catalog/ProductController.php");

class Icommerce_Style_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{

    public function _initProductSave() {
        $params = $this->getRequest()->getParams();

        if(isset($params['ss']) && isset($params['id'])) {
            $increment = 1;
            $relatedProds = array();

            $productId = Icommerce_Db::getDbSingleton('SELECT product_id FROM catalog_product_link WHERE linked_product_id = ? and link_type_id = 1', $params['id']);
            if(!empty($productId)) { //Remove exsisting assocation
                $styleProduct = Mage::getModel('catalog/product')->load($productId);
                $relatedConfProducts = $styleProduct->getRelatedProductCollection();
                //Remove from exsisting products
                foreach($relatedConfProducts as $prod) {
                    if($prod->getId() != $params['id']) {
                        $relatedProds[$prod->getId()] = array('position' => $increment);
                        $increment++;
                    }
                }
                $styleProduct->setRelatedLinkData($relatedProds);
                $styleProduct->save();

                $increment = 1;
                $relatedProds = array();
            }

            $styleProduct = Mage::getModel('catalog/product')->load($params['ss']);

            if($styleProduct instanceof Mage_Catalog_Model_Product && $styleProduct->getTypeId() == 'style') {

                $relatedConfProducts = $styleProduct->getRelatedProductCollection();
                //Remove from exsisting products
                foreach($relatedConfProducts as $prod) {
                    $relatedProds[$prod->getId()] = array('position' => $increment);
                    $increment++;
                }
                $relatedProds[$params['id']] = array('position' => $increment);

                $styleProduct->setRelatedLinkData($relatedProds);
                $styleProduct->save();
            }

        }

        return parent::_initProductSave();
    }

}
?>