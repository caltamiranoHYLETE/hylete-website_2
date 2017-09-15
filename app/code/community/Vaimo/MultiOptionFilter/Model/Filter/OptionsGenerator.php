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

class Vaimo_MultiOptionFilter_Model_Filter_OptionsGenerator
{
    /**
     * @var Vaimo_MultiOptionFilter_Helper_Filter
     */
    protected $_filterHelper;

    public function __construct()
    {
        $this->_filterHelper = Mage::helper('multioptionfilter/filter');
    }

    public function generate($attributeCode, $items)
    {
        $itemsByLabel = array();

        foreach ($items as $item) {
            $label = $item->getData('label');
            $value = $item->getData('value');

            $url = $this->_filterHelper->getOptionUrl($attributeCode, $item->getData('value'))
                . $this->_filterHelper->getQueryString($attributeCode, $value, true);

            $itemsByLabel[$label] = new Varien_Object(array(
                'label' => $label,
                'value' => $value,
                'count' => $item->getData('count'),
                'url' => $url
            ));
        }

        return $itemsByLabel;
    }
}
