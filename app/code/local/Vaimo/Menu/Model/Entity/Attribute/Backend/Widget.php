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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Menu_Model_Entity_Attribute_Backend_Widget extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    protected $_factory;

    public function __construct($parameters = array())
    {
        $this->_factory = isset($parameters['factory']) ?
            $parameters['factory'] : Mage::getModel('vaimo_menu/core_factory');
    }

    public function beforeSave($object)
    {
        $storeId = (int)$object->getStoreId();

        $attributeCode = $this->getAttribute()->getAttributeCode();
        if ($serializedFormData = $object->getData($attributeCode)) {
            if (!is_array($serializedFormData)) {
                $widgetData = array();
                parse_str($serializedFormData, $widgetData);
            } else {
                $widgetData = $serializedFormData;
            }

            $widgetInstance = $this->_factory->getModel('widget/widget_instance');
            $type = isset($widgetData['widget_type']) ? $widgetData['widget_type'] : '';

            if (isset($widgetData['instance_id'])) {
                $widgetInstance->load($widgetData['instance_id']);
                if (!$widgetInstance->getId() || ($widgetInstance->getType() != $type && $type)) {
                    $widgetInstance->setData(array());
                }

                if ($widgetInstance->hasData() && !in_array($storeId, $widgetInstance->getStoreIds())) {
                    $widgetInstance->setData(array());
                }
            }

            if (!$type) {
                if ($widgetInstance->hasData()) {
                    $widgetInstance->delete();
                }

                $object->setData($attributeCode, null);
                return parent::beforeSave($object);
            }

            if (isset($widgetData['parameters'])) {
                $parameters = $widgetData['parameters'];
                $widgetInstance->setWidgetParameters($parameters);
            }

            if (!$widgetInstance->getTitle()) {
                $widgetInstance->setTitle('Menu Widget For Category');
            }

            $categoryWidget = $this->_factory->getSingleton(
                'vaimo_menu/catalog_category_widget',
                array('factory' => $this->_factory)
            );

            $handles = $categoryWidget->getWidgetContainersForAttributeCode($attributeCode, $object->getStoreId());

            if (!$handles) {
                throw Mage::exception(
                    'Vaimo_Menu',
                    'Attribute has no layout reference links',
                    Vaimo_Menu_Exception::LAYOUT_REFERENCES_NOT_FOUND
                );
            }

            $pageGroups = array();
            foreach ($handles as $handle) {
                $pageGroups[] = array(
                    'page_group' => 'all_pages',
                    'all_pages' => array(
                        'page_id' => '0',
                        'layout_handle' => 'default',
                        'for' => 'all',
                        'block' => $handle,
                        'template' => isset($parameters) ? $parameters['template'] : ''
                    )
                );
            }

            /**
             * We're storing everything on base/default because database layout updates do not follow normal layout
             * update fallback.
             *
             * This will mean that widgets will not magically disappear when one changes the theme (or package).
             */
            $widgetInstance->setType($type)
                ->setPackageTheme(Mage::helper('vaimo_menu')->getBasePackageTheme())
                ->setStoreIds(array($object->getStoreId()))
                ->setPageGroups($pageGroups)
                ->save();

            $object->setData($attributeCode, $widgetInstance->getInstanceId());
        }

        return parent::beforeSave($object);
    }
}