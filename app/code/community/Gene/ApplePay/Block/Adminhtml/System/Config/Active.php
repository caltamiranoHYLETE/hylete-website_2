<?php

/**
 * Class Gene_ApplePay_Block_Adminhtml_System_Config_Active
 *
 * @author Dave Macaulay <dave@gene.co.uk>
 */
class Gene_ApplePay_Block_Adminhtml_System_Config_Active extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Disable element if the dependencies don't exist for the extension
     *
     * @param \Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if (!Mage::helper('gene_applepay')->hasDependencies()) {
            $element->setDisabled(true)
                ->setValue(0)
                ->setComment(Mage::helper('gene_applepay')->__(
                    '<span style="font-weight: bold;color: darkred;">Gene_ApplePay requires Gene_Braintree version ' .
                    '2.2.0+, please install / upgrade Gene_Braintree to a supported version to use Apple Pay.</span>'
                ));
        }

        return parent::_getElementHtml($element);
    }
}
