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
 * @package     Icommerce_SlideshowManager
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */
class Icommerce_SlideshowManager_Block_Slideshow_Item_Addhtml extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Prepare button
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        $this->_addButton('backButton', array(
                    'label'     => Mage::helper('adminhtml')->__('Back'),
                    'onclick'   => 'history.back()',
                    'class' => 'back'
        ));
        $this->_addButton('addhtml_save', array(
            'label'   => Mage::helper('slideshowmanager')->__('Save'),
            'onclick' => 'addhtmlForm.submit()',
            'class'   => 'save'
        ));

        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

    }

    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getForm()
    {
        return $this->getLayout()
            ->createBlock('slideshowmanager/slideshow_item_addhtml_form')
            ->toHtml();
    }




}




