<?php
/**
 * Copyright © 2009-2011 Icommerce Nordic AB
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
 * @package     Icommerce_Enhancedgrid
 * @copyright   Copyright © 2009-2012 Icommerce Nordic AB
 */
class Icommerce_Enhancedgrid_Block_Widget_Grid_Column_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected static $showImagesUrl = null;
    protected static $showByDefault = null;
    protected static $width = null;
    protected static $height = null;
    
    public function __construct() {
        if(self::$showImagesUrl == null)
            self::$showImagesUrl = (int)Mage::getStoreConfig('enhancedgrid/images/showurl') === 1;
        if(self::$showByDefault == null)
            self::$showByDefault = (int)Mage::getStoreConfig('enhancedgrid/images/showbydefault') === 1;
        if(self::$width == null)
            self::$width = Mage::getStoreConfig('enhancedgrid/images/width');
        if(self::$height == null)
            self::$height = Mage::getStoreConfig('enhancedgrid/images/height');
    }

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        
        return $this->_getValue($row);
    }
    
    /*
    public function renderProperty(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        $val = Mage::helper('imagebyurl')->getImageUrl($val);
        $out = parent::renderProperty(). ' onclick="showImage('.$val.')" ';
        return $out;
    }

        */
    protected function _getValue(Varien_Object $row)
    {
        
        $dored = false;
        if ($getter = $this->getColumn()->getGetter()) {
            $val = $row->$getter();
        }
        $val = $val2 = $row->getData($this->getColumn()->getIndex());
        $val = str_replace("no_selection", "", $val);
        $val2 = str_replace("no_selection", "", $val2);
        $url = Mage::helper('enhancedgrid')->getImageUrl($val);

        $file_exists = true;
        if(!Mage::helper('enhancedgrid')->getFileExists($val)) {
          $file_exists = false;
          $dored =true;
          $val .= "[!]";
        }
        if(strpos($val, "placeholder/")) {
          $dored = true;
        }
        
        $filename = substr($val2, strrpos($val2, "/")+1, strlen($val2)-strrpos($val2, "/")-1);
        if(!self::$showImagesUrl) $filename = '';
        if($dored) {
          $val = "<span style=\"color:red\" id=\"img\">$filename</span>";
        } else {
          $val = "<span>". $filename ."</span>";
        }
        
        if(empty($val2) ) {
            $out = "<center>" . $this->__("(no image)") . "</center>";
        } else {
            $out = $val. '<center><a href="#" onclick="window.open(\''. $url .'\', \''. $val2 .'\')"'.
            'title="'. $val2 .'" '. ' url="'.$url.'" id="imageurl">';
        }
        
        if( $file_exists && self::$showByDefault && !empty($val2) ) {
            $storeId = (int) $this->getRequest()->getParam('store', 0);
            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($row->getEntityId());
            $smallImage = Mage::helper('catalog/image')->init($product, $this->getColumn()->getIndex())->resize(self::$width);
            $out .= "<img src=". $smallImage ." width='". self::$width ."' ";
            if(self::$height > self::$width) {
                $out .= "height='". self::$height ."' ";
            }
            $out .=" />";
        }
        //die( $this->helper('catalog/image')->init($_product, 'small_image')->resize(135, 135));
        $out .= '</a></center>';
        
        return $out;
    }


}
