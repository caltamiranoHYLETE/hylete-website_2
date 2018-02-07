<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->dropColumn($installer->getTable('mediotype_offerstab/offer'), 'created_time');

$installer->getConnection()->dropColumn($installer->getTable('mediotype_offerstab/offer'), 'update_time');

$installer->getConnection()->addColumn($this->getTable('mediotype_offerstab/offer'), 'created_at', 'datetime not null');

$installer->getConnection()->addColumn($this->getTable('mediotype_offerstab/offer'), 'updated_at', 'datetime not null');