<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table service_league_verifier(id int not null auto_increment, response varchar(1000),  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, primary key(id));
		
SQLTEXT;

$installer->run($sql);
//demo
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 