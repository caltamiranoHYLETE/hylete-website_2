<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_Blog
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Blog_Block_Adminhtml_Product_Edit extends Mage_Adminhtml_Block_Catalog_Product_Edit {
    public function __construct() {
        parent::__construct();
    }
    public function _prepareLayout() {
        parent::_prepareLayout();
        /**
         * Check to see if current product is actually a blog post,
         * and change the link for "back" button if it is
         */
        $_product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
        $productType = Mage::getStoreConfig("blog/settings/product_type");
        $productTypeId = $_product->getTypeId();
        if($productTypeId == $productType || $this->getRequest()->getParam('type')==$productType) {
            $this->setChild('back_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('catalog')->__('Back'),
                        'onclick'   => 'setLocation(\''.Mage::helper("adminhtml")->getUrl("adminhtml/blog_blog").'\')',
                        'class' => 'back'
                    ))
            );
        }
    }
}