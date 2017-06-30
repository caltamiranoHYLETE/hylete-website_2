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

class Vaimo_MultiOptionFilter_Model_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    protected $_resourceProxy;

    public function _getResource()
    {
        if (!$this->_resourceProxy) {
            $this->_resourceProxy = Mage::getSingleton(('multioptionfilter/proxy_price'))
                ->create($this, parent::_getResource());
        }

        return $this->_resourceProxy;
    }
    protected function _renderItemLabelFromTo($fromPrice, $toPrice)
    {
        $store = Mage::app()->getStore();
        $fromPrice = $store->formatPrice($fromPrice);
        $toPrice = $store->formatPrice($toPrice);

        return Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
    }

    /**
     * Apply price range filter to collection
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param $filterBlock
     *
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Filter must be string: $index,$range
         */
        if (!$filter = $request->getParam($this->getRequestVar())) {
            return $this;
        }

        if (false === strpos($filter, '..') && false === strpos($filter, '-')) {
            $filter = explode(',', $filter);

            if (count($filter) != 2) {
                return $this;
            }

            $requestClone = $request;
        } else {
            $originalFilter = $filter;
            $filter = explode('..', $filter);

            if (count($filter) != 2) {
                /**
                 * in 1.7 we have minus as separator
                 * @src http://svn.magentocommerce.com/source/branches/1.7/app/code/core/Mage/Catalog/Model/Layer/Filter/Price.php::_getItemsData
                 */
                $filter = explode('-', $originalFilter);

                if (count($filter) != 2) {
                    return $this;
                }
            }

            $requestClone = clone $request;
            $requestClone->setParam($this->getRequestVar(), implode('-', $filter));
        }

        return parent::apply($requestClone, $filterBlock);
    }
}
