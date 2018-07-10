<?php

$installer = $this;

$installer->startSetup();




if  (Mage::helper('wsacommon')->getNewVersion() == 1.6) {
    $installer->run("

    select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

    insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'ship_price_res',
    	backend_type	= 'decimal',
    	frontend_input	= 'price',
    	is_required	= 0,
    	is_user_defined	= 1,
    	used_in_product_listing = 0,
    	is_filterable_in_search	= 0,
    	frontend_label	= 'Shipping Price Residential';


    insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'ship_price_com',
    	backend_type	= 'decimal',
    	frontend_input	= 'price',
    	is_required	= 0,
    	is_user_defined	= 1,
    	used_in_product_listing = 0,
    	is_filterable_in_search	= 0,
    	frontend_label	= 'Shipping Price Commercial';

");

}
else{

    $installer->run("

    select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

    insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'ship_price_res',
    	backend_type	= 'decimal',
    	frontend_input	= 'price',
    	is_required	= 0,
    	is_user_defined	= 1,
    	frontend_label	= 'Shipping Price Residential';

    select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_price_res';

    insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id = @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 0,
    	is_filterable_in_search	= 0;


    insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'ship_price_com',
    	backend_type	= 'decimal',
    	frontend_input	= 'price',
    	is_required	= 0,
    	is_user_defined	= 1,
    	frontend_label	= 'Shipping Price Commercial';

    select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_price_com';

    insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id = @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 0,
    	is_filterable_in_search	= 0;

");



}

    

$installer->endSetup();