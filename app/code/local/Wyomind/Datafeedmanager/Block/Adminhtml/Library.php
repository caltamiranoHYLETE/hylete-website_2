<?php
/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
class Wyomind_Datafeedmanager_Block_Adminhtml_Library extends Mage_Adminhtml_Block_Template
{
    public function _ToHtml()
    {
        $attributeList = Mage::helper('datafeedmanager')->getOrderedAttributeList();
        
        $tabOutput = '<div id="dfm-library"><ul><h3>Attribute groups</h3> ';
        $contentOutput = '<table >';
        $tabOutput .= " <li><a href='#attributes'>Base Attributes</a></li>";
        $contentOutput .= "<tr><td><a name='attributes'></a><b>Base Attributes</b></td></tr>";
        
        foreach ($attributeList as $attribute) {
            if (!empty($attribute['frontend_label'])) {
                $contentOutput.= "<tr><td>" . $attribute['frontend_label'] . "</td>"
                        . "<td><span class='pink'>{" . $attribute['attribute_code'] . "}</span></td></tr>";
            }
        }

        foreach ($attributeList as $attribute) {
            if (!empty($attribute['attribute_code']) && empty($attribute['frontend_label'])) {
                $contentOutput.= "<tr><td>" . $attribute['frontend_label'] . "</td>"
                        . "<td><span class='pink'>{" . $attribute['attribute_code'] . "}</span></td></tr>";
            }
        }

        $class = new Wyomind_Datafeedmanager_Model_Configurations;
        $myCustomAttributes = new Wyomind_Datafeedmanager_Model_MyCustomAttributes;
        
        foreach ($myCustomAttributes->_getAll() as $group => $attributes) {
            $tabOutput .=" <li><a href='#" . $group . "'> " . $group . "</a></li>";
            $contentOutput .="<tr><td><a name='" . $group . "'></a><b>" . $group . "</b></td></tr>";
            foreach ($attributes as $attr) {
                $contentOutput.= "<tr><td><span class='pink'>{" . $attr . "}</span></td></tr>";
            }
        }

        $tabOutput .= " <li><a target='_blank' class='external_link' "
                . "href='http://wyomind.com/data-feed-manager-magento.html?src=dfm-library&directlink=documentation#Special_attributes'>Special Attributes</a></li>";
        $contentOutput .= '</table></div>';
        $tabOutput .= '</ul>';
        
        return $tabOutput . $contentOutput;
    }
}