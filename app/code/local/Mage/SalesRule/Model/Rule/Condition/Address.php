<?php

/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 *
 * This is an override of Core's `Mage_SalesRule_Model_Rule_Condition_Address`
 */
class Mage_SalesRule_Model_Rule_Condition_Address extends Mage_Rule_Model_Condition_Abstract
{
	public function loadAttributeOptions()
	{
		$attributes = array(
			'base_subtotal' => Mage::helper('salesrule')->__('Subtotal'),
			'total_qty' => Mage::helper('salesrule')->__('Total Items Quantity'),
			'weight' => Mage::helper('salesrule')->__('Total Weight'),
			'payment_method' => Mage::helper('salesrule')->__('Payment Method'),
			'shipping_method' => Mage::helper('salesrule')->__('Shipping Method'),
			'postcode' => Mage::helper('salesrule')->__('Shipping Postcode'),
			'region' => Mage::helper('salesrule')->__('Shipping Region'),
			'region_id' => Mage::helper('salesrule')->__('Shipping State/Province'),
			'country_id' => Mage::helper('salesrule')->__('Shipping Country'),

			// MYLES: Introduce HYLETE's 'discounted_subtotal' condition
			'discounted_subtotal' => Mage::helper('salesrule')->__('Discounted Subtotal')
		);

		$this->setAttributeOption($attributes);

		return $this;
	}

	public function getAttributeElement()
	{
		$element = parent::getAttributeElement();
		$element->setShowAsText(true);
		return $element;
	}

	public function getInputType()
	{
		switch ($this->getAttribute()) {
			case 'base_subtotal':
			case 'weight':
			case 'total_qty':

				// MYLES: Specify the input type for HYLETE's 'discounted_subtotal' condition
			case 'discounted_subtotal':
				return 'numeric';

			case 'shipping_method':
			case 'payment_method':
			case 'country_id':
			case 'region_id':
				return 'select';
		}
		return 'string';
	}

	public function getValueElementType()
	{
		switch ($this->getAttribute()) {
			case 'shipping_method':
			case 'payment_method':
			case 'country_id':
			case 'region_id':
				return 'select';
		}
		return 'text';
	}

	public function getValueSelectOptions()
	{
		if (!$this->hasData('value_select_options')) {
			switch ($this->getAttribute()) {
				case 'country_id':
					$options = Mage::getModel('adminhtml/system_config_source_country')
						->toOptionArray();
					break;

				case 'region_id':
					$options = Mage::getModel('adminhtml/system_config_source_allregion')
						->toOptionArray();
					break;

				case 'shipping_method':
					$options = Mage::getModel('adminhtml/system_config_source_shipping_allmethods')
						->toOptionArray();
					break;

				case 'payment_method':
					$options = Mage::getModel('adminhtml/system_config_source_payment_allmethods')
						->toOptionArray();
					break;

				default:
					$options = array();
			}
			$this->setData('value_select_options', $options);
		}
		return $this->getData('value_select_options');
	}

	/**
	 * Validate Address Rule Condition
	 *
	 * @param Varien_Object $object
	 * @return bool
	 */
	public function validate(Varien_Object $object)
	{
		$address = $object;
		if (!$address instanceof Mage_Sales_Model_Quote_Address) {
			if ($object->getQuote()->isVirtual()) {
				$address = $object->getQuote()->getBillingAddress();
			} else {
				$address = $object->getQuote()->getShippingAddress();
			}
		}

		if ('payment_method' == $this->getAttribute() && !$address->hasPaymentMethod()) {
			$address->setPaymentMethod($object->getQuote()->getPayment()->getMethod());
		}

		// MYLES: Validate the 'discounted_subtotal' attribute
		if ($this->getAttribute() == 'discounted_subtotal') {
			$op = $this->getOperator();

			// MYLES: "Discounted subtotal" is the subtotal less any applied discounts (discount amount is negative!)
			$discountedSubtotal = $address->getSubtotal() + $address->getDiscountAmount();
			$value = $this->getValue();

			// MYLES: Doesn't support array operators, and probably doesn't need to support "=="
			switch ($op) {
				case "==":
					return $discountedSubtotal == $value;
					break;

				case ">=":
					return $discountedSubtotal >= $value;
					break;

				case "<=":
					return $discountedSubtotal <= $value;
					break;

				case ">":
					return $discountedSubtotal > $value;
					break;

				case "<":
					return $discountedSubtotal < $value;
					break;

				default:
					// MYLES: An inappropriate operator was given; log and return false
					Mage::log("Operator '" . $op . "' given for the discounted subtotal condition is not valid", null, "discounted_subtotal_operator_validation_fails.log");
					return false;
					break;
			}
		}

		return parent::validate($address);
	}
}
