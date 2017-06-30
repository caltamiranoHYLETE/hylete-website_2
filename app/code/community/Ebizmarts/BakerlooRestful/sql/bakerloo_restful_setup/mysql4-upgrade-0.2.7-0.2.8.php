<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'json_payload', 'TEXT null');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'request_url', 'varchar(255) null');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'json_request_headers', 'TEXT null');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'fail_message', 'TEXT null');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'updated_at', 'datetime NOT NULL default \'0000-00-00 00:00:00\'');

$installer->endSetup();