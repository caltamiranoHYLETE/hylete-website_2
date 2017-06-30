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

class Icommerce_CatalogImageFormat_Model_Image_Processor extends Varien_Image
{
    protected $initial_adapter = null;

    /**
     * Constructor
     *
     * @param Varien_Image_Adapter $adapter. Default value is GD2
     * @param string $fileName
     * @return void
     */
    public function __construct($fileName = null, $adapter = Varien_Image_Adapter::ADAPTER_GD2)
    {
        // lookup adapter selection and assign correct to it
        $image_processor = Mage::getStoreConfig('catalogimageformat/settings/image_processor');
        if (!empty($image_processor)) {
            $adapter = strtoupper($image_processor);
        }

        $this->initial_adapter = $adapter;
        parent::__construct($fileName, $adapter);
    }

    /**
     * Retrieve image adapter object
     *
     * @param string $adapter
     * @return Varien_Image_Adapter_Abstract
     */
    protected function _getAdapter($adapter = null)
    {
        if (!isset($this->_adapter)) {
            if (empty($this->initial_adapter)) {
                $adapter = $this->initial_adapter;
            }

            switch ($adapter) {
                case Varien_Image_Adapter::ADAPTER_IM:
                    $this->_adapter = new Varien_Image_Adapter_Imagemagic();
                    break;
                default:
                    $this->_adapter = new Icommerce_CatalogImageFormat_Model_Image_Adapter_Gd2();
            }
        }
        return $this->_adapter;
    }

    public function outputType($type)
    {
        return $this->_getAdapter()->setOutputType($type);
    }
}
