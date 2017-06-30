<?php
/* ProductMatrix
 *
 * @category   Webshopapps
 * @package    Webshopapps_productmatrix
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Shippingoverride2_Model_Carrier_Source_Shipoptions {

public function toOptionArray()
    {
        return array(
	        array('value'=>'usetax', 'label'=>Mage::helper('shippingoverride2')->__("Use Tax Inclusive Prices")),
	        array('value'=>'usediscount', 'label'=>Mage::helper('shippingoverride2')->__("Use Discounted Prices")),
        	array('value'=>'usebase', 'label'=>Mage::helper('shippingoverride2')->__("Use Base Currency Prices")),        		
	        array('value'=>'subtotalpw', 'label'=>Mage::helper('shippingoverride2')->__("Filter on Subtotal Price & Weight")),
	        array('value'=>'pattern', 'label'=>Mage::helper('shippingoverride2')->__("Use Pattern Matching"))
        );
    }
}
