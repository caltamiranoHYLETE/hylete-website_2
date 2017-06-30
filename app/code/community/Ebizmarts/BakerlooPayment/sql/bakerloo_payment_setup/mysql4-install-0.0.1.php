<?php

$installer = $this;
$installer->startSetup();

$attributeCode = 'bakerloo_payment_methods';

$installer->addAttribute('customer', $attributeCode, array(
    'type'            => 'text',
    'label'           => 'POS Payment Methods',
    'note'            => 'All payment methods are enabled by default or if none is selected.',
    'input'           => 'multiselect',
    'source'          => 'bakerloo_payment/source_paymentmethods',
    'visible'         => true,
    'required'        => false,
    'is_user_defined' => 1,
    //'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    //'backend'       => 'catalog/category_attribute_backend_sortby',
    //'input_renderer'=> 'adminhtml/catalog_category_helper_sortby_available',
));

$installer->endSetup();

//Make it visible on the customer edit form in backend
$adminForm = Mage::getSingleton('eav/config')
        ->getAttribute('customer', $attributeCode);

$adminForm->setData('used_in_forms', array('adminhtml_customer'));
$adminForm->setData('sort_order', 1969);

$adminForm->save();