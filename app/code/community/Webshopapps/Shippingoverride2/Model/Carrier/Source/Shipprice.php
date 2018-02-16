<?php
/**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     Webshopapps_override
 * User         karen
 * Date         27/10/2013
 * Time         11:19
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */

class Webshopapps_Shippingoverride2_Model_Carrier_Source_Shipprice {


    public function toOptionArray()
    {
        return array(
            array('value'=>'ignore',             'label'=>Mage::helper('shippingoverride2')->__("Ignore")),
            array('value'=>'replace_ship_price', 'label'=>Mage::helper('shippingoverride2')->__("Replace by Ship Price, ignores all CSV Rules")),
            array('value'=>'append_ship_price',  'label'=>Mage::helper('shippingoverride2')->__("Surcharge by Ship Price, ignores all CSV Rules")),
            array('value'=>'append_csv',         'label'=>Mage::helper('shippingoverride2')->__("Surcharge Price only when rule matched in CSV")),
            array('value'=>'discount_csv',       'label'=>Mage::helper('shippingoverride2')->__("Discount Price only when rule matched in CSV")),
        );
    }

}