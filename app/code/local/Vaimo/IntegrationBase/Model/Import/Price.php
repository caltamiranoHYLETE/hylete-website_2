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

class Vaimo_IntegrationBase_Model_Import_Price extends Vaimo_IntegrationBase_Model_Import_Abstract
{
    protected $_productModel;
    protected $_eventPrefix = 'price';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/price', 'Price import');
        $this->_successMessage = '%d price(s) imported';
        $this->_failureMessage = '%d price(s) failed to import';
        $this->_productModel = Mage::getModel('catalog/product');
    }

    protected function _importRecord($item)
    {
        $this->_log($item->getSku() . ' - ' . $item->getPrice());
        $productId = $this->_productModel->getIdBySku($item->getSku());

        if (!$productId) {
            Mage::throwException('Product not found');
        }

        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->_productModel->reset();
        $product->load($productId)->setOrigData(null, null);

        if (!$product->getId()) {
            Mage::throwException('Product could not be loaded');
        }


        /** Customer group price **/
        if(!is_null($item->getCustomerGroupId())){
            $groupPriceArray = $product->getData("group_price");
            if (array_key_exists($item->getCustomerGroupId(), $groupPriceArray)){
                $groupPriceArray[$item->getCustomerGroupId()]['price'] = $item->getGroupPrice();
                $groupPriceArray[$item->getCustomerGroupId()]['website_price'] = $item->getGroupPrice();
            } else {
                $storeData = Mage::getModel('core/store')->load($item->getStoreId());
                $websiteId = $storeData->getWebsiteId();
                $groupPriceArray[$item->getCustomerGroupId()] = array(
                    'website_id' => $websiteId,
                    'all_groups' => "0",
                    'cust_group' => $item->getCustomerGroupId(),
                    'price' => $item->getPrice(),
                    'website_price' => $item->getPrice(),
                );
            }
            $product->setData("group_price", $groupPriceArray);
        } else {
            if ($storeId = $item->getStoreId()) {
                $product->setStoreId($storeId);
                if ($storeId != 0){
                    $product->setOrigData("price", "FORCEUPDATE");
                    $product->setOrigData("special_price", "FORCEUPDATE");
                    $product->setOrigData("special_price_from_date", "FORCEUPDATE");
                    $product->setOrigData("special_price_to_date", "FORCEUPDATE");
                    if ($item->getTaxClassId() !== null) {
                        $product->setOrigData("tax_class_id", "FORCEUPDATE");
                    }
                }
            }

            $product->setPrice((string)$item->getPrice());
            // Note that setting this to NULL will actually end up leaving the special price active
            $product->setSpecialPrice((string)$item->getSpecialPrice()); // TODO: maybe should check it, and not override if empty
            $product->setSpecialFromDate((string)$item->getSpecialFromDate());
            $product->setSpecialToDate((string)$item->getSpecialToDate());

            if ($item->getTaxClassId() !== null) {
                $product->setTaxClassId($item->getTaxClassId());
            }

            $product->addData($item->getProductData());
        }



        $product->save();

        Mage::helper('integrationbase/pricerules')->applyAllRulesToProduct($product,'price');

        return $this;
    }

    protected function _deleteRecord($item)
    {
        Mage::throwException('Price import does not support delete');
    }
}
