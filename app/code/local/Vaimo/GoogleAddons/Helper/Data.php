<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_GoogleAddons
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */
class Vaimo_GoogleAddons_Helper_Data extends Mage_Core_Helper_Abstract {

    function getUseTagManager()
    {
        return Mage::getStoreConfig("googleaddons/google_tag_manager/enable");
    }

    function getGTMContainerId()
    {
        return Mage::getStoreConfig("googleaddons/google_tag_manager/gtm_container_id");
    }

    function getUseAnalytics()
    {
        return Mage::getStoreConfig("googleaddons/google_analytics/enable");
    }

    function getUseUniversalAnalytics()
    {
        return Mage::getStoreConfig("googleaddons/google_analytics/universal");
    }

    function getUAPropertyId()
    {
        return Mage::getStoreConfig("googleaddons/google_analytics/web_property_id");
    }

    function getUseDynamicRemarketing()
    {
        return Mage::getStoreConfig("googleaddons/adwords_dynamic_remarketing/enable");
    }

    function getTotalIncludeTaxAndShipping()
    {
        return Mage::getStoreConfig("googleaddons/settings/include_tax_in_total");
    }

    function getIncludeTaxItItems()
    {
        return Mage::getStoreConfig("googleaddons/settings/include_tax_in_items");
    }

    function getTotalIncludeDiscounts()
    {
        return Mage::getStoreConfig("googleaddons/settings/include_discount_in_total");
    }

    /**
     * @return Mage_Sales_Model_Order|Mage_Sales_Model_Quote|null
     */
    public function getLastOrderOrQuote()
    {
        $result = null;

        $klarnaCheckoutId = Mage::getSingleton('checkout/session')->getKlarnaCheckoutId();

        if ($klarnaCheckoutId) {
            $klarnaOrder = Mage::getModel('klarnacheckout/klarna')->getKlarnaOrder($klarnaCheckoutId);

            if ($klarnaOrder) {
                $data = $klarnaOrder->marshal();

                if ($data && isset($data['merchant_reference']['orderid1'])) {
                    $incrementId = $data['merchant_reference']['orderid1'];
                    /** @var Mage_Sales_Model_Order $result */
                    $result = Mage::getModel("sales/order")->loadByIncrementId($incrementId);
                } else {
                    /** @var Mage_Sales_Model_Quote $result */
                    $result = Mage::getModel('sales/quote')->load($klarnaCheckoutId, 'klarna_checkout_id');
                    $result->reserveOrderId()->save();
                }
            }
        }

        if (!$result) {
            $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            /** @var Mage_Sales_Model_Order $order */
            $result = Mage::getModel('sales/order')->load($lastOrderId);
        }

        return $result;
    }

    /**
     * @deprecated deprecated since version 0.1.18, will be removed 2015-04-03 - use getTransactionEventJSON() instead.
     */
    public function getPrecisDigitalJSON()
    {
        Mage::log('getPrecisDigitalJSON() is deprecated since version 0.1.18, will be removed 2015-04-03 - use getTransactionEventJSON() instead.', Zend_Log::INFO);
        return $this->getTransactionEventJSON();
    }

    /**
     * @return string|null
     */
    public function getTransactionEventJSON()
    {
        $result = null;

        if ($this->getUseTagManager()) {
            /** @var Mage_Sales_Model_Order | Mage_Sales_Model_Quote $orderData */
            $orderData = $this->getLastOrderOrQuote();

            if ($orderData) {
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
                $storeName = Mage::app()->getStore()->getName();

                $totalData = $orderData->getData();
                if ($orderData instanceof Mage_Sales_Model_Quote) {
                    $orderId = $totalData['reserved_order_id'];
                } else {
                    $orderId = $totalData['increment_id'];
                }
                $currency = $orderData->getStoreCurrencyCode();
                $total = $orderData->getGrandTotal();
                $totalIncludeTaxAndShipping = Mage::helper("googleaddons")->getTotalIncludeTaxAndShipping();
                $baseTotal = $orderData->getBaseSubtotal();
                if ($totalIncludeTaxAndShipping) {
                    $baseTotal = $orderData->getBaseGrandTotal();
                }
                $shippingCost = $orderData->getShippingAmount() ? $orderData->getShippingAmount() : $orderData->getShippingAddress()->getShippingAmount();
                $items = $orderData->getAllVisibleItems();
                $processedItems = array();
                $tax = $orderData->getTaxAmount() ? $orderData->getTaxAmount() : $orderData->getShippingAddress()->getTaxAmount();
                $product = Mage::getModel('catalog/product');
                foreach ($items as $item) {

                    $collection = $product->reset()
                        ->setId($item->getProductId())
                        ->getCategoryCollection()
                        ->addAttributeToSelect('name');

                    $categoryNames = array_map(function ($item) {
                        return $item['name'];
                    }, $collection->load()->toArray());

                    $categoryName = implode(",", $categoryNames);

                    $itemPrice = ($this->getIncludeTaxItItems()? $item->getPriceInclTax() : $item->getBasePrice());
                    $totalIncludeDiscounts = Mage::helper("googleaddons")->getTotalIncludeDiscounts();
                    if($item->getDiscountAmount() > 0 && $totalIncludeDiscounts) {
                        $itemPrice = $itemPrice-$item->getDiscountAmount();
                    }

                    //we are going to get the parent sku so we can pass this to Facebook - STK
					$parentSku = "";
					try {
						$child_id = Mage::getModel('catalog/product')->getIdBySku($item->getSku());
						$parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($child_id);
						$parent_collection = Mage::getResourceModel('catalog/product_collection')
							->addFieldToFilter('entity_id', array('in'=>$parent_ids))
							->addAttributeToSelect('sku');
						$parent_skus = $parent_collection->getColumnValues('sku');
						if(!empty($parent_skus)) {
							//we get the first one just in case there are multiple
							$parentSku = $parent_skus[0];
						}
					} catch (Exception $e) {
						//ignore
					}

                    $tempItem = array(
						"parent_sku" => $parentSku,
                    	"sku" => $item->getSku(),
                        "name" => $item->getName(),
                        "category" => $categoryName,
                        "price" => $itemPrice,
                        "qty" => $item->getQtyOrdered() ? $item->getQtyOrdered() : $item->getQty()
                    );
                    $processedItems[] = $tempItem;
                }

                $result = array(
                    'event' => 'transactionEvent',          // This event triggers the transaction
                    'customerNumber' => $customerId,        // CustomerNumber if applicable - Magento's Customer Id
                    'transactionId' => $orderId,            // Unique Transaction ID - Magento's Order Id
                    'transactionAffiliation' => $storeName, // The name of the webshop
                    'transactionTotal' => $baseTotal,       // Total Transaction Value - Magento's total order value
                    'transactionTax' => $tax,               // VAT MOMS - Magento's tax
                    'transactionCurrency' => $currency,     // Currency Code
                    'transactionShipping' => $shippingCost, // Magento's Shipping Cost
                );

                $result['transactionProducts'] = array();
                $products = & $result['transactionProducts'];

                foreach ($processedItems as $boughtItem) {

                	$data = array(
						'parent_sku' => $boughtItem['parent_sku'],
                		'sku' => $boughtItem['sku'],
                        'name' => htmlspecialchars($boughtItem['name'], ENT_QUOTES),
                        'price' => $boughtItem['price'],
                        'quantity' => intval($boughtItem['qty']),
                        'category' => ''
                    );

                    if (isset($boughtItem['category'])) {
                        $data['category'] = htmlspecialchars($boughtItem['category'], ENT_QUOTES);
                    }

                    $products [] = $data;
                }

                $data = array('info' => new Varien_Object($result));

                // deprecated since version 0.1.18, will be removed 2015-04-03 - use
                // vaimo_googleaddons_prepare_transactionevent_info_after instead.
                Mage::dispatchEvent('vaimo_googleaddons_prepare_precisdigital_info_after', $data);

                Mage::dispatchEvent('vaimo_googleaddons_prepare_transactionevent_info_after', $data);

                $result = $data['info']->getData();
            }
        }

        return Mage::helper('core')->jsonEncode($result);
    }
}