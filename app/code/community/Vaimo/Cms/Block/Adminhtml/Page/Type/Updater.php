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

class Vaimo_Cms_Block_Adminhtml_Page_Type_Updater extends Vaimo_Cms_Block_Js_Lib
{
    protected $_jsClassName = 'cmsPageTypeSelector';
    protected $_constructorParams = array(
        'source_element' => '',
        'target_element' => '',
        'tab_visibility_map' => ''
    );

    protected function _init()
    {
        $selectBlock = Mage::getBlockSingleton('vaimo_cms/adminhtml_page_type_selector');

        $targetSelector = '[name="general[' . Vaimo_Cms_Helper_Data::PAGE_TYPE_ATTRIBUTE_CODE . ']"]';

        $this->setConstructorParam('source_element', '#' . $selectBlock->getId());
        $this->setConstructorParam('target_element', $targetSelector);
        $this->setConstructorParam('tab_visibility_map', $this->_getTabVisibilityMap($this->getLayout()));
    }

    public function _getTabVisibilityMap($layout)
    {
        $factory = $this->getFactory();

        $pageType = $factory->getSingleton('vaimo_cms/page_type');
        $map = $pageType->getTabValueMap();
        $tabsBlock = $layout->getBlock('tabs');

        if (!$tabsBlock) {
            throw Mage::exception('Vaimo_Cms', 'Tabs block not found from layout');
        }

        $categoryAttributeSetId = $factory->getModel('catalog/category')->getDefaultAttributeSetId();

        $attributeSetGroupsCollection = $factory->getModel('eav/entity_attribute_group')->getCollection()
            ->setAttributeSetFilter($categoryAttributeSetId);

        $attributeGroupCodesByName = array();
        foreach ($attributeSetGroupsCollection as $group) {
            $attributeGroupCodesByName[$group->getAttributeGroupName()] = $group->getAttributeGroupId();
        }

        $tabId = $tabsBlock->getId();
        foreach ($map as &$names) {
            foreach ($names as &$name) {
                if (isset($attributeGroupCodesByName[$name])) {
                    $name = $tabId . '_group_' . $attributeGroupCodesByName[$name];
                } elseif (substr($name, 0, 1) == '_') {
                    $name = $tabId . $name;
                } else {
                    $name = false;
                }

                unset($name);
            }

            $names = array_values(array_filter($names));
            unset($names);
        }

        return $map;
    }
}