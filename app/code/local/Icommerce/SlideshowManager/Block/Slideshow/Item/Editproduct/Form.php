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
class Icommerce_SlideshowManager_Block_Slideshow_Item_Editproduct_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getItemType()
    {
        return array('value' => 'product', 'label' => Mage::helper('slideshowmanager')->__('Product'));
    }

    protected function _prepareForm()
    {
        $model  = Mage::getModel('slideshowmanager/text');
        $params = $this->getRequest()->getParams();
        $id = (int)$params['id'];
        $item = $model->getItem($id);

        $form = new Varien_Data_Form(array(
            'id' => 'product_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('slideshowmanager')->__('Enter product information below'),
            'class' => 'fieldset-wide'
        ));

        $itemType = $this->getItemType();
        $fieldset->addField('type', 'hidden', array(
            'name' => 'type',
            'value' => $itemType['value'],
        ));

        $fieldset->addField('product_id', 'text', array(
                'name' => 'product_id',
                'label' => Mage::helper('slideshowmanager')->__('Product ID'),
                'title' => Mage::helper('slideshowmanager')->__('Product ID'),
                'required' => false,
                'value' => $item['product_id'],
        ));

        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => Mage::helper('slideshowmanager')->__('Status'),
            'title' => Mage::helper('slideshowmanager')->__('Status'),
            'required' => true,
            'values' => Mage::helper('slideshowmanager')->getStatuses(),
            'value' => $item['status']
        ));

        $fieldset->addField('position', 'text', array(
            'name' => 'position',
            'label' => Mage::helper('slideshowmanager')->__('Position'),
            'title' => Mage::helper('slideshowmanager')->__('Position'),
            'required' => false,
            'value' => $item['position']
        ));


        $form->setAction($this->getUrl('*/*/product'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
