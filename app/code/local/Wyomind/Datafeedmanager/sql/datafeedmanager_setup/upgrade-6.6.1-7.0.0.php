<?php
$installer = $this;

$installer->startSetup();



$installer->run(
    "ALTER TABLE {$this->getTable('datafeedmanager_configurations')} "
    . "MODIFY `datafeedmanager_attribute_sets` varchar(1500) default '*';"
);

$installer->endSetup();