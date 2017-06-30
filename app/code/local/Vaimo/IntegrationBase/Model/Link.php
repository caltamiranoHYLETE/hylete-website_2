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
 *
 * @method string getLinkTypeCode()
 * @method string getProductSku()
 * @method string getLinkedProductSku()
 */

class Vaimo_IntegrationBase_Model_Link extends Vaimo_IntegrationBase_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/link');
    }

    /**
     * Load product link import data by type and sku's of the products that will be linked. Created link is uni-directional
     *
     * @param string $linkTypeCode Type of the link (a new link type will be created if there is none)
     * @param string $productSku    Sku of the product where the link will be listed
     * @param string $linkedProductSku  Sku of the product that will be linked
     * @return $this
     */
    public function loadByTypeAndSkus($linkTypeCode, $productSku, $linkedProductSku)
    {
        $this->_getResource()->loadByKeys($this, array(
            'link_type_code' => $linkTypeCode,
            'product_sku' => $productSku,
            'linked_product_sku' => $linkedProductSku
        ));

        return $this;
    }

    /**
     * Set other link related data that will be added to the link entity when it's created
     *
     * @param array $data
     * @return Varien_Object
     */
    public function setLinkData(array $data)
    {
        return $this->_setComplexData('link_data', $data);
    }

    /**
     * Get other link related data that will be added to the link entity when it's created
     *
     * @param null $valueKey
     * @return array|mixed|string
     */
    public function getLinkData($valueKey = null)
    {
        return $this->_getComplexData('link_data', $valueKey);
    }
}