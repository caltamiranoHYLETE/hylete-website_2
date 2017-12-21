<?php

$customerGroup = Mage::getModel('customer/group')->load(0);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_default');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(1);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_default');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(5);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_other');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(6);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_other');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(8);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_other');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(9);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_other');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(10);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_other');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(17);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_other');
$customerGroup->save();

$customerGroup = Mage::getModel('customer/group')->load(37);
$customerGroup->setData('hylete_price_cms_block_identifier', 'hylete_price_difference_verbiage_investor');
$customerGroup->save();
