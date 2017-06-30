<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kaarel
 * Date: 28.03.12
 * Time: 14:20
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_Style_Block_Adminhtml_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    private $parent;
    /**
     * @return mixed
     */
    protected function _prepareLayout()
    {
        $type = $this->getRequest()->getParam('type');
        if(empty($type)) {
            $type = $this->getProduct()->getData('type_id');
        }
        //get all existing tabs
        $this->parent = parent::_prepareLayout();

        if($type == 'style') {
            $this->removeTab('upsell');
            $this->removeTab('crosssell');
            $this->removeTab('productalert');
            $this->removeTab('reviews');
            $this->removeTab('related');
            $this->removeTab('set');
            $this->removeTab('customer_options');

            $this->removeTab('group_18');
            $this->removeTab('group_20');
            $this->removeTab('group_9');

            $this->addTab('related', array(
                'label'     => Mage::helper('catalog')->__('Style Products'),
                'url'       => $this->getUrl('*/*/related', array('_current' => true)),
                'class'     => 'ajax',
            ));
        }
        if($type == 'configurable') {
            $productId = $this->getProduct()->getId();
            $this->addTab('related2', array(
                'label'     => Mage::helper('catalog')->__('Style Products'),
                'url'       => $this->getUrl('style/admin/related2', array('product_id' => $productId)),
                'class'     => 'ajax',
            ));

        }
        return $this->parent;
    }
}