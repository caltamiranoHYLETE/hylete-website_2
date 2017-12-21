<?php

$defaultHyletePriceLabelContent = 'Hylete Price';

$block = Mage::getModel('cms/block');
$block->setTitle('Hylete Price Difference Verbiage (Default)');
$block->setIdentifier('hylete_price_difference_verbiage_default');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($defaultHyletePriceLabelContent);
$block->save();

$investorHyletePriceLabelContent = 'Investor Price';
$block = Mage::getModel('cms/block');
$block->setTitle('Hylete Price Difference Verbiage (Investor)');
$block->setIdentifier('hylete_price_difference_verbiage_investor');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($investorHyletePriceLabelContent);
$block->save();

$otherHyletePriceLabelContent = 'Other Price';
$block = Mage::getModel('cms/block');
$block->setTitle('Hylete Price Difference Verbiage (Other)');
$block->setIdentifier('hylete_price_difference_verbiage_other');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($otherHyletePriceLabelContent);
$block->save();
