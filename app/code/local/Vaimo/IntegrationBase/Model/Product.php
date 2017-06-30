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
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Product extends Vaimo_IntegrationBase_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/product');
    }

    /**
     * Set the configurable attributes of the created product (only needed for configurable product)
     *
     * @param array $configurableAttributes
     * @return Varien_Object
     */
    public function setConfigurableAttributes(array $configurableAttributes)
    {
        return $this->setData('configurable_attributes', implode(',', $configurableAttributes));
    }

    /**
     * Get the configurable attributes of the created product
     *
     * @return array
     */
    public function getConfigurableAttributes()
    {
        return $this->getData('configurable_attributes') ? explode(',', $this->getData('configurable_attributes')) : array();
    }

    /**
     * Set the data that will be added as product entity data when the product will be created. This is added after
     * other configuration which gives the oportunity to override certain values.
     *
     * @param array $data
     * @return Varien_Object
     */
    public function setProductData(array $data)
    {
        return $this->_setComplexData('product_data', $data);
    }

    public function addProductData(array $data)
    {
        return $this->_addComplexData('product_data', $data);
    }

    /**
     * Get the data the will be added as product entity data when the product will be created
     *
     * @param null|string $valueKey Gives the oportunity to fetch a single values from the serialized data
     * @return array|mixed|string
     */
    public function getProductData($index = null)
    {
        return $this->_getComplexData('product_data', $index);
    }

    public function setBundleData(array $data)
    {
        return $this->_setComplexData('bundle_data', $data);
    }

    public function getBundleData()
    {
        return $this->_getComplexData('bundle_data');
    }
}