<?php

$installer = $this;
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful_orders'), 'uses_default_customer', 'int(1) NOT NULL default 0');