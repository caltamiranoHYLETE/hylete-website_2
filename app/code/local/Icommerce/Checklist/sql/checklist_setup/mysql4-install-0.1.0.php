<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Checklist
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

$installer = $this;
$installer->startSetup();
$installer->run("

-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_checklist')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_checklist')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `urlkey` varchar(255) NOT NULL,
  `other_url` varchar(255) NOT NULL,
  `pm_email` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_checklist_item')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_checklist_item')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_checklist_item_checkbox')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_checklist_item_checkbox')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `checked` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('icommerce_checklist_item_checkbox_comment')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('icommerce_checklist_item_checkbox_comment')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_checkbox_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$installer->run("

INSERT INTO {$this->getTable('icommerce_checklist')}(
`name` ,
`urlkey` ,
`pm_email` ,
`customer_email` ,
`other_url` ,
`status` ,
`position` 
)
VALUES 
('Upgrade', 'upgrade', 'info@vaimo.com', 'unkown@domain.com', '', '1', '0'),
('Go live', 'live', 'info@vaimo.com', 'unkown@domain.com', '', '1', '1');


INSERT INTO {$this->getTable('icommerce_checklist_item')}(
`project_id` ,
`name` ,
`status` ,
`position` 
)
VALUES 
('1', 'Categories', '1', '0') ,
('1', 'Products', '1', '1') ,
('1', 'Sales & Orders', '1', '2') ,
('1', 'Customers', '1', '3') ,
('1', 'Marketing & newsletters', '1', '4') ,
('1', 'CMS', '1', '5') ,
('1', 'Reports', '1', '6') ,
('1', 'System', '1', '7') ,

('2', '1. Katalog & momsinställningar', '1', '0') , 
('2', '2. Startsida, CMS-sidor & länkar ', '1', '1') , 
('2', '3. E-postmallar och adresser', '1', '2') , 
('2', '4. Betalningssätt & frakt', '1', '3') , 
('2', '5. Kassan', '1', '4') , 
('2', '6. Översättning & terminologi', '1', '5') , 
('2', '7. Ompekning av domän', '1', '6') ; 


INSERT INTO {$this->getTable('icommerce_checklist_item_checkbox')}(
`project_id` ,
`item_id` ,
`text` ,
`status` ,
`position` 
)
VALUES 
('1', '1', 'Startsidan', '1', '0') , 
('1', '1', 'Menyn', '1', '1') , 
('1', '1', 'Lagerbaserad navigation', '1', '2') , 
('1', '1', 'Listläge', '1', '3') , 
('1', '1', 'Övriga anpassningar', '1', '4') , 
('1', '2', 'Bilder och miniatyrer', '1', '0') , 
('1', '2', 'Prisvisning', '1', '1') ,
('1', '2', 'Pris skillnader i konfigurerbara produkter', '1', '2') ,
('1', '2', 'Anpassade val', '1', '3') ,
('1', '2', 'Se till att produkterna finns kvar 24h efter testuppgradering', '1', '4') ,
('1', '2', 'Övriga anpassningar', '1', '5') ,
('1', '3', 'Lägg ordrar i kombination av alla betalnings- metoder och multisiter', '1', '0') ,
('1', '3', 'Lägg ordrar som inloggad kund och gäst', '1', '1') ,
('1', '3', 'Slutför ordrar', '1', '2') ,
('1', '3', 'Kreditera ordrar', '1', '3') ,
('1', '3', 'Makulera ordrar', '1', '4') ,
('1', '3', 'Skriv ut ordrar, fraktsedlar, fakturor och kreditnotor', '1', '5') ,
('1', '3', 'Övriga anpassningar', '1', '6') ,
('1', '4', 'Logga in som kund', '1', '0') ,
('1', '4', 'Kontrollera lagda ordrar under mitt konto', '1', '1') ,
('1', '4', 'Testa olika kundgrupper som t.ex. prisregler', '1', '2') ,
('1', '4', 'Editera kund i admin', '1', '3') ,
('1', '4', 'Övriga anpassningar', '1', '4') ,
('1', '5', 'Kontrollera aktiva prisregler', '1', '0') ,
('1', '5', 'Nyhetsbrev och kopplingar som t.ex. MailChimp', '1', '1') ,
('1', '6', 'Hantera sidor', '1', '0') ,
('1', '6', 'Hantera CMS block', '1', '1') ,
('1', '6', 'Hantera blogg och dess bilder', '1', '2') ,
('1', '6', 'Övriga anpassningar', '1', '3') ,
('1', '7', 'Kontrollera dina mest använda rapporter', '1', '0') ,
('1', '7', 'Övriga anpassningar', '1', '1') ,
('1', '8', 'E-postmallar', '1', '0') ,
('1', '8', 'Fraktalternativ', '1', '1') ,
('1', '8', 'Betalningsmetoder', '1', '2') ,
('1', '8', 'API-integrationer som t.ex. E-conomic och Visma', '1', '3') ,
('1', '8', 'Övriga anpassningar', '1', '4') ,

('2', '9', '1.1 Produkter', '1', '0') ,
('2', '9', '1.2 Moms', '1', '1') ,
('2', '10', '2.1 Startsida', '1', '0') ,
('2', '10', '2.2 CMS-sidor', '1', '1') ,
('2', '10', '2.3 Länkar på sidan', '1', '2') ,
('2', '10', '2.4 CMS-block', '1', '3') ,
('2', '11', '3.1 E-postadresser för butik', '1', '0') ,
('2', '11', '3.2 E-postmallar. OBS gör inga ändringar på logotyp.', '1', '1') ,
('2', '11', '3.3 E-postadresser för olika formulär', '1', '2') ,
('2', '12', '4.1 Betalningssätt', '1', '0') ,
('2', '13', '5.1 Ska du tillåta gäster att använda kassan?', '1', '0') ,
('2', '13', '5.2 Presentmeddelanden', '1', '1') ,
('2', '13', '5.3 Kvalitetssäkra', '1', '2') ,
('2', '14', '6.1 Varukorgen', '1', '0') ,
('2', '14', '6.2 Övriga översättningar', '1', '1') ,
('2', '15', '7.1 Peka om', '1', '0') ;

");

$installer->endSetup();