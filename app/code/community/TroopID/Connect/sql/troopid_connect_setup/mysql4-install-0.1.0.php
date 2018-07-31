<?php

$this->startSetup();

$this->getConnection()->addColumn($this->getTable("sales/order"), "troopid_affiliation", "varchar(255)");
$this->getConnection()->addColumn($this->getTable("sales/order"), "troopid_uid", "varchar(128)");

$this->getConnection()->addColumn($this->getTable("sales/quote"), "troopid_affiliation", "varchar(255)");
$this->getConnection()->addColumn($this->getTable("sales/quote"), "troopid_uid", "varchar(128)");

$this->endSetup();