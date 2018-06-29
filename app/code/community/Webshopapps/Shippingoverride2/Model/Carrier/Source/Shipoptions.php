<?php
/* ProductMatrix
 *
 * @category   Webshopapps
 * @package    Webshopapps_override
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Shippingoverride2_Model_Carrier_Source_Shipoptions {

    public function toOptionArray()
    {
        return array(
            array('value'=>'subtotalpw',    'label'=>Mage::helper('shippingoverride2')->__("Filter on Subtotal Price & Weight")),
        	array('value'=>'usebase',       'label'=>Mage::helper('shippingoverride2')->__("Use Base Currency Prices")),
            array('value'=>'usediscount',   'label'=>Mage::helper('shippingoverride2')->__("Use Discounted Prices")),
	        array('value'=>'pattern',       'label'=>Mage::helper('shippingoverride2')->__("Use Pattern Matching")),
            array('value'=>'use_sku',       'label'=>Mage::helper('shippingoverride2')->__("Use Custom Option SKU as Shipping Group")),
            array('value'=>'usetax',        'label'=>Mage::helper('shippingoverride2')->__("Use Tax Inclusive Prices")),
            array('value'=>'group_text',    'label'=>Mage::helper('shippingoverride2')->__("Use Text based Shipping Group")),
            array('value'=>'warehouse',     'label'=>Mage::helper('shippingoverride2')->__("Use with Dropship warehouse")),
            array('value'=>'NONE',          'label'=>Mage::helper('shippingoverride2')->__("N/A"))
        );
    }
}
