<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();


$identifier = 'cart_promo_title';
$title = 'Cart promo title';
$contentCmsBlock = <<<EOT
<h2 class="promo-title">promo codes & Gift cards</h2>
EOT;
$cmsBlock = Mage::getModel('cms/block')->load($identifier);
$cmsBlock->setTitle($title)
    ->setContent($contentCmsBlock)
    ->setIdentifier($identifier)
    ->setStores(0)
    ->setIsActive(true);
$cmsBlock->save();
$installer->endSetup();
