<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Kjell Holmqvist
 */

class Vaimo_AppApi_Model_Product extends Vaimo_AppApi_Model_Abstract
{

    public function listDetails($websiteId, $storeId, $productId, $detailLevel)
    {
        $selectedStoreId = $this->_getHelper()->getStoreId($websiteId, $storeId);

        $product = Mage::getModel('catalog/product')
            ->setStoreId($selectedStoreId)
            ->load($productId);

        if (!$product->getId()) {
            return 'Wrong product ID';
        }

        $prodObj = $this->_getHelper()->getProductDetails($product, $detailLevel);

        $res = $this->_getHelper()->dispatchUpdateEventObject( 'app_api_product_info_record', $prodObj, array('product' => $product) );

        return $res;
    }

}
