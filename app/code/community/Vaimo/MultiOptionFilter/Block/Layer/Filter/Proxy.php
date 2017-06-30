<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_MultiOptionFilter_Block_Layer_Filter_Proxy extends Mage_Core_Block_Template
{
    protected $_delegate;
    protected $_rendered = false;

    public function getDelegate()
    {
        return $this->_delegate;
    }

    public function getIsRendered()
    {
        return $this->_rendered;
    }

    public function setDelegate(Mage_Catalog_Block_Layer_Filter_Abstract $filterBlock)
    {
        $this->_delegate = $filterBlock;
        $this->setTemplate($filterBlock->getTemplate());
    }

    public function urlEscape($string)
    {
        $string = parent::urlEscape($string);

        return str_replace('&amp;', '&', $string);
    }

    public function init()
    {
        return $this->_delegate->init();
    }

    public function getName()
    {
        $name = $this->_delegate->getName();

        if ($this->getShowSelectedOptionsCountInName()) {
            if ($selectedOptions = Mage::helper('multioptionfilter')->getSelectedLayerOptions($this)) {
                $name .= ' (' . count($selectedOptions['selected']) . ')';
            }
        }

        return $name;
    }

    public function getAttributeCode()
    {
        return Mage::helper('multioptionfilter/filter')->getRequestVarForFilterBlock($this->_delegate);
    }

    public function getItems()
    {
        return Mage::helper('multioptionfilter')->getItems($this->_delegate);
    }

    public function getItemsCount()
    {
        $attributeCode = $this->getAttributeCode();
        /** @var Vaimo_MultiOptionFilter_Helper_Data $helper */
        $helper = Mage::helper('multioptionfilter');

        if (!$helper->displayAttribute($attributeCode)) {
            $items = array();
        } else {
            $items = $helper->getItems($this->_delegate);
        }

        return count($items);
    }

    public function shouldDisplayProductCount()
    {
        return $this->_delegate->shouldDisplayProductCount();
    }

    public function __call($method, $args)
    {
        if ($this->_delegate) {
            return $this->_delegate->__call($method, $args);
        }

        return parent::__call($method, $args);
    }

    public function getData($key='', $index=null)
    {
        if ($this->_delegate) {
            return $this->_delegate->getData($key, $index);
        } else {
            return parent::getData($key, $index);
        }
    }

    public function getHtml()
    {
        $this->_rendered = true;
        $requestVar = Mage::helper('multioptionfilter/filter')->getRequestVarForFilterBlock($this->_delegate);

        $layerView = $this->getParentBlock();
        $renderedAttributes = $layerView->getDataSetDefault('rendered_filter_request_vars', array());
        $renderedAttributes[] = $requestVar;
        $layerView->setRenderedFilterRequestVars($renderedAttributes);

        return parent::_toHtml();
    }
}