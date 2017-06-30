<?php

class Vaimo_PrevNextLocal_DataController extends Mage_Core_Controller_Front_Action
{

    /**
     * Return product data for one or many products
     */
    public function  getAction() {
        $in = $this->getRequest()->getParams();
        $in = array_merge(array('id'=>0,'ids'=>''),$in);

        if ($in['ids'] === '') {
            echo json_encode(array());
        }
        $in['ids'] = explode('-',$in['ids']);

        $attributes = $this->getYourSelectedProductAttributeNamesFromAdmin();
        $collection = $this->getCollection($in['ids'], $attributes);

        $oldWhen = time() + 24 * 60 * 60; // Seconds since epoc + 1 day

        $scene7Helper = '';
        $useScene7 = (int)Mage::getStoreConfig('prevnextlocal/scene7/enable');
        if ($useScene7) {
            $scene7HelperName = Mage::getStoreConfig('prevnextlocal/scene7/helper');
            $scene7Helper = Mage::helper($scene7HelperName);
        }

        $response = array();
        $response['product_data'] = array();
        foreach ($collection as $id => $product) {
            $productData = array();
            foreach ($attributes as $attribute) {
                $productData[$attribute] = $product->getData($attribute);

                if ($attribute == 'image') {
                    if ($useScene7) {
                        $productData[$attribute] = $scene7Helper->getProductImageUrl($product);
                    } else {
                        // $productData[$attribute] = $product->getMediaConfig()->getMediaUrl($product->getData('thumbnail'));
                        $productData[$attribute] = $product->getData('thumbnail');
                    }
                }

                if ($attribute == 'color') {
                    $productData[$attribute] = $this->getColors($product);
                }

            }
            $productData['id'] = $id;
            $productData['old_when'] = $oldWhen;
            $response['product_data'][$id] = $productData;
        }
        $response['product_id'] = $in['id'];
        echo json_encode($response);
    }

    private function getYourSelectedProductAttributeNamesFromAdmin() {
        $response = array();
        $config = $this->getConfig();
        foreach ($config as $name => $data) {
            if ($data == 1) {
                $response[] = $name;
            }
        }
        $response[] = 'url_key';
        $response[] = 'sku';
        $response[] = 'type_id';
        $response[] = 'status';
        $response[] = 'is_salable';
        return $response;
    }

    private function getCollection($productIds = array(), $attributes = array() ) {
        $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $productIds))
            ->addAttributeToSelect($attributes)
            ->load();
        return $collection;
    }

    private function getConfig() {
        $enable = (int)Mage::getStoreConfig('prevnextlocal/general/enable');
        if ($enable === 0) {
            return array();
        }
        $configValues = Mage::getStoreConfig('prevnextlocal/include');
        return $configValues;
    }

    private function getColors($product) {
        // Special note: Polarn & Pyret, Brothers, they have color on the configurable product.
        $response = array();
        $childIdsArray = Mage::getResourceSingleton('catalog/product_type_configurable')->getChildrenIds($product->getId());
        $childIds = array();
        foreach ($childIdsArray[0] as $id => $data) {
            $childIds[] = $id;
        }
        $childProducts = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $childIds))
            ->addAttributeToSelect('color');
            // ->groupByAttribute('color');

        foreach ($childProducts as $childProduct) {
            $colorName = $childProduct->getAttributeText('color');
            $response[] = $colorName;
        }
        return $response;
    }

}