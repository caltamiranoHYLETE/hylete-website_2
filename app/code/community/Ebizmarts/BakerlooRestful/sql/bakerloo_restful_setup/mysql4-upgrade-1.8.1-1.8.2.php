<?php

$installer = $this;

$inventoryTable = $installer->getTable('cataloginventory/stock_item');

$installer->getConnection()->addColumn($inventoryTable, 'updated_at', "datetime NOT NULL default '0000-00-00 00:00:00'");

$installer->run("
    DROP TABLE {$installer->getTable('bakerloo_restful/inventorydelta')};
");