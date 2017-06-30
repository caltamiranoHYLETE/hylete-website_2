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

class Vaimo_Cms_Adminhtml_Vaimocms_WidgetController extends Vaimo_Cms_Controller_Adminhtml_Editor_Action
{
    public function editAction()
    {
        $factory = $this->getFactory();

        $widgetConfiguration = array();

        if ($configuration = $this->getRequest()->getParam('configuration')) {
            $widgetConfiguration = $factory->getHelper('vaimo_cms/widget')->parseWidgetParameters($configuration);
        }

        $widgetId = $this->getRequest()->getParam('id');

        $params = new Varien_Object($this->getRequest()->getParams());

        $this->getApp()->dispatchEvent('vaimo_cms_widget_open_edit_form_before', array(
            'params' => $params
        ));

        $pageId = $params->getData('page_id');

        if (!$widgetId && $pageId) {
            $page = $factory->getModel('vaimo_cms/widget_instance_page')->load($pageId);
            $widgetId = $page->getInstanceId();
        }

        if ($widgetId && !$widgetConfiguration) {
            $widgetInstance = $factory->getModel('vaimo_cms/widget_instance')->load($widgetId);

            $widgetConfiguration = array(
                'widget_type' => $widgetInstance->getInstanceType(),
                'parameters' => $widgetInstance->getWidgetParameters()
            );
        }

        if ($widgetConfiguration) {
            Mage::register('current_widget_configuration', $widgetConfiguration);
        }

        if ($dimensions = $this->getRequest()->getParam('dimensions')) {
            Mage::register('widget_editor_dimensions', explode('-', $dimensions));
        }

        $this->loadLayout();
        $this->renderLayout();
    }
}