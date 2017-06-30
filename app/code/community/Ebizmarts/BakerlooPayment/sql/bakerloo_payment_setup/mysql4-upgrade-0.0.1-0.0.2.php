<?php

$this->startSetup();

$this->run("ALTER TABLE `{$this->getTable('customer_group')}` ADD COLUMN `bakerloo_payment_methods` TEXT NOT NULL DEFAULT '';");

$this->endSetup();