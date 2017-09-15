<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_MultiOptionFilter_Model_Runtime_Interceptors_Filter
{
    /**
     * @var Vaimo_MultiOptionFilter_Helper_Data
     */
    protected $_helper;

    /**
     * @var Vaimo_MultiOptionFilter_Helper_Filter
     */
    protected $_filterHelper;

    /**
     * @var Mage_Catalog_Block_Layer_Filter_Abstract
     */
    protected $_delegate;

    /**
     * @var bool
     */
    protected $_rendered = false;

    public function __construct()
    {
        $this->_helper = Mage::helper('multioptionfilter');
        $this->_filterHelper = Mage::helper('multioptionfilter/filter');
    }

    public function setDelegate($filterBlock)
    {
        $this->_delegate = $filterBlock;
    }

    public function urlEscape($string)
    {
        $string = $this->_delegate->urlEscape($string);

        return str_replace('&amp;', '&', $string);
    }

    public function getName()
    {
        $filterName = $this->_delegate->getName();

        if ($this->_delegate->getShowSelectedOptionsCountInName()) {
            if ($selectedOptions = $this->_helper->getSelectedLayerOptions($this)) {
                $filterName .= ' (' . count($selectedOptions['selected']) . ')';
            }
        }

        return $filterName;
    }

    public function getItems()
    {
        return $this->_helper->getItems($this->_delegate);
    }

    public function getItemsCount()
    {
        if (!$this->_helper->displayAttribute($this->getAttributeCode())) {
            return 0;
        }

        return count($this->_helper->getItems($this->_delegate));
    }

    public function getHtml()
    {
        $this->_rendered = true;

        $requestVar = $this->_filterHelper->getRequestVarForFilterBlock($this->_delegate);

        /**
         * @var Vaimo_MultiOptionFilter_Block_View @layerView
         */
        $layerView = $this->_delegate->getParentBlock();

        $renderedVars = $layerView->getDataSetDefault('rendered_filter_request_vars', array());
        $renderedVars[] = $requestVar;

        $layerView->setData('rendered_filter_request_vars', $renderedVars);
    }

    public function getAttributeCode()
    {
        return $this->_filterHelper->getRequestVarForFilterBlock($this->_delegate);
    }

    public function getIsRendered()
    {
        return $this->_rendered;
    }
}
