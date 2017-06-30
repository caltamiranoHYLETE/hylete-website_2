<?php

$installer = $this;

$installer->run("
    UPDATE {$installer->getTable('cataloginventory/stock_item')} SET updated_at = '0001-01-01 00:00:00' WHERE updated_at='0000-00-00 00:00:00';
");