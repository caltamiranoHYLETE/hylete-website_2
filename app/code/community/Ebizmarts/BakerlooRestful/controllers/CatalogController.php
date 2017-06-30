<?php

class Ebizmarts_BakerlooRestful_CatalogController extends Mage_Core_Controller_Front_Action {

    public function imageAction() {

        //Mage_Core_Helper_Data
        $ch = Mage::helper('core/url');

        $imageFile  = $ch->urlDecode($this->getRequest()->getParam('f'));
        $productId  = (int)$ch->urlDecode($this->getRequest()->getParam('p'));
        $categoryId = (int)$ch->urlDecode($this->getRequest()->getParam('c'));
        $width      = (int)$this->getRequest()->getParam('w');
        $height     = (int)$this->getRequest()->getParam('h');

        if($productId or $categoryId) {

            if($productId) {
                $product = Mage::getModel('catalog/product')->load($productId);

                $thumb = Mage::helper('catalog/image')->init($product, 'image', $imageFile)
                                              ->resize($width, $height);

                $this->_redirectUrl((string)$thumb);
                return;
            }
            else {

                $baseDir = Mage::getBaseDir('media');
                $baseUrl = Mage::getBaseUrl('media') . 'catalog/category/';

                $basePath = $baseDir . DS . 'catalog' . DS . 'category' . DS;
                $file     = $basePath . $imageFile;

                $cachePath = $basePath;
                $cacheDir  = 'cache' . DS;
                $cacheFile = $cacheDir . $width . 'x' . $height . '_' . $imageFile;

                $cacheFileObj = new SplFileInfo($cachePath . $cacheFile);

                $cachedFileUrl = $baseUrl . str_replace(DS, '/', $cacheFile);

                if( !$cacheFileObj->isFile() ) {
                    $image = Mage::helper('bakerloo_restful/image')->setImageFile($file);

                    $resizeOrCrop = (string) Mage::helper('bakerloo_restful')->config('catalog/category_thumbnails');

                    if($resizeOrCrop == 'resize')
                        $image->resample($width, $height, false);
                    else
                        $image->cropCentered($width, $height);

                    $saveResult = $image->save($cachePath . $cacheFile);

                    if($saveResult) {
                        $this->_redirectUrl($cachedFileUrl);
                        return;
                    }

                }
                else {
                    $this->_redirectUrl($cachedFileUrl);
                    return;
                }

                $this->_redirectUrl($baseUrl . $imageFile);
                return;

            }

        }

        $this->_forward('noRoute');

    }

}