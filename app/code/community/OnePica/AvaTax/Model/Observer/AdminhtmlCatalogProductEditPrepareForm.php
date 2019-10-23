<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2015 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Avatax Observer SalesConvertQuoteAddressToOrderAddress
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Model_Observer_AdminhtmlCatalogProductEditPrepareForm
    extends OnePica_AvaTax_Model_Observer_Abstract
{
    /**
     * Save quote address id to Mage_Sales_Model_Order_Address
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws \Varien_Exception
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var \Varien_Data_Form $form */
        $form = $observer->getForm();
        if (!$form) {
            return $this;
        }

        /** @var \Varien_Data_Form_Element_Collection $elements */
        $elements = $form->getElements();
        if (!$elements || !$elements->count()) {
            return $this;
        }

        switch ($elements[0]->getLegend()) {
            case OnePica_AvaTax_Helper_LandedCost::AVATAX_PRODUCT_GROUP_LANDED_COST:
                {
                    $this->_setRenderer($form, OnePica_AvaTax_Helper_LandedCost::AVATAX_PRODUCT_LANDED_COST_ATTR_PARAMETER);
                    $this->_setRenderer($form, OnePica_AvaTax_Helper_LandedCost::AVATAX_PRODUCT_LANDED_COST_ATTR_HSCODE);
                }
                break;
        }

        return $this;
    }

    /**
     * @param \Varien_Data_Form $form
     * @param  string           $attr
     * @return \OnePica_AvaTax_Model_Observer_AdminhtmlCatalogProductEditPrepareForm
     */
    protected function _setRenderer($form, $attr)
    {
        $element = $form->getElement($attr);

        switch ($attr) {
            case OnePica_AvaTax_Helper_LandedCost::AVATAX_PRODUCT_LANDED_COST_ATTR_PARAMETER:
                $renderer = Mage::app()->getLayout()->createBlock('avatax/adminhtml_catalog_product_edit_tab_landedCost_parameter');

                break;
            case OnePica_AvaTax_Helper_LandedCost::AVATAX_PRODUCT_LANDED_COST_ATTR_HSCODE:
                $renderer = Mage::app()->getLayout()->createBlock('avatax/adminhtml_catalog_product_edit_tab_landedCost_hsCode');

                break;
            default:
                $renderer = null;
        }

        if ($element && $renderer) {
            $element->setRenderer($renderer);
        }

        return $this;
    }
}
