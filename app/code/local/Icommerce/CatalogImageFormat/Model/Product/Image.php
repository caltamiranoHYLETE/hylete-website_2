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

class Icommerce_CatalogImageFormat_Model_Product_Image extends Mage_Catalog_Model_Product_Image
{
    /**
     * @return Varien_Image
     */
    public function getImageProcessor()
    {
        if( !$this->_processor ) {
            $this->_processor = new Icommerce_CatalogImageFormat_Model_Image_Processor($this->getBaseFile());
            $this->_processor->outputType($this->getOutputType()); //WILKO
        }
        return parent::getImageProcessor();
    }

    /**
     * Set filenames for base file and new file
     *
     * @param string $file
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setBaseFile($file)
    {
        parent::setBaseFile($file);

        if ($this->getOutputType()) { //WILKO
            $ext = null;
            switch ($this->getOutputType()) {
                case IMAGETYPE_JPEG:
                    $ext = '.jpg';
                    break;
                case IMAGETYPE_PNG:
                    $ext = '.png';
                    break;
            }
            if ($ext) {
                $this->_newFile = substr($this->_newFile,0,strrpos($this->_newFile,'.'));
                $this->_newFile .= $ext;
            }
        }
    }
}