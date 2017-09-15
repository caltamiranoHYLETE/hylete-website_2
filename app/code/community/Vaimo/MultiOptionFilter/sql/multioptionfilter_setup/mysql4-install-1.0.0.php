<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

$installer = $this;
$installer->startSetup();

$installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'excluded_attribute_filters', array(
    'type'              => 'text',
    'input'             => 'multiselect',
    'backend'           => 'eav/entity_attribute_backend_array',
    'source'            => 'multioptionfilter/attribute_options',
    'user_defined'      => true,
    'visible'           => true,
    'sort_order'        => 999,
    'required'          => false,
    'label'             => 'Exclude filtering options for attributes',
    'group'             => 'Display Settings',
));

$installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'suppress_filters', array(
    'type'              => 'int',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'user_defined'      => true,
    'visible'           => true,
    'sort_order'        => 999,
    'required'          => false,
    'label'             => 'Supress all filters',
    'group'             => 'Display Settings',
));

$installer->endSetup();