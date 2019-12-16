<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Block_TaxServices_Adminhtml_Tax_Class_Grid extends Mage_Adminhtml_Block_Tax_Class_Grid
{
    /**
     * Adds the cybersource tax code field to the adminhtml grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter(
            'cs_tax_code',
            array(
                'header' => Mage::helper('cybersource_taxservices')->__('CyberSource Tax Code'),
                'align' => 'left',
                'index' => 'cs_tax_code',
                'after' => 'class_name',
                'width' => '250px'
            ),
            'class_name'
        );
        return parent::_prepareColumns();
    }
}
