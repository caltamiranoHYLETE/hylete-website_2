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

UPDATE icommerce_checklist_item_checkbox SET text='7.1 Peka om
* Är du redo att gå live?
* Om du har en tidigare live site, har du skapat 301:or?
* Om inte, ta kontakt med vår support.
* Kontakta oss innan du pekar om din domän.' WHERE text='7.1 Peka om
* Är du redo att gå live?
* Kontakta oss innan du pekar om din domän.';


UPDATE icommerce_checklist_item_checkbox SET text='7.1 Point to
* Are you ready to go live?
* If you have a previous live site, have you created 301 redirects?
* If not, please contact our support.
* Contact us before you point your domain.' WHERE text='7.1 Point to
* Are you ready to go live?
* Contact us before you point your domain.';

");

$installer->endSetup();
