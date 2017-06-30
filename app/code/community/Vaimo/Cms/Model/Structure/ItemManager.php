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

/**
 * Class Vaimo_Cms_Model_Structure_Editor
 *
 * @method object setCurrentControllerActionName(string $actionName)
 * @method string getCurrentControllerActionName()
 */
class Vaimo_Cms_Model_Structure_ItemManager extends Vaimo_Cms_Model_Editor_Abstract
{
    public function getWidgetParameters($item, $handle, $reference)
    {
        $factory = $this->getFactory();

        /* @var $widgetCreator Vaimo_Cms_Helper_Widget */
        $widgetCreator = $factory->getHelper('vaimo_cms/widget');

        /** @var Vaimo_Cms_Model_Structure_Widgets $structureWidgets */
        $structureWidgets = $factory->getSingleton('vaimo_cms/structure_widgets');

        $parameters = false;
        if (isset($item['widget_parameters'])) {
            $itemInfo = $widgetCreator->parseWidgetParameters($item['widget_parameters']);

            $widgetType = $itemInfo['widget_type'];
            $parameters = $itemInfo['parameters'];
        } else if (isset($item['widget_type'])) {
            $widgetType = $item['widget_type'];
        } else {
            return array();
        }

        if (!isset($item['clone_of']) && $parameters === false) {
            $parameters = $structureWidgets->generateParameters($widgetType, $handle, $reference);
        } else if ($parameters !== false && $parameters && !isset($item['widget_page_id'])) {
            if (isset($itemInfo['clone']) && $itemInfo['clone']) {
                $parameters = $structureWidgets->cloneParameters($widgetType, $parameters);
            } else {
                $parameters = $structureWidgets->createParameters($widgetType, $parameters);
            }
        }

        return array(
            'widget_type' => $widgetType,
            'parameters' => $parameters
        );
    }
}
