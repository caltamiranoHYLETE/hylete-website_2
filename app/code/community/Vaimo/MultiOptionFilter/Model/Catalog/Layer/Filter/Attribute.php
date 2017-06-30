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

class Vaimo_MultiOptionFilter_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    protected $_currentRequest;
    protected $_resourceProxy;

    public function _getResource()
    {
        if (!$this->_resourceProxy) {
            $this->_resourceProxy = Mage::getSingleton(('multioptionfilter/proxy_attribute'))
                ->create($this, parent::_getResource());
        }

        return $this->_resourceProxy;
    }

    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $this->_currentRequest = $request;

        if (!$filter = $request->getParam($this->_requestVar)) {
            return $this;
        }

        $value = $this->_getOptionText($filter);

        if (is_array($value) && !count($value)) {
            return $this;
        }

        if (is_string($value) && !strlen($value)) {
            return $this;
        }

        $this->_getResource()->applyFilterToCollection($this, $filter);

        if (!is_array($value)) {
            $value = array($value);
        }

        $stateModel = $this->getLayer()->getState();

        foreach (explode(',', $filter) as $filter) {
            $filterState = $this->_createItem(array_shift($value), $filter);
            $stateModel->addFilter($filterState);
        }

        $this->_items = array();

        return $this;
    }

    protected function _createItem($label, $value, $count = 0)
    {
        return Mage::getModel('multioptionfilter/filter_item', array(
            'filter' => $this,
            'label' => $label,
            'value' => $value,
            'count' => $count
        ));
    }

    public function getResetValue()
    {
        $value = parent::getResetValue();

        if (!empty($value)) {
            return $value;
        }

        if (!$this->_currentRequest) {
            return $value;
        }

        $filter = $this->_currentRequest->getParam($this->_requestVar);
        $selectedOptions = explode(',', $filter);

        if (count($selectedOptions) <= 1) {
            return $value;
        }

        /** @var Vaimo_MultiOptionFilter_Model_Filter_Item $currentItem */
        if (!$currentItem = $this->getData(Vaimo_MultiOptionFilter_Helper_Filter::TARGETED_OPTION_ITEM)) {
            return $value;
        }

        $key = $currentItem->getValue();

        $selectedOptions = array_flip($selectedOptions);

        if (isset($selectedOptions[$key])) {
            unset($selectedOptions[$key]);
            $value = implode(',', array_keys($selectedOptions));
        }

        return $value;
    }
}