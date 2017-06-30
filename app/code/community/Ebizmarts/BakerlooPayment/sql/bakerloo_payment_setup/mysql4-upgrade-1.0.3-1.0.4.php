<?php

$this->startSetup();

$this->run("ALTER TABLE `{$this->getTable('sales_flat_quote_payment')}` CHANGE `pos_sagepay_info` `pos_payment_info` TEXT;");
$this->run("ALTER TABLE `{$this->getTable('sales_flat_order_payment')}` CHANGE `pos_sagepay_info` `pos_payment_info` TEXT;");

$this->endSetup();