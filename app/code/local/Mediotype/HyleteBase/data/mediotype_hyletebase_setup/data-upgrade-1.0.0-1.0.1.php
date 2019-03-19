<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All rights reserved.
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** Update Satisfaction bar */
$block = Mage::getModel('cms/block')
    ->getCollection()
    ->addFieldToFilter('identifier', 'blue_bar_satisfaction_guaranteed')
    ->getFirstItem();
if ($block) {
    $block->setData('content', '
        <div id="top-announcement" class="owl-carousel owl-theme">
            <div class="owl-item ">
                100% fit guarantee + free U.S. return shipping
            </div>
        </div>')
        ->save();
}

$installer->endSetup();
