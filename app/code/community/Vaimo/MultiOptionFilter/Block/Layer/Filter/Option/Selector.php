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

class Vaimo_MultiOptionFilter_Block_Layer_Filter_Option_Selector extends Vaimo_MultiOptionFilter_Block_Template
{
    protected $_selectedOptionsMapping;

    protected function _construct()
    {
        $this->setTemplate('catalog/layer/option/selector.phtml');

        parent::_construct();
    }

    public function hasSelectedOptions()
    {
        return (bool)$this->getSelectedOptionsMapping();
    }

    public function getSelectedOptionsMapping()
    {
        if ($this->_selectedOptionsMapping === null) {
            $this->_selectedOptionsMapping = Mage::helper('multioptionfilter')
                ->getAllSelectedLayerOptions(true);
        }

        return $this->_selectedOptionsMapping;
    }

    public function getSelectedOptionsMappingAsJson()
    {
        $filtersWithSelectedOptions = $this->getSelectedOptionsMapping();
        $selectedOptions = array();

        foreach ($filtersWithSelectedOptions as $filter) {
            $selectedOptions[$filter['code']] = $filter['selected'];
        }

        return json_encode($selectedOptions);
    }

    public function getFilterSequenceAsJson()
    {
        return json_encode(
            Mage::helper('multioptionfilter')->getFilterRequestVarsInRenderSequence()
        );
    }
}