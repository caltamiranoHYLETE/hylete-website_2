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

UPDATE icommerce_checklist SET name='Gå live' WHERE name='Go live';

UPDATE icommerce_checklist_item SET name='1. Katalog, Kategorivy' WHERE name='Katalog, Kategorivy';
UPDATE icommerce_checklist_item SET name='2. Katalog, Produktvy' WHERE name='Katalog, Produktvy';
UPDATE icommerce_checklist_item SET name='3. Försäljning, beställningar' WHERE name='Försälning, beställningar';
UPDATE icommerce_checklist_item SET name='5. Marknadsföring och nyhetsbrev' WHERE name='Marknadsföring och nyhetsbrev';
UPDATE icommerce_checklist_item_checkbox SET text='5.3 Kvalitetssäkra
* Har du genomfört köp med alla fraktsätt?
* Har du genomfört köp med alla betalningssätt?
* Har du ställt alla betalningssätt i live läge?
* Har du kontrollerat alla beställningar i Magento admin?
* Har du upprepat detta för alla eventuella multisiter?
* Har du kontrollerat att rätt information skickas till t.ex. Klarna/DIBS/Auriga/PayPal/E-conomic?' WHERE text='5.3 Kvalitetssäkra
* Har du genomfört köp med alla fraktsätt?
* Har du genomfört köp med alla betalningssätt?
* Har du ställt alla betalningssätt i live läge?
* Har du kontrollerat alla beställningar i Magento admin?
* Har du upprepat detta för alla enventuella multisiter?
* Har du kontrollerat att rätt information skickas till t.ex. Klarna/DIBS/Auriga/E-conomic?';


INSERT INTO {$this->getTable('icommerce_checklist')}(
`name` ,
`pm_email` ,
`customer_email` ,
`status` ,
`position` 
)
VALUES 
('Go Live', 'info@vaimo.com', 'unkown@domain.com', '1', '1');


INSERT INTO {$this->getTable('icommerce_checklist_item')}(
`project_id` ,
`name` ,
`status` ,
`position` 
)
VALUES 
('3', '1. Catalog & tax settings', '1', '0') , 
('3', '2. Home, CMS Pages & Links', '1', '1') , 
('3', '3. Transactional Emails', '1', '2') , 
('3', '4. Payment & Shipping', '1', '3') , 
('3', '5. Checkout', '1', '4') , 
('3', '6. Translation and Terminology', '1', '5') , 
('3', '7. Pointing of domain', '1', '6') ; 

INSERT INTO {$this->getTable('icommerce_checklist_item_checkbox')}(
`project_id` ,
`item_id` ,
`text` ,
`status` ,
`position` 
)
VALUES 
('3', '16', '1.1 Products
* Are all products added?
* Are all products activated?
* Do all products belong to at least one product category?
* Are the prices for products entered correctly?', '1', '0') ,
('3', '16', '1.2 Tax
* Are the prices consistent in excluding or including Tax', '1', '1') ,
('3', '17', '2.1 Homepage
* Does your homepage include a main heading (H1)?
* Does your H1 include three of your most important keywords?', '1', '0') ,
('3', '17', '2.2 CMS Pages
* Do all of your links display the right information?
* Check the text in your pages, such as: Legal & About Us', '1', '1') ,
('3', '17', '2.3 Links
* Ensure all other links in your store.', '1', '2') ,
('3', '17', '2.4 CMS blocks
* Do all of your blocks contain the content you want to display?', '1', '3') ,
('3', '18', '3.1 E-mail addresses for store
* Have you set your senders with email addresses as a customer will see when they receive automatic e-mail from the store?', '1', '0') ,
('3', '18', '3.2 Email Templates
* Do not change the logo!
* Use Upload Logo to change the logo.
* Have you tried to receive all e-mail templates in your inbox?
* Do all e-mail templates display the right content?', '1', '1') ,
('3', '18', '3.3 E-mail addresses for different forms
* Have you checked the following:
* System  ›› Configuration  ›› Sales Emails.
* System  ›› Configuration  ›› Email to a Friend.
* System  ›› Configuration  ›› Newsletter.
* System  ›› Configuration  ›› Customer Configuration.
* System  ›› Configuration  ›› Wishlist.
* System  ›› Configuration  ›› Contacts  ›› Email Options', '1', '2') ,
('3', '19', '4.1 Payment & Shipping
* Are all shipping and payment methods displayed at checkout?', '1', '0') ,
('3', '20', '5.1 Allowing guests
* Will you allow guests to use the checkout?
* If you have our Quick Checkout, all of your customers will get a customer account.', '1', '0') ,
('3', '20', '5.2 Gift Messaging
* Should it be possible to write a gift message at checkout?', '1', '1') ,
('3', '20', '5.3 Quality assurance
* Have you made purchases with all the shipping methods?
* Have you made purchases with any payment methods?
* Have you set all the payment methods in live mode?
* Have you checked all orders in Magento admin?
* Have you repeated this for all possible multi-sites?
* Have you checked that the correct information is sent to e.g. Klarna/DIBS/Auriga/PayPal/E-conomic?', '1', '2') ,
('3', '21', '6.1 Shopping Cart
* Does the cart display the correct amounts and discounts?
* Is tax correctly calculated for the allowed shipping countries and for the different tax classes?', '1', '0') ,
('3', '21', '6.2 Other translations
* Have you translated all non-English translations or strange terms?    ', '1', '1') ,
('3', '22', '7.1 Point to
* Are you ready to go live?
* Contact us before you point your domain.', '1', '0') ;

");

$installer->endSetup();
