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
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;

$catalogSetup = new Mage_Catalog_Model_Resource_Setup('core_setup');

$catalogSetup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'disable_link', array(
    'label'            => 'Disable link',
    'type'             => 'int',
    'input'            => 'select',
    'source'           => 'eav/entity_attribute_source_boolean',
    'required'         => false,
    'unique'           => false,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'position'         => 6,
    'group'            => 'Menu'
));

$installer->endSetup();