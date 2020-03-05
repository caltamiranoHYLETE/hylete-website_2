<?php

class Bestworlds_KlaviyoExtend_Model_Klaviyo
{

    const QUOTE_ORDER_TIME_ADJUSTMENT_SECONDS = 5;

    public function sendKlaviyoTrack($quote) {
        if (Mage::getStoreConfigFlag('klaviyoextend/basic/enable')) {
            $tracker = $this->getKlaviyoTracker($quote);
            $billing_address = $quote->getBillingAddress();

            if (!$billing_address || !$tracker) {
                return false;
            }

            // Skip quotes that don't have an email or remote_ip set. Checking the IP is our best guess
            // at not sending emails for quotes created on the Magento backend. There
            // doesn't seem a reliable way to tell if a quote is from the frontend or backend.
            if (!$quote->getCustomerEmail() || is_null($quote->getRemoteIp())) {
                return false;
            }

            $quote_id = $quote->getId();

            $checkout = Mage::helper('klaviyo_reclaim')->getCheckout($quote_id);

            $customer_properties = $this->_customerPropertiesForQuote($quote, $billing_address);

            $item_details = array();

            $configurable_product_ids = array();

            foreach ($quote->getItemsCollection() as $quote_item) {
                $response = $this->_quoteItemData($quote_item);
                $quote_item_data = $response['quote_item'];
                $is_product_configurable = $response['is_product_configurable'];

                // Configurable product - The "product" when the product has multiple SKUs, e.g. "t-shirt"
                // Simple product - The "SKU," e.g. "red, medium t-shirt." For products that don't have options or variations,
                // this is the "product."
                // http://docs.magento.com/m1/ce/user_guide/catalog/product-create.html
                if ($is_product_configurable) {
                    $configurable_product_ids[] = $quote_item_data['product']['id'];
                }

                $item_details[] = $quote_item_data;
            }

            $response = $this->_mergeConfigurableAndSimpleQuoteItems($item_details, $configurable_product_ids);
            $item_details = $response['items'];
            $item_count = $response['item_count'];
            $item_descriptions = $response['item_descriptions'];

            // Skip quotes that don't have items.
            if (empty($item_details)) {
                return false;
            }

            $quote_categories = array();
            foreach($item_details as $item){
                $quote_categories = array_merge($quote_categories, $item['product']['categories']);
            }

            $quote_categories = array_unique($quote_categories);

            $checkout_id = $checkout->getId();
            $checkout_url = $quote->getStore()->getUrl('reclaim/index/view', array('_query' => array('id' => $checkout_id)));

            $properties = array(
                '$event_id'   => $quote_id,
                '$value'      => (float) $quote->getGrandTotal(),
                'Items'       => $item_descriptions,
                'Items Count' => $item_count,
                'Categories' => $quote_categories,
                '$extra'      => array(
                    'checkout_url' => $checkout_url,
                    'checkout_id'  => $checkout_id,
                    'line_items'   => $item_details,
                )
            );

            $coupon_code = $quote->getCouponCode();
            if ($coupon_code) {
                $totals = $quote->getTotals();
                $properties['Discount Codes'] = array($coupon_code);
                if (array_key_exists('discount', $totals) && is_object($totals['discount'])) {
                    $properties['Total Discounts'] = (float) $totals['discount']->getValue() * -1;
                }
            }

            $quote_last_updated = strtotime($quote->getUpdatedAt());
            // So we don't die sending checkout completed if there really is no order
            $order_created_at = $quote_last_updated;

            $order = Mage::getModel('sales/order')->load($quote->getEntityId(), 'quote_id');
            // is_active isn't reliable enough to nest this in
            if ($order->getId()) {
                $order_created_at = strtotime($order->getCreatedAt());

                // This can happen because of one page checkouts and other checkout extensions
                if ($quote_last_updated >= $order_created_at) {
                    $quote_last_updated = $order_created_at - self::QUOTE_ORDER_TIME_ADJUSTMENT_SECONDS;
                }
            }

            $tracker->track('Cart_Started', $customer_properties, $properties, $quote_last_updated);

            return true;
        }
        return false;
    }

    /**
     * Merged quote item (line item) details in cases where there are two line items to represent
     * a configurable product and its options.
     *
     * @param $item_details (array)
     * @param $configurable_product_ids (array)
     * @return array
     */
    protected function _mergeConfigurableAndSimpleQuoteItems($item_details, $configurable_product_ids) {
        $model_configurable = Mage::getModel('catalog/product_type_configurable');

        $merged = array();
        $item_descriptions = array();
        $item_count = 0;

        // Look for simple products that are really represented by a configurable product.
        foreach ($item_details as $line_index => $line) {
            $product_id = $line['product']['id'];
            if (in_array($product_id, $configurable_product_ids) || $line['row_total']) {
                $merged[] = $line;
            } else {
                $configurable_parent_ids = $model_configurable->getParentIdsByChild($product_id);
                $common_ids = array_intersect($configurable_product_ids, $configurable_parent_ids);

                // If it's a simple product placeholder for a configurable product, discard it. Otherwise, keep it.
                if (empty($common_ids)) {
                    $merged[] = $line;
                } else {
                    continue;
                }
            }

            if (array_key_exists('simple_name', $line['product'])) {
                $item_descriptions[] = $line['product']['simple_name'];
            } else {
                $item_descriptions[] = $line['product']['name'];
            }

            $item_count += $line['quantity'];
        }

        return array(
            'items'             => $merged,
            'item_count'        => $item_count,
            'item_descriptions' => $item_descriptions
        );
    }

    /**
     * Normalize quote item (line item) to an array that's JSON serializable.
     *
     * @param $quote_item (Mage_Sales_Model_Quote_Item)
     * @return array
     */
    protected function _quoteItemData ($quote_item) {
        $data = array();

        $quote_item_quantity = $quote_item->getQty();
        $quote_item_product = Mage::getModel('catalog/product')->setStoreId($quote_item->getQuote()->getStoreId())->load($quote_item->getProduct()->getId());

        $categories = array();

        foreach ($quote_item_product->getCategoryCollection()->addAttributeToSelect('name') as $category) {
            $categories[] = $category->getName();
        }

        $product_details = array(
            'id'    => $quote_item_product->getId(),
            'type'  => $quote_item_product->getType(),
            'sku'   => $quote_item_product->getSKU(),
            'name'  => $quote_item_product->getName(),
            'price' => (float) $quote_item_product->getPrice(),
            'special_price' => (float) $quote_item_product->getFinalPrice(),
            'categories' => $categories
        );

        $product_images = array();
        // If we have a simple product for this configurable product, get the simple name
        // and also attempt to get the images
        if ($option = $quote_item->getOptionByCode('simple_product')) {
            $simple_product = Mage::getModel('catalog/product')->load($option->getProduct()->getId());
            $product_details['simple_name'] = $simple_product->getName();
            foreach ($simple_product->getMediaGalleryImages() as $product_image) {
                if (!$product_image->getDisabled()) {
                    $product_images[] = array(
                        'url' => $product_image->getUrl()
                    );
                }
            }
        }

        // if there is no simple product or we tried but couldn't find any
        // images for the simple product just grab the configurable images
        if (empty($product_images)) {
            foreach ($quote_item_product->getMediaGalleryImages() as $product_image) {
                if (!$product_image->getDisabled()) {
                    $product_images[] = array(
                        'url' => $product_image->getUrl()
                    );
                }
            }
        }

        if (empty($product_images)) {
            $product_images = $this->try_to_get_image_from_parent($quote_item_product);
        }

        $product_details['images'] = $product_images;

        return array(
            'is_product_configurable' => $quote_item_product->isConfigurable(),
            'quote_item'              => array(
                'quantity'     => (float) $quote_item_quantity,
                'row_total'    => (float) $quote_item->getBaseRowTotal(),
                'row_discount' => (float) $quote_item->getBaseDiscountAmount(),
                'product'      => $product_details
            )
        );
    }

    // attempt to get images from the Conbfigurable, Grouped or Bundled Product parent if the child product does
    // have images
    function try_to_get_image_from_parent($quote_item_product) {

        $product_images = array();

        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($quote_item_product->getId());
        if(empty($parentIds)) {
            $parentIds = Mage::getResourceSingleton('catalog/product_link')->getParentIdsByChild($quote_item_product->getId(), Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED);
            if(empty($parentIds)) {
                $parentIds = Mage::getModel('bundle/product_type')->getParentIdsByChild($quote_item_product->getId());
            }
        }

        if(!empty($parentIds)) {
            $product_images = $this->get_image_of_parent($parentIds[0]);
        }

        return $product_images;
    }


    function get_image_of_parent($parentId) {
        $parent_product = Mage::getModel('catalog/product')->load($parentId);
        foreach ($parent_product->getMediaGalleryImages() as $product_image) {
            if (!$product_image->getDisabled()) {
                $product_images[] = array(
                    'url' => $product_image->getUrl()
                );
            }
        }
        return $product_images;
    }

    /**
     * Normalize customer and billing address data to an array that's JSON serializable.
     *
     * @param $quote (Mage_Sales_Model_Quote)
     * @param $billing_address (Mage_Sales_Model_Quote_Address)
     * @return array
     */
    protected function _customerPropertiesForQuote($quote, $billing_address) {
        $properties = array(
            '$email'            => $quote->getCustomerEmail(),
            '$first_name'       => $quote->getCustomerFirstname(),
            '$last_name'        => $quote->getCustomerLastname(),
            'Magento Store'     => $quote->getStore()->getName(),
            'Magento WebsiteID' => $quote->getStore()->getWebsiteId(),
            'location'          => array(
                'source'   => 'magento',
                'address1' => $billing_address->getStreet(1),
                'city'     => $billing_address->getCity(),
                'region'   => $billing_address->getRegion(),
                'zip'      => $billing_address->getPostcode(),
                'country'  => $billing_address->getCountry()
            )
        );

        if ($billing_address->getStreet(2)) {
            $properties['location']['address2'] = $billing_address->getStreet(2);
        }

        return $properties;
    }

    public function getKlaviyoTracker($quote) {
        $store_id       = $quote->getStoreId();
        $public_api_key = Mage::helper('klaviyo_reclaim')->getPublicApiKey($store_id);
        if(!$public_api_key){
            return NULL;
        }
        $tracker =  new Klaviyo_Reclaim_Model_Tracker($public_api_key);
        return $tracker;
    }
}

