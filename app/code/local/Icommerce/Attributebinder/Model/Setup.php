<?php

class Icommerce_Attributebinder_Model_Setup extends Mage_Core_Model_Resource_Setup {

    public function applyUpdates(){

        if( !Icommerce_Db::tableExists("icommerce_attributebinder") ){
            Icommerce_Db::write(  "CREATE TABLE icommerce_attributebinder (
              id int(11) unsigned NOT NULL AUTO_INCREMENT,
              main_attribute_id int(11) NOT NULL,
              main_attribute_label varchar(50) NOT NULL DEFAULT '',
              main_attribute_code varchar(50) NOT NULL DEFAULT '',
              bind_attribute_id int(11) NOT NULL,
              bind_attribute_label varchar(50) NOT NULL DEFAULT '',
              bind_attribute_code varchar(50) NOT NULL DEFAULT '',
              created_time datetime DEFAULT NULL,
              update_time datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            " );
        }

        if( !Icommerce_Db::tableExists("icommerce_attributebinder_bindings") ){
            Icommerce_Db::write( "CREATE TABLE icommerce_attributebinder_bindings (
              attributebinder_id int(11) NOT NULL,
              main_attribute_value varchar(255) DEFAULT NULL,
              bind_attribute_value varchar(255) NOT NULL DEFAULT ''
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!Icommerce_Db::columnExists("icommerce_attributebinder", "default_main_attribute") ){
            Icommerce_Db::write(  "ALTER TABLE `icommerce_attributebinder` ADD `default_main_attribute` INT  NOT NULL AFTER `update_time`;" );
        }

        if (!Icommerce_Db::columnExists("icommerce_attributebinder", "suppress_man_main_attr") ){
            Icommerce_Db::write(  "ALTER TABLE `icommerce_attributebinder` ADD `suppress_man_main_attr` INT  NOT NULL DEFAULT 0 AFTER `default_main_attribute`;" );
        }

        return parent::applyUpdates();
    }

}