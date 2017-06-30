<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 */

class Vaimo_Blog_Adminhtml_Blog_BlogController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('blog/blog_posts');
    }

    protected function _initAction()
    {
        $this
                ->loadLayout()
                ->_addBreadcrumb(Mage::helper('blog')->__('Blog Manager'), Mage::helper('blog')->__('Blog Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('blog/blog_posts');
        $this->_addContent($this->getLayout()->createBlock('blog/adminhtml_blog'));
        $this->renderLayout();
    }

    /**
     * Create new blog product page
     */
    public function newAction()
    {
        $attributeSetId = Mage::getStoreConfig("blog/settings/attribute_set_id");
        $productType = Mage::getStoreConfig("blog/settings/product_type");
        $redirectUrl = 'adminhtml/catalog_product/new/set/' . $attributeSetId . '/type/' . $productType;
        $this->_redirect($redirectUrl);
    }
}
