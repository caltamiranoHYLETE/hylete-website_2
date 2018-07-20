<?php

$this->startSetup();

$this->getConnection()->addColumn($this->getTable("sales/order"), "troopid_scope", "varchar(255)");
$this->getConnection()->addColumn($this->getTable("sales/quote"), "troopid_scope", "varchar(255)");

$this->endSetup();