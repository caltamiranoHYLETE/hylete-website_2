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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Model_Adminhtml_Category_Editor extends Vaimo_Cms_Model_Abstract
{
    public function addEditFormUpdates($layout)
    {
        $factory = $this->getFactory();

        $codes = $factory->getHelper('vaimo_cms')->getCmsConfigurationFormAttributeCodes();

        $tabs = $layout->getBlock('tabs');
        $attributes = array_intersect_key($tabs->getCategory()->getAttributes(), array_flip($codes));

        $attributeGroup = new Varien_Object(array(
            'id' => 'cms',
            'attribute_group_name' => $factory->getHelper('vaimo_cms')->__('Content Configuration')
        ));

        $block = $layout->createBlock($tabs->getAttributeTabBlock())
            ->setGroup($attributeGroup)
            ->setAttributes($attributes)
            ->setAddHiddenFields(true);

        $tabIds = $tabs->getTabsIds();
        $tabs->addTabAfter('cms_configuration', array(
            'label'     => $factory->getHelper('vaimo_cms')->__('Content Configuration'),
            'content'   => $block->toHtml(),
            'active'    => false
        ), array_shift($tabIds));
    }
}