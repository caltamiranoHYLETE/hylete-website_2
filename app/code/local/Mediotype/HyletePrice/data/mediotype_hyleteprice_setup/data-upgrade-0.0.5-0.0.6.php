<?php

$customerGroup = Mage::getModel('customer/group')->load(0);
$customerGroup->setData('customer_group_hylete_price_label', 'Hylete');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(1);
$customerGroup->setData('customer_group_hylete_price_label', 'Hylete');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(5);
$customerGroup->setData('customer_group_hylete_price_label', 'Team');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(6);
$customerGroup->setData('customer_group_hylete_price_label', 'Team');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(8);
$customerGroup->setData('customer_group_hylete_price_label', 'Team');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(9);
$customerGroup->setData('customer_group_hylete_price_label', 'Team');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(10);
$customerGroup->setData('customer_group_hylete_price_label', 'Team');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(17);
$customerGroup->setData('customer_group_hylete_price_label', 'Team');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(37);
$customerGroup->setData('customer_group_hylete_price_label', 'Investor');
$customerGroup->save();
