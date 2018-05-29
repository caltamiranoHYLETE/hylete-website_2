<?php
    /**
    * Magedelight
    * Copyright (C) 2015 Magedelight <info@magedelight.com>
    *
    * NOTICE OF LICENSE
    *
    * This program is free software: you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation, either version 3 of the License, or
    * (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
    *
    * @category MD
    * @package MD_Cybersource
    * @copyright Copyright (c) 2015 Mage Delight (http://www.magedelight.com/)
    * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
    * @author Magedelight <info@magedelight.com>
    */  
    $installer = $this;
    $installer->startSetup();   
    $installer->run("
        DROP TABLE IF EXISTS `{$installer->getTable("md_cybersource/cards")}`;
        CREATE TABLE `{$installer->getTable("md_cybersource/cards")}`(
        `card_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Card Id',
        `customer_id` int(10) unsigned default NULL,
        `subscription_id` varchar(30) DEFAULT NULL COMMENT 'Customer Subscription Id',
        `firstname` varchar(150) DEFAULT NULL COMMENT 'Card Customer First Name',
        `lastname` varchar(150) DEFAULT NULL COMMENT 'Card Customer Last Name',
        `postcode` varchar(50) DEFAULT NULL COMMENT 'Card Customer First Name',
        `country_id` varchar(10) DEFAULT NULL COMMENT 'Card Customer Country',
        `region_id` varchar(150) DEFAULT NULL COMMENT 'Card Customer Region',
        `city` varchar(150) DEFAULT NULL COMMENT 'Card Customer City',
        `company` varchar(255) DEFAULT NULL COMMENT 'Card Customer Company',
        `street` varchar(255) NULL COMMENT 'Card Customer street',
        `telephone` varchar(50) NULL COMMENT 'Card Customer telephone',
        `cc_exp_month` varchar(255) DEFAULT NULL COMMENT 'Cc Exp Month',
        `cc_last4` varchar(255) DEFAULT NULL COMMENT 'Cc Last4',
        `cc_type` varchar(255) DEFAULT NULL COMMENT 'Cc Type',
        `cc_exp_year` varchar(255) DEFAULT NULL COMMENT 'Cc Exp Year',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Created At',
        `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Updated At',
        PRIMARY KEY (`card_id`),
        KEY `FK_CUSTOMER_ID_CYBERSOURCE` (`customer_id`),
        CONSTRAINT `FK_CUSTOMER_ID_CYBERSOURCE` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE)ENGINE=InnoDB  DEFAULT CHARSET=utf8;        

        ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `md_cybersource_subscription_id` varchar(30) DEFAULT NULL COMMENT 'Cybersource customer payment subscription id';
        ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `md_cybersource_requestid` varchar(30) DEFAULT NULL COMMENT 'Cybersource Request ID';
        ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `md_cybersource_subscription_id` varchar(30) DEFAULT NULL COMMENT 'Cybersource customer payment subscription id';               
        ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `md_cybersource_requestid` varchar(30) DEFAULT NULL COMMENT 'Cybersource Request Id';        
        ");
    $quoteToken=$installer->getConnection()->tableColumnExists($installer->getTable('sales/quote_payment'),'cybersource_token','');
    if($quoteToken==false){
        $installer->run("   
            ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `cybersource_token` varchar(255) DEFAULT NULL COMMENT 'Cybersource Token';        
            ");
    }
    $orderToken=$installer->getConnection()->tableColumnExists($installer->getTable('sales/order_payment'),'cybersource_token','');
    if($orderToken==false){
        $installer->run("   
            ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `cybersource_token` varchar(255) DEFAULT NULL COMMENT 'Cybersource Token';        
            ");
    }
    $invoiceToken=$installer->getConnection()->tableColumnExists($installer->getTable('sales/invoice'),'cybersource_token','');
    if($invoiceToken==false){
        $installer->run("   
            ALTER TABLE `{$installer->getTable('sales/invoice')}` ADD `cybersource_token` varchar(255) DEFAULT NULL COMMENT 'Cybersource Token';        
            ");
    }
    $creditToken=$installer->getConnection()->tableColumnExists($installer->getTable('sales/creditmemo'),'cybersource_token','');
    if($creditToken==false){
        $installer->run("   
            ALTER TABLE `{$installer->getTable('sales/creditmemo')}` ADD `cybersource_token` varchar(255) DEFAULT NULL COMMENT 'Cybersource Token';        
            ");
    }
    $installer->endSetup();

