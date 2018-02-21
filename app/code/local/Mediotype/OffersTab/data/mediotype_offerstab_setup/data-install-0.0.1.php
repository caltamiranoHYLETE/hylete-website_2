<?php

/**
 * Provides 5 example CMS blocks for loading into the "Offers Tab"
 */

$defaultOfferContent = '
	<h4>Example Offer</h4>
	<img src="{{skin url=\'images/mediotype/hylete/offerstab/offer-placeholder-img.png\'}}?>" alt=""/>
';

$block = Mage::getModel('cms/block');
$block->setTitle('Offer Example 1');
$block->setIdentifier('offer_example_1');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($defaultOfferContent);
$block->save();

$block = Mage::getModel('cms/block');
$block->setTitle('Offer Example 2');
$block->setIdentifier('offer_example_2');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($defaultOfferContent);
$block->save();

$block = Mage::getModel('cms/block');
$block->setTitle('Offer Example 3');
$block->setIdentifier('offer_example_3');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($defaultOfferContent);
$block->save();

$block = Mage::getModel('cms/block');
$block->setTitle('Offer Example 4');
$block->setIdentifier('offer_example_4');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($defaultOfferContent);
$block->save();

$block = Mage::getModel('cms/block');
$block->setTitle('Offer Example 5');
$block->setIdentifier('offer_example_5');
$block->setStores(array(0));
$block->setIsActive(1);
$block->setContent($defaultOfferContent);
$block->save();
