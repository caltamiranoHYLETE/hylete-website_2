<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 * @author      Urmo Schmidt
 */

class Vaimo_IntegrationBase_Model_Price extends Vaimo_IntegrationBase_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/price');
    }

    /**
     * Load price import data for certain product in certain store
     *
     * @param $sku
     * @param int $storeId
     * @return $this
     */
    public function loadBySkuAndStore($sku, $storeId = null)
    {
        if (is_object($storeId)) {
            $storeId = $storeId->getId();
        }

        $this->_getResource()->loadByKeys($this, array(
            'sku' => $sku,
            'store_id' => $storeId
        ));

        return $this;
    }

    /**
     * Set other product related data that needs to be changed after price import
     *
     * @param array $data
     * @return Varien_Object
     */
    public function setProductData(array $data)
    {
        return $this->_setComplexData('product_data', $data);
    }

    /**
     * Get other product related data that needs to be changed after price import
     *
     * @param null|string $valueKey Gives the oportunity to fetch a single values from the serialized data
     * @return array|mixed|string
     */
    public function getProductData($valueKey = null)
    {
        return $this->_getComplexData('product_data', $valueKey);
    }
}