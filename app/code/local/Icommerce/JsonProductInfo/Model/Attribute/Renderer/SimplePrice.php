<?php

/**
 * Created by JetBrains PhpStorm.
 * User: peter lembke
 * Date: 2012-09-27
 * Time: 14.30
 * To change this template use File | Settings | File Templates.
 */

class Icommerce_JsonProductInfo_Model_Attribute_Renderer_SimplePrice extends Icommerce_JsonProductInfo_Model_Attribute_Renderer_Abstract
{

    /**
     * Returns the products final price.
     * Look at a configurable product and you will see super attributes.
     * With the super attributes you can modify the configurable product price.
     * Example: blue +5 kr, extra small -7 kr
     *
     * @param   $eid Entity ID
     * @param   $acode Attribute code
     * @param   $val The configurable product
     * @param   $vals Array to store "side effects into"
     * @param   $prod The product
     * @return  The actually rendered value
     */
    public function render($eid, $acode, $val, &$vals, $prod)
    {
        // load event configuration areas (One of these things, without it getFinalPrice will not apply the price rules)
        // http://stackoverflow.com/questions/8829391/cant-retrieve-discounted-product-price-in-custom-script
        // Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_FRONTEND, Mage_Core_Model_App_Area::PART_EVENTS);

        static $config = null;
        if ($config === null) {
            /** @var Mage_Tax_Helper_Data $taxHelper */
            $taxHelper = Mage::helper('tax');
            $config = array(
                'configSimplePrice' => (int )Mage::getStoreConfig("configurablecommon/settings/configs_simple_price"),
                'displayPriceIncludingTax' => (bool )$taxHelper->displayPriceIncludingTax(),
                'priceIncludesTax' => (bool )$taxHelper->priceIncludesTax(),
            );
        }

        if (!$prod->isConfigurable()) {
            $vals[$eid]["sku"] = $prod->getData("sku");
            $_finalPrice = $prod->getFinalPrice();
            return $this->_getProductPrice($prod, $_finalPrice, $config);
        }

        /** Load the simple product */
        /** @var Mage_Catalog_Model_Product $item */
        $item = Mage::getSingleton('catalog/product')
                ->reset()
                ->load($eid);
        $vals[$eid]["sku"] = $item->getData("sku");

        if ($config['configSimplePrice']) {
            // Just use the simple price instead of the configurable price + offsets
            // Just return the simple price
            return $this->_getProductPrice($item, $item->getFinalPrice(), $config);
        }

        // Set the product in registry, if needed, before calling getJsonConfig
        $oldProduct = Mage::registry('product');
        if (!$oldProduct) {
            Mage::register('product', $prod);
        } else if ($oldProduct->getId() != $prod->getId()) {
            Mage::unregister('product');
            Mage::register('product', $prod);
        }

        // This give an array with everything needed.
        $className = Mage::getConfig()->getBlockClassName('catalog/product_view_type_configurable');
        $block = new $className();

        $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
        if ($skipSaleableCheck == false) {
            Mage::helper('catalog/product')->setSkipSaleableCheck(true);
        }
        $superprices = $block->getJsonConfig(); // 2012-10-11 Peter
        if ($skipSaleableCheck == false) {
            Mage::helper('catalog/product')->setSkipSaleableCheck(false);
        }

        $superprices = Mage::helper('core')->jsonDecode($superprices);
        $attr = array();
        foreach ($superprices["attributes"] as $id => $row) {
            foreach ($row["options"] as $option_id => $option_row) {
                $attr[$row["code"]][$option_row["id"]] = $option_row["price"];
            }
        }

        $price = $superprices["basePrice"];
        // Loop trough all super attributes
        foreach ($attr as $name => $data) {
            // Get the value in the simple product, often a dropdown index value
            $id = $item->getData($name);

            // If we got a value...
            if ($id > 0 && isset($data[$id])) {
                // Get the price to add from the super attribute.
                $price += (float )$data[$id];
            }
        }
        // Calculate incl tax if needed.
        $taxConfig = $superprices['taxConfig'];
        if ($taxConfig['includeTax'] === false && $taxConfig['showIncludeTax'] === true) {
            $price = (1 + ((float)$taxConfig['currentTax']) / 100.0) * $price;
            $price = Mage::app()->getStore()->roundPrice($price);
        }

        // Restore the product in the registry
        if (!$oldProduct) {
            Mage::unregister('product');
        } else if ($oldProduct->getId() != $prod->getId()) {
            Mage::unregister('product');
            Mage::register('product', $oldProduct);
        }

        // Return the calculated offset that will be added to the price
        return $price;
    }

    /**
     * Return price with tax when needed
     *
     * @param $product
     * @param $_finalPrice
     * @param $config
     * @return float
     */
    protected function _getProductPrice($product, $_finalPrice, $config)
    {
        /** @var Mage_Tax_Helper_Data $taxHelper */
        $taxHelper = Mage::helper('tax');
        return $taxHelper->getPrice($product, $_finalPrice, $config['displayPriceIncludingTax'], null, null, null, null, $config['priceIncludesTax']);
    }
}
