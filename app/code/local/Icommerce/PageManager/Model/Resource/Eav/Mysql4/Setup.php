<?php
class Icommerce_PageManager_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
   
    public function applyUpdates(){
        if( !Icommerce_Eav::getAttributeId("pagemanager_page") ){
            Icommerce_Eav::createAttribute( "pagemanager_page", "catalog/product", "Pagemanager Page",
                                            array( "label" => "PageManager Page",
                                                'source' => 'pagemanager/product_attribute_source_unit',
                                                'group'             => 'Design',
                                                'type'              => 'int',
                                                'input'             => 'select',
                                                'default'           => '0',
                                                'visible_on_front'  => true,
                                                'visible'           => true,
                                                'user_defined'      => true,
                                            ));
        }
    }

        
        /*return array(
            'catalog_product' => array(
                'entity_model'      => 'catalog/product',
                'attribute_model'   => 'catalog/resource_eav_attribute',
                'table'             => 'catalog/product',
                'additional_attribute_table' => 'catalog/eav_attribute', //1.4 support
                'entity_attribute_collection' => 'catalog/product_attribute_collection', //1.4 support
                'attributes'        => array(
                    'pagemanager_page' => array(
                        'group'             => 'Design',
                        'label'             => 'PageManager Page',
                        'type'              => 'int',
                        'input'             => 'select',
                        'default'           => '0',
                        'class'             => '',
                        'backend'           => '',
                        'frontend'          => '',
                        'source'            => 'pagemanager/product_attribute_source_unit',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible'           => true,
                        'required'          => false,
                        'user_defined'      => true,
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => true,
                        'visible_in_advanced_search' => false,
                        'unique'            => false
                    ),
               )
            )
        );*/
  
}