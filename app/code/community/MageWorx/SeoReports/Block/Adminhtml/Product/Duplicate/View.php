<?php
/**
 * MageWorx
 * MageWorx SeoReports Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoReports
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */



class MageWorx_SeoReports_Block_Adminhtml_Product_Duplicate_View extends Mage_Adminhtml_Block_Widget_Container
{

    protected function _prepareLayout()
    {
        $this->_addButton(
            'back_button',
            array(
            'label'   => Mage::helper('mageworx_seoreports')->__('Back'),
            'onclick' => "setLocation('{$this->getUrl('*/*/index')}')",
            'class'   => 'back'
            )
        );

        $this->_addButton(
            'generate',
            array(
            'label'   => Mage::helper('mageworx_seoreports')->__('Generate'),
            'onclick' => "setLocation('{$this->getUrl('*/*/generate')}')",
            'class'   => 'generate'
            )
        );

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'mageworx_seoreports/adminhtml_product_duplicate_view_grid',
                'mageworx.seoreports.product.duplicate.view.grid'
            )
        );
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}
