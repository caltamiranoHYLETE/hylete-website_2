<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'login_user', 'varchar(255) NULL');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'login_user_auth', 'varchar(255) NULL');

$installer->endSetup();