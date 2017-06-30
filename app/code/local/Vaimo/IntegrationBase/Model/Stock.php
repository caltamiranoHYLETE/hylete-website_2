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
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt
 */

class Vaimo_IntegrationBase_Model_Stock extends Vaimo_IntegrationBase_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/stock');
    }

    /**
     * Store the data that will be added to the created stock item
     *
     * @param array $data   Stock item data
     * @return Varien_Object
     */
    public function setStockData(array $data)
    {
        return $this->setData('stock_data', serialize($data));
    }

    /**
     * Get the data that will be added to the created stock item
     *
     * @param   string  Gives the oportunity to fetch just a single value from the serialized data
     * @return array|mixed
     */
    public function getStockData($valueKey = null)
    {
        return $this->_getComplexData('stock_data', $valueKey);
    }
}