<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/debug'), 'request_headers', 'text');
$installer->getConnection()->addColumn($installer->getTable('bakerloo_restful/debug'), 'response_headers', 'text');

$installer->endSetup();