<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Hylete
 * @file        upgrade-0.1.5-0.1.6.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;

$installer->startSetup();

$table = $installer->getTable('icommerce_slideshow_item');

$installer->getConnection()->addColumn($table, 'text_placement', 'int DEFAULT ' . Vaimo_Hylete_Helper_Slideshow::TEXT_PLACEMENT_LEFT);
$installer->getConnection()->addColumn($table, 'align_text', 'int DEFAULT ' . Vaimo_Hylete_Helper_Slideshow::ALIGN_TEXT_LEFT);
$installer->getConnection()->addColumn($table, 'invert', 'int DEFAULT 0');

$installer->endSetup();