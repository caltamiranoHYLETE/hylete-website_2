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
 * @file        upgrade-0.1.18-0.1.19.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;

$catalogSetup = new Mage_Catalog_Model_Resource_Setup('core_setup');

$catalogSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'size_guide', array(
    'label'            => 'Size Guide',
    'type'             => 'int',
    'input'            => 'select',
    'required'         => false,
    'unique'           => false,
    'source'           => 'hylete/attribute_source_size_guide',
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'group'            => 'General'
));

$installer->endSetup();