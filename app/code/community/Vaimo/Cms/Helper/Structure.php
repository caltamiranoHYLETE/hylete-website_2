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

class Vaimo_Cms_Helper_Structure extends Vaimo_Cms_Helper_Abstract
{
    const NAME_PREFIX = 'vcms.structure.';
    const BLOCK_TYPE = 'vaimo_cms/structure';

    public function createStructureBlock($structure, $layout)
    {
        $factory = $this->getFactory();

        $layoutHelper = $factory->getHelper('vaimo_cms/layout');

        $container = $layout->getBlock($structure->getBlockReference());

        if (!$container) {
            return;
        }

        $block = $layoutHelper->createUniqueBlock(
            $container,
            self::BLOCK_TYPE,
            self::NAME_PREFIX . $structure->getId(),
            self::NAME_PREFIX . $structure->getBlockReference(),
            array(
                'structure_id' => $structure->getId()
            )
        );

        if (!$block) {
            return;
        }

        $structureDataForLayout = $structure->getStructureDataForLayout($layout);

        $block->prepareGrid($structureDataForLayout);
    }

    public function getHandleForAction($fullActionName)
    {
        switch ($fullActionName) {
            case Vaimo_Cms_Helper_Data::CATEGORY_VIEW_CONTROLLER_ACTION:
            case Vaimo_Cms_Helper_Data::CMS_HOME_PAGE_CONTROLLER_ACTION:
                $prefix = 'CATEGORY_';
                break;
            case Vaimo_Cms_Helper_Data::CMS_PAGE_CONTROLLER_ACTION:
                $prefix = 'CMSPAGE_';
                break;
            case Vaimo_Cms_Helper_Data::PRODUCT_VIEW_CONTROLLER_ACTION:
                $prefix = 'PRODUCT_';
                break;
            default:
                $prefix = $fullActionName;
                break;
        }

        $transport = new Varien_Object(array(
            'full_action_name' => $fullActionName,
            'prefix' => $prefix
        ));

        Mage::dispatchEvent('vaimo_cms_get_layout_handle_prefix_for_action_after', array(
            'transport' => $transport
        ));

        return $transport->getPrefix();
    }

    public function getCurrentLayoutHandle($fullActionName)
    {
        $update = $this->getApp()->getLayout()->getUpdate();
        $handles = $update->getHandles();

        $match = $this->getHandleForAction($fullActionName);

        $structureHandle = false;
        foreach ($handles as $handle) {
            if ($match == $fullActionName && $handle == $fullActionName) {
                $structureHandle = $handle;
                break;
            }

            if (substr($handle, 0, strlen($match)) == $match) {
                if (is_numeric(str_replace($match, '', $handle))) {
                    $structureHandle = $handle;
                    break;
                }
            }
        }

        return $structureHandle;
    }

    public function getClone(Vaimo_Cms_Model_Structure $structure)
    {
        $structureData = $structure->getStructureData();

        $factory = $this->getFactory();
        $widgetHelper = $factory->getHelper('vaimo_cms/widget');

        $clone = $factory->getModel('vaimo_cms/structure')
            ->setData($structure->getData())
            ->setOrigData('structure', $structure->getOrigData('structure'))
            ->unsStructureId();

        $parametersForPageIds = $structure->getParametersForStructureWidgets();

        foreach($structureData as $index => &$item) {
            if (!isset($item['widget_page_id'])) {
                continue;
            }

            $pageId = $item['widget_page_id'];
            unset($item['widget_page_id']);

            $item['clone_of'] = $pageId;

            if (!isset($parametersForPageIds[$pageId])) {
                continue;
            }

            if (!isset($item['widget_parameters'])) {
                $parameters = $parametersForPageIds[$pageId];

                $item['widget_parameters'] = array(
                    'widget_type' => $parameters['type'],
                    'parameters' => $parameters['parameters'],
                );
            } else {
                $item['widget_parameters'] = $widgetHelper->parseWidgetParameters($item['widget_parameters']);
            }

            $item['widget_parameters']['clone'] = true;
        }

        $clone->setStructureData($structureData);

        return $clone;
    }

    public function copyStructureData($from, $to)
    {
        $structureData = $from->getStructureData();

        foreach ($structureData as &$item) {
            $widget = $to->getItem($item['widget_page_id'], 'clone_of');

            if (!$widget) {
                continue;
            }

            $item['widget_page_id'] = $widget['widget_page_id'];
            $item['clone_of'] = $widget['clone_of'];
        }

        return $to->setStructureData($structureData);
    }

    public function getStructureWidgetStoreView($structureId, $widgetPageId, $storeId)
    {
        $structure = $this->getFactory()->getHelper('vaimo_cms/page')
            ->getStructureStoreView($structureId, $storeId);

        $widget = $structure->findItem($widgetPageId, array('widget_page_id', 'clone_of'));

        $widget = array_merge($widget, array(
            'widget_parameters' => $structure->getParametersForItem($widget['widget_page_id'])
        ));

        return $widget;
    }

    public function getStructureOutputFromLayout($structures, $layout)
    {
        $items = array();

        foreach ($structures as $structure) {
            $reference = $structure->getBlockReference();

            $block = $layout->getBlock($reference);

            $items[] = array(
                'reference' => $reference,
                'html' => $block ? $block->toHtml() : '',
                'id' => $structure->getId(),
                'items' => $structure->getStructureData()
            );
        }

        return $items;
    }

    public function validate($structure)
    {
        foreach ($structure->getStructureData() as $item) {
            if (!isset($item['widget_page_id'])) {
                return $this->__('Saving structure without widget_page_id reference');
            }
        }

        return true;
    }
}