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

class Vaimo_AppApi_ProductController extends Vaimo_AppApi_Controller_Action
{
    /**
     * Retrive a list of categories
     *
     * Expects product, website (optional), store (optional), detail (optional)
     *
     * @return void
     */
    public function detailAction()
    {
        $result = array();

        if ($this->_isRequestGet($result)) {

            $productId = $this->_request->getParam('product', null);
            $websiteId = $this->_request->getParam('website', 0);
            $storeId = $this->_request->getParam('store', null);
            $detailLevel = $this->_request->getParam('detail', Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_DEFAULT);

            try {
                Mage::register('isSecureArea', true);

                $apiResult = Mage::getModel('appapi/product')->listDetails(
                    $websiteId,
                    $storeId,
                    $productId,
                    $detailLevel
                );

                Mage::unregister('isSecureArea');

                if (is_string($apiResult)) {
                    $result['status'] = 'error';
                } else {
                    $result['status']  = 'success';
                }
                $result['result']  = $apiResult;
            }
            catch (Exception $e) {
                $result['status'] = 'error';
                $result['result']  = $e->getMessage();
            }
        }

        $jsonData = Mage::helper('core')->jsonEncode($result);

        $this->_response->setHeader('Content-type', 'application/json');
        $this->_response->setBody($jsonData);
    }


}