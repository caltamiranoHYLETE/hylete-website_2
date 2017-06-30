<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
*
* Vaimo reserves all rights in the Program as delivered. The Program
* or any portion thereof may not be reproduced in any form whatsoever without
* the written consent of Vaimo, except as provided by licence. A licence
* under Vaimo's rights in the Program may be available directly from
* Vaimo.
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
* @category    Vaimo
* @package     Vaimo_Carbon
* @copyright   Copyright (c) 2009-2015 Vaimo AB
*/

class Vaimo_Carbon_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{  
    const DEFAULT_TEMPLATE = 'catalog/product/list/grid-item.phtml';
    
    protected $_productBlock = null;
    protected $_templates = array(
            'default_grid' => 'catalog/product/list/grid-item.phtml',
            'default_list' => 'catalog/product/list/list-item.phtml'
    );
    
    public function setItemTemplate($template, $type = null, $mode = null)
    {
        if (!$type) {
            $type = 'default';
        }
        
        if (!$mode) {
            $this->_templates[$type . '_grid'] = $template;
            $this->_templates[$type . '_list'] = $template;
        } else {
            $this->_templates[$type . '_' . $mode] = $template;
        }
    }
    
    public function getItemTemplate($type, $mode)
    {
        if (isset($this->_templates[$type . '_' . $mode])) {
            return $this->_templates[$type . '_' . $mode];
        }
        
        if (isset($this->_templates['default_' . $mode])) {
            return $this->_templates['default_' . $mode];
        }
        
        return self::DEFAULT_TEMPLATE;
    }
    
    protected function getProductBlock()
    {
        if (!$this->_productBlock) {
            $this->_productBlock = $this->getLayout()->createBlock('carbon/catalog_product_list_item');
        }
        return $this->_productBlock;
    }
    
    protected function getItemHtml($product, $template)
    {
        $itemBlock = $this->getProductBlock();
        $itemBlock->setProduct($product);
        $itemBlock->setTemplate($template);
        return $itemBlock->toHtml();
    }
    
    protected function getListItemHtml(Mage_Catalog_Model_Product $product)
    {
        $productType = $product->getTypeId();
        return $this->getItemHtml($product, $this->getItemTemplate($productType, 'list'));
    }
    
    protected function getGridItemHtml(Mage_Catalog_Model_Product $product)
    {
        $productType = $product->getTypeId();
        return $this->getItemHtml($product, $this->getItemTemplate($productType, 'grid'));
    }    
}