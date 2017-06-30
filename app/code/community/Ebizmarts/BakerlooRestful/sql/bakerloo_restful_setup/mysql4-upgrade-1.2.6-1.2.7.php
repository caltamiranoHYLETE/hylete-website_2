<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'json_payload_enc', 'TEXT null');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'json_request_headers_enc', 'TEXT null');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'customer_signature', 'TEXT null');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/order'), 'customer_signature_enc', 'TEXT null');

$installer->endSetup();