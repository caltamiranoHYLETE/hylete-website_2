<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table customer_group_log(customer_group_log_id int not null auto_increment, customer_id int not null, old_customer_group int not null, new_customer_group int not null, change_source varchar(255) null,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, primary key(customer_group_log_id));
		
SQLTEXT;

$installer->run($sql);
//demo
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 