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

$catalogSetup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'text_placement', array(
    'label'            => 'Text placement',
    'note'             => 'The text is always centered in mobile view',
    'type'             => 'int',
    'input'            => 'select',
    'option'           => array ('value' => array(
                            'left' => array( 
                                0 =>'Left'),
                            'center' => array( 
                                0 =>'Center'),
                            'right' => array( 
                                0 => 'Right'))),
    'default'          => 'left',
    'required'         => false,
    'unique'           => false,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'position'         => 5,
    'group'            => 'Images and text'
));


$setId = Mage::getSingleton('eav/config')->getEntityType('catalog_category')->getDefaultAttributeSetId();
$attributes = array('image', 'image_tablet', 'image_mobile', 'image_thumbnail', 'description', 'text_placement', 'align_text', 'invert');

$placement = 0;
foreach ($attributes as $code) {
    $attribute = $installer->getAttribute('catalog_category', $code);
    if (isset($attribute['attribute_id'])) {
        $placement++;
        $installer->addAttributeToGroup('catalog_category', $setId, 'Images and text', $attribute['attribute_id'], $placement);
    }
}

$installer->endSetup();