<?php
// This installer scripts adds a product attribute "Is Blocked For Global-e".

// Set data:
$AttributeName  = 'Block For Global-e';    // Name of the attribute
$AttributeCode  = 'is_blocked_for_globale'; // Code of the attribute
$AttributeGroup = 'General';                 // Group to add the attribute to
$AttributeSetIds = array(Mage::getModel('catalog/product')->getDefaultAttributeSetId()); // Array with attribute set ID's to add this attribute to.

// Configuration:
$Data = array(
    'type'      => 'int',
    'input'     => 'boolean',
    'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,    // Attribute scope
    'required'  => false,
    'user_defined' => false,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'used_in_product_listing' => true,
    'label' => $AttributeName
);

// Create attribute
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();
$installer->addAttribute('catalog_product', $AttributeCode, $Data);

// Add the attribute to the proper sets/groups:
foreach($AttributeSetIds as $AttributeSetId)
{
    $installer->addAttributeToGroup('catalog_product', $AttributeSetId, $AttributeGroup, $AttributeCode);
}

$installer->endSetup();