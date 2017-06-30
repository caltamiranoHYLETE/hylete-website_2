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
 * @package     Icommerce_
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 */
class Icommerce_PageManager_Block_Page_Row_Item_Addcategory extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Prepare button
     *
     */
    protected function _prepareLayout()
    {
        $this->_addButton('backButton', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => 'history.back()',
            'class' => 'back'
        ));
        $this->_addButton('addcategory_save', array(
            'label'   => Mage::helper('pagemanager')->__('Save'),
            'onclick' => 'addcategoryForm.submit()',
            'class'   => 'save'
        ));

    }

    public function getItemType()
    {
        return array('value'=>'category', 'label'=>Mage::helper('pagemanager')->__('Category'));
    }

}