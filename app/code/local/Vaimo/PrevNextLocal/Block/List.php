<?php
/**
 * @category    Vaimo
 * @package     Vaimo_ProductWidget
 * @copyright   Copyright (c) 2012 Vaimo.com
 * @license     Property of Vaimo
 */


/**
 * Block class for the "List" widget.
 * @category    Vaimo
 * @package     Vaimo_PrevNextLocal
 * @author      Peter Lembke, Vaimo
 */
class Vaimo_PrevNextLocal_Block_List
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    private $_productId = 0;

    protected function _toHtml()
    {
        $this->setTemplate('vaimo/prevnextlocal/list.phtml');
        return parent::_toHtml();
    }

    public function getScript() {
        return Mage::helper('prevnextlocal/product')->getScript();
    }

    public function setProductId($productIid) {
        $this->_productId = (int)$productIid;
        return $this;
    }

    public function getEventScript() {
        $spread = $this->getData('spread');
        $scriptCode = '<script type="text/javascript">
            function vaimo_prevnextlocal_list() {
                var $that = this;
                this.eventHandler = function ($eventData) {
                    var $productsData = $eventData.detail,
                        $dataLength = $productsData.data_array.length,
                        $i, $id, $url, $middle, $detail = {}, $image = [], $tmpImage = [];

                    // console.log("Got the product data");
                    // console.dir($productsData);
                    if ($dataLength === 0) {
                        return false;
                    }

                    for ($i = 0; $i < $dataLength; $i++) {
                        $id = "vaimo_prevnextlocal_list_image_" + $i;
                        $image[$i] = document.getElementById($id);
                        $image[$i].setAttribute("product_id", $productsData.data_array[$i].id);
                        // $fullImageUrl = $productsData.base_data.base_image_url + "catalog/product" + $productsData.data_array[$i].image;
                        $cachedImageUrl = $productsData.base_data.base_image_url + "catalog/product/" + $productsData.base_data.cache_path + $productsData.data_array[$i].image;
                        $image[$i].src = $cachedImageUrl;
                        $tmpImage[$i] = new Image();
                        $tmpImage[$i].setAttribute("i", $i);
                        $tmpImage[$i].onerror = function () {
                            if (this.height < 70) {
                                var $i = this.getAttribute("i");
                                var $backupUrl = $productsData.base_data.base_url + "trigger/prevnextlocal/image.php?image=" + $productsData.data_array[$i].image;
                                $image[$i].src = $backupUrl;
                            }
                        }
                        $tmpImage[$i].src = $cachedImageUrl;
                    }
                    $middle = ($dataLength -1)/2;
                    $detail = {
                        "base_data":$productsData.base_data,
                        "product_data":$productsData.data_array[$middle]
                    };

                    _makeListVisible();
                    _triggerEvent($detail);
                };
                this.imageExist = function ($cachedImageUrl, $backupUrl, $image) {
                    var $tmpImage = new Image();
                    $tmpImage.onload = function () {
                       alert("image is loaded");
                    }
                    $image.src = $cachedImageUrl;
                };
                this.go = function ($nr, $isMiddle) {
                    // window.alert($nr + " " + $isMiddle);
                    var $productId = document.getElementById("vaimo_prevnextlocal_list_image_"+$nr).getAttribute("product_id");
                    if ($isMiddle === false) {
                        $prevNextLocal.getProductData({"product_id":$productId, "spread":' . $spread . ' });
                    }
                    if ($isMiddle === true) {
                        var $productData = $prevNextLocal.fetchProductData({"product_id":$productId }),
                            $url = $productData.base_data.base_url + $productData.url_key;
                        window.location.replace($url);
                    }
                };
                var _makeListVisible = function() {
                    document.getElementById("vaimo_prevnextlocal_list").style.display = "block";
                };
                var _triggerEvent = function ($detail) {
                    "use strict";
                    var $event = new CustomEvent("vaimoPrevNextLocalListProductData", {detail: $detail, bubbles: true, cancelable: true });
                    document.dispatchEvent($event);
                    return {"answer":"true", "message":"Data is available. Triggered the event:vaimoPrevNextLocalListProductData" };
                };
            }
            $prevNextLocalList = new vaimo_prevnextlocal_list();
            document.addEventListener("vaimoPrevNextLocalHaveData", $prevNextLocalList.eventHandler, false);
            jQuery( document ).ready(function() {
                var $productId = '.$this->_productId.';
                if ($productId > 0) {
                    localStorage.setItem("vaimo_prevnextlocal_product_id", $productId);
                } else {
                    $productId = localStorage.getItem("vaimo_prevnextlocal_product_id");
                }
                $prevNextLocal.getProductData({"product_id":$productId, "spread":' . $spread . ' });
            });
        </script>';
        return $scriptCode;
    }

}
