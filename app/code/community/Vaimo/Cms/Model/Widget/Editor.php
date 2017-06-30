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

class Vaimo_Cms_Model_Widget_Editor extends Vaimo_Cms_Model_Editor_Abstract
{
    const UPDATE_ACTION = 'widget_save';

    protected $_actions = array(
        self::UPDATE_ACTION => 'widgetSave'
    );

    public function widgetSave($arguments)
    {
        if (!isset($arguments['widget_parameters'])) {
            return;
        }

        $factory = $this->getFactory();

        $parameters = $factory->getHelper('vaimo_cms/widget')->parseWidgetParameters($arguments['widget_parameters']);

        $parameters = array_merge($parameters, array(
            'page_id' => $arguments['widget_page_id'],
        ));

        if (isset($parameters['parameters'])) {
            $parameters['parameters']['_vcms_user_configured'] = 1;
        }

        $factory->getHelper('vaimo_cms/widget')->update($parameters);
    }

    public function widgetSaveResponse($arguments)
    {
        $factory = $this->getFactory();
        $app = $this->getApp();
        $update = $app->getLayout()->getUpdate();

        $widgetPage = $factory->getModel('vaimo_cms/widget_instance_page')
            ->load($arguments['widget_page_id']);

        $widgetsDataById = $factory->getHelper('vaimo_cms/layout')
            ->getWidgetLayoutUpdateDataGroupedByInstanceId($update);

        if (!$widgetPage->getInstanceId()) {
            return false;
        }

        $widgetByPageId = $widgetsDataById[$widgetPage->getInstanceId()];

        $allBlocks = $app->getLayout()->getAllBlocks();
        $widgetBlocks = array_intersect_key($allBlocks, $widgetByPageId);

        if ($widgetBlocks) {
            $items = array();

            foreach ($widgetBlocks as $name => $block) {
                if (isset($arguments['targeted_page_id'])) {
                    $pageId = $arguments['targeted_page_id'];
                } else {
                    $pageId = $widgetByPageId[$name]['page_id'];
                }

                $items[] = array(
                    'page_id' => $pageId,
                    'html' => $block->toHtml()
                );
            }

            return array('items' => $items);
        }

        return false;
    }
}