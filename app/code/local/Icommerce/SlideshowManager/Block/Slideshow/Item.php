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
class Icommerce_SlideshowManager_Block_Slideshow_Item extends Mage_Adminhtml_Block_Widget_Container
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
    	$helper = Mage::helper('slideshowmanager');

    	if($helper->isHtmlActive()){
	    	$this->_addButton('add_new_html', array(
	            'label'   => Mage::helper('slideshowmanager')->__('Add New HTML block'),
	            'onclick'   => 'window.location.href=\''.$this->getUrl('*/slideshowitem/addhtml/').'\'',
	            'class'   => 'add',
	            'title'   => 'Used for creating a HTML slide'
	        ));
	    }
        if($helper->isEasyActive()){
            $this->_addButton('add_new_easy', array(
                'label'   => Mage::helper('slideshowmanager')->__('Add New Easy block'),
                'onclick'   => 'window.location.href=\''.$this->getUrl('*/slideshowitem/addeasy/').'\'',
                'class'   => 'add',
                'title'   => 'Used for creating a Easy slide'
            ));
        }
	    if($helper->isLayeredHtmlActive()){
            $this->_addButton('add_new_layeredhtml', array(
	            'label'   => Mage::helper('slideshowmanager')->__('Add New Layered HTML block'),
	            'onclick'   => 'window.location.href=\''.$this->getUrl('*/slideshowitem/addlayeredhtml/').'\'',
	            'class'   => 'add',
	            'title'   => 'Used for positioning a block on top of an image'
	        ));
        }
        if($helper->isProductActive()){
            $this->_addButton('add_new_product', array(
                'label' => Mage::helper('slideshowmanager')->__('Add New Product block'),
                'onclick' => 'window.location.href=\''.$this->getUrl('*/slideshowitem/addproduct/').'\'',
                'class' => 'add',
                'title' => 'Provides a block with product data',
            ));
        }
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('slideshowmanager')->__('Add New Image'),
            'onclick'   => 'window.location.href=\''.$this->getUrl('*/slideshowitem/add/').'\'',
            'class'   => 'add'
        ));
    }

    public function getRowUrl($row)
    {
    	return $this->getUrl('*/slideshowitem/edit/', array('id' => $row));
    }

    public function getHtmlRowUrl($row)
    {
    	return $this->getUrl('*/slideshowitem/edithtml/', array('id' => $row));
    }

    public function getEasyRowUrl($row)
    {
        return $this->getUrl('*/slideshowitem/editeasy/', array('id' => $row));
    }

    public function getLayeredHtmlRowUrl($row)
    {
        return $this->getUrl('*/slideshowitem/editlayeredhtml/', array('id' => $row));
    }

    public function getProductUrl($row)
    {
        return $this->getUrl('*/slideshowitem/editproduct/', array('id' => $row));
    }

	public function getItems(){
		return Mage::getSingleton('slideshowmanager/item')->getItems((int)$_SESSION['slideshow_id']);
	}
}

