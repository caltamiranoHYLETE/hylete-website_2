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
class Icommerce_Enhancedgrid_Block_Catalog_Product extends Mage_Adminhtml_Block_Catalog_Product
{
    
    public function __construct()
    {
        parent::__construct();
        $this->_headerText = Mage::helper('enhancedgrid')->__('Manage Products (Enhanced)');
        
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('icommerce/enhancedgrid/catalog/product.phtml');
        $this->setChild('grid', $this->getLayout()->createBlock('enhancedgrid/catalog_product_grid', 'product.enhancedgrid'));
        
        $store_switcher =  $this->getLayout()->createBlock('adminhtml/store_switcher', 'store_switcher');
        $store_switcher->setUseConfirm(false);
        $this->setChild('store_switcher', $store_switcher);
        
        $this->setChild('add_new_button',
        $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('catalog')->__('Add Product'),
                'onclick'   => "setLocation('".$this->getUrl('adminhtml/catalog_product/new')."')",
                'class'   => 'add'
                ))
        );
    }
}

