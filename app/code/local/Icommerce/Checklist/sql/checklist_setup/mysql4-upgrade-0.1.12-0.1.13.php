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

UPDATE icommerce_checklist_item SET name='1. Catalog and VAT settings' WHERE name='1. Catalog & tax settings';


UPDATE icommerce_checklist_item_checkbox SET text='1.2 VAT
* Did you apply excluding and including VAT prices consistently?' WHERE text='1.2 Tax
* Are the prices consistent in excluding or including Tax';

UPDATE icommerce_checklist_item_checkbox SET text='2.3 Links
* Ensure all other links in your store are working.' WHERE text='2.3 Links
* Ensure all other links in your store.';

UPDATE icommerce_checklist_item_checkbox SET text='3.1 Default store email address
* Did you test and check that the sending email address for all automatic e-mails sent from the store is correct?' WHERE text='3.1 E-mail addresses for store
* Have you set your senders with email addresses as a customer will see when they receive automatic e-mail from the store?';

UPDATE icommerce_checklist_item_checkbox SET text='5.3 Quality assurance
* Have you made purchases with all the shipping methods?
* Have you made purchases with any payment methods?
* Have you set all the payment methods in live mode?
* Have you checked all orders in Magento admin?
* Have you repeated this for all possible multi-sites?
* Have you checked that the correct information is sent to 3rd Party Service providers such as Klarna, Dibs?' WHERE text='5.3 Quality assurance
* Have you made purchases with all the shipping methods?
* Have you made purchases with any payment methods?
* Have you set all the payment methods in live mode?
* Have you checked all orders in Magento admin?
* Have you repeated this for all possible multi-sites?
* Have you checked that the correct information is sent to e.g. Klarna/DIBS/Auriga/PayPal/E-conomic?';

UPDATE icommerce_checklist_item_checkbox SET text='6.1 Shopping Cart
* Does the cart display the correct amounts and discounts?
* Is VAt correctly calculated for the allowed shipping countries and for the different VAT classes?' WHERE text='6.1 Shopping Cart
* Does the cart display the correct amounts and discounts?
* Is tax correctly calculated for the allowed shipping countries and for the different tax classes?';

");

$installer->endSetup();
