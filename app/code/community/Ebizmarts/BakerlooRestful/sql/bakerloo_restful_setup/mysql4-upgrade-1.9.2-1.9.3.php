<?php

$installer = $this;

$installer->startSetUp();

$installer->run("
ALTER TABLE {$this->getTable('bakerloo_restful/debug')} MODIFY COLUMN request_body LONGTEXT
");

$installer->run("
ALTER TABLE {$this->getTable('bakerloo_restful/debug')} MODIFY COLUMN response_body LONGTEXT
");

$installer->endSetUp();