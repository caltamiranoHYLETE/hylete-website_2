<?php

$this->startSetup();

$this->getConnection()->addColumn($this->getTable("sales/order_grid"), "troopid_affiliation",   "varchar(255)");
$this->getConnection()->addColumn($this->getTable("sales/order_grid"), "troopid_uid",           "varchar(255)");
$this->getConnection()->addColumn($this->getTable("sales/order_grid"), "troopid_scope",         "varchar(255)");

$this->run("UPDATE sales_flat_order_grid a, sales_flat_order b SET a.troopid_affiliation = b.troopid_affiliation, a.troopid_uid = b.troopid_uid, a.troopid_scope = b.troopid_scope WHERE a.entity_id = b.entity_id");

$this->endSetup();