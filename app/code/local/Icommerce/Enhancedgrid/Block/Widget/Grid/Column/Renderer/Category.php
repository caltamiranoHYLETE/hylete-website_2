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
class Icommerce_Enhancedgrid_Block_Widget_Grid_Column_Renderer_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected static $cat_map = null;
    
    public function __construct() {
        return parent::_construct();
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
        
        if ($getter = $this->getColumn()->getGetter()) {
            $val = $row->$getter();
        }
        if(self::$cat_map == null) {
            $cat_col = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('name');
            $cat_map = array();
            foreach($cat_col as &$c) {
                $cat_map[$c->getId()] = $c->getName();
            }
            self::$cat_map = $cat_map;
        }
        $category_ids_str = $row->getData('category_ids');
        $category_ids = explode(",", $category_ids_str);
        
        $cat_names = array();
        foreach(self::$cat_map as $id=>$name) {
            if(array_search($id, $category_ids) !== false) {
                $cat_names[] = $name;
            }
        }
        $cat_names_str = implode(",", $cat_names);
        
        return $cat_names_str;
    }


}
