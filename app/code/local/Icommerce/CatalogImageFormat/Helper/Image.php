<?php
/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
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
 * @category    Icommerce
 * @package     Icommerce_CatalogImageFormat
 * @copyright   Copyright (c) 2009-2012 Icommerce Nordic AB
 * @author      Wilko Nienhaus
 */

class Icommerce_CatalogImageFormat_Helper_Image extends Mage_Catalog_Helper_Image
{
    /**
     * Used to indicate if product thumbnail attribute is accessible or not
     *
     * @var bool
     */
    protected $_useSmallImagesInsteadOfThumbnails = 0;

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param $attributeName
     * @param null $imageFile
     */
    public function init(Mage_Catalog_Model_Product $product, $attributeName, $imageFile=null)
    {
        // Switches calls for 'thumbnail' to 'small_image' if this setting is enabled
        $this->_useSmallImagesInsteadOfThumbnails = Mage::getStoreConfig('catalogimageformat/settings/use_small_images_instead_of_thumbnails');

        if ($attributeName == 'thumbnail' && $this->_useSmallImagesInsteadOfThumbnails) {
            $attributeName = 'small_image';
        }

        $this->_reset();
        $this->_setModel(Mage::getModel('catalogimageformat/product_image'));
        $this->_getModel()->setDestinationSubdir($attributeName);
        $this->setProduct($product);

        $this->setWatermark(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_image"));
        $this->setWatermarkImageOpacity(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_imageOpacity"));
        $this->setWatermarkPosition(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_position"));
        $this->setWatermarkSize(Mage::getStoreConfig("design/watermark/{$this->_getModel()->getDestinationSubdir()}_size"));

        if ($imageFile) {
            $this->setImageFile($imageFile);
        }
        else {
            // add for work original size
            $this->_getModel()->setBaseFile( $this->getProduct()->getData($this->_getModel()->getDestinationSubdir()) );
        }
        if ($format = (int)Mage::getStoreConfig('catalogimageformat/settings/format')) {
            $this->setOutputType($format);
        }
        return $this;
    }

    /**
     * @param IMAGETYPE $type
     */
    public function setOutputType($type)
    {
        if ($this->_model) {
            $this->_getModel()->setOutputType($type);
        }
    }

    /**
     * Lazyloader for dummy product
     */
    protected function _getProduct($entityId)
    {
        if (!$this->_product) {
            $this->_product = Mage::getModel('catalog/product');
        }

        return $this->_product->reset()->setEntityId($entityId);
    }

    /**
     * Set whether to use small images instead of thumbnails
     *
     * @param $value
     * @return $this
     */
    public function setAllowThumbnail($value)
    {
        if($value) {
            $this->_useSmallImagesInsteadOfThumbnails = 0;
        } else {
            $this->_useSmallImagesInsteadOfThumbnails = 1;
        }

        return $this;
    }

}