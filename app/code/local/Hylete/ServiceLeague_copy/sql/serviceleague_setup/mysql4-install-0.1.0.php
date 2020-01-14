<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table hylete_govx_connector(result_id int not null auto_increment, result varchar(100), primary key(result_id));
    insert into hylete_govx_connector values(1,'tablename1');
    insert into hylete_govx_connector values(2,'tablename2');
		
SQLTEXT;

$installer->run($sql);
//demo
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 