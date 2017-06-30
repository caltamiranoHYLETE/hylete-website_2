<?php
/**
 * @category    Vaimo
 * @package     Vaimo_ProductWidget
 * @copyright   Copyright (c) 2012 Vaimo.com
 * @license     Property of Vaimo
 */


/**
 * Block class for the "Detail" widget.
 * @category    Vaimo
 * @package     Vaimo_PrevNextLocal
 * @author      Peter Lembke, Vaimo
 */
class Vaimo_PrevNextLocal_Block_Detail
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{

    protected function _toHtml()
    {
        $this->setTemplate('vaimo/prevnextlocal/detail.phtml');
        return parent::_toHtml();
    }

    public function getEventScript() {
        $scriptCode = '<script type="text/javascript">
            function vaimoPrevNextLocalDetail($eventData) {
                var $productData = $eventData.detail,
                    $key = "vaimo_prevnextlocal_detail_",
                    $imageUrl, $cachedImageUrl, $image, $tmpImage;
                console.log("Got the product detail data");
                console.dir($productData);

                $cachedImageUrl = $productData.base_data.base_image_url + "catalog/product/" + $productData.base_data.cache_path + $productData.product_data.image;
                // $imageUrl = $productData.base_data.base_image_url + "catalog/product" + $productData.product_data.image;
                $image = document.getElementById($key + "image");
                $image.src = $cachedImageUrl;
                $tmpImage = new Image();
                $tmpImage.onerror = function () {
                    if (this.height < 70) {
                        var $backupUrl = $productData.base_data.base_url + "trigger/prevnextlocal/image.php?image=" + $productData.product_data.image;
                        $image.src = $backupUrl;
                    }
                }
                $tmpImage.src = $cachedImageUrl;

                document.getElementById($key + "name").innerHTML = $productData.product_data.name;
                document.getElementById($key + "short_description").innerHTML = $productData.product_data.short_description;
                document.getElementById($key + "sku").innerHTML = $productData.product_data.sku;
                document.getElementById("vaimo_prevnextlocal_detail").style.display = "block";
            }
            document.addEventListener("vaimoPrevNextLocalListProductData", vaimoPrevNextLocalDetail, false);
        </script>';
        return $scriptCode;
    }

}
