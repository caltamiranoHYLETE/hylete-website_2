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

class Vaimo_Cms_Block_Structure_Editor extends Vaimo_Cms_Block_Content_Editor_Abstract
{
    protected $_jsClassName = 'cmsStructureEditor';

    protected $_constructorParams = array(
        'updateUri'             => '',
        'overlayManager'        => '',
        'widgetEditor'          => '',
        'loaderIndicator'       => '',
        'selectors'         => array(
            'container' => 'body',
            'itemTemplate' => '.vcms-structure-item-content-template',
            'editStructureButton' => '.vcms-structure-edit',
            'toolbarButtonGroup' => array(
                'container' => '#js-vcms-structure-button-group',
                'save' => '#js-vcms-save-structure',
                'cancel' => '#js-vcms-cancel-structure',
                'addWidget' => '#js-vcms-add-widget',
                'addCmsBlock' => '#js-vcms-add-cms-block',
            ),
            'gridster' => array(
                'widget' => '.vcms-gridster-widget',
                'remove' => '.vcms-structure-widget-remove',
                'configure' => '.vcms-structure-widget-configure'
            )
        ),
        'classNames' => array(
            'gridster' => array(
                'container' => 'gridster vcms-structure-overlay',
                'widget' => 'vcms-gridster-widget',
                'actionContainer' => 'vcms-actions',
                'configure' => 'vcms-structure-widget-configure',
                'remove' => 'vcms-structure-widget-remove'
            )
        ),
        'layoutHandle' => '',
        'structureDefinitions' => array(),
        'widgetTypes' => array(),
        'cmsBlockWidgetType' => Vaimo_Cms_Model_Structure::CMS_BLOCK_WIDGET_TYPE
    );

    protected function _construct()
    {
        $this->setConstructorParam('updateUri', $this->_getUrl(Vaimo_Cms_Model_Structure_Editor::UPDATE_ACTION));

        $this->_populateStructureDefinitions();
        $this->_populateWidgetTypes();
    }

    protected function _populateStructureDefinitions()
    {
        $factory = $this->getFactory();

        $structureEditor = $factory->getSingleton('vaimo_cms/structure_editor');

        $this->setConstructorParam('layoutHandle', $structureEditor->getCurrentLayoutHandle());

        $structureInstances = array();
        foreach ($structureEditor->getCurrentStructureDefinitions() as $item) {
            $structureInstances[] = array(
                'id' => $item->getId(),
                'reference' => $item->getBlockReference(),
                'items' => $item->getStructureData()
            );
        }

        $this->setConstructorParam('structureDefinitions', $structureInstances);
    }

    protected function _populateWidgetTypes()
    {
        $factory = $this->getFactory();

        $widgetTypes = array();
        foreach ($factory->getModel('widget/widget')->getWidgetsArray() as $item) {
            $type = strtolower($item['type']);
            unset($item['code']);
            $widgetTypes[$type] = $item;
        }

        $vcmsOptions = array(
            'cms/widget_block' => array('role' => 'cms', 'default_size' => array(4, 2))
        );

        $widgetTypes = array_merge_recursive($widgetTypes, $vcmsOptions);

        $this->setConstructorParam('widgetTypes', $widgetTypes);
    }

    protected function _toHtml()
    {
        $factory = $this->getFactory();

        $params = $this->getConstructorParameters();

        $update = $this->getLayout()->getUpdate();

        $widgetDataByPageId = $factory->getHelper('vaimo_cms/layout')
            ->getWidgetLayoutUpdateDataGroupedByPageId($update);

        foreach ($params['structureDefinitions'] as &$structureInstance) {
            foreach ($structureInstance['items'] as &$item) {
                $pageId = $item['widget_page_id'];

                if (!isset($widgetDataByPageId[$pageId])){
                    continue;
                }

                $item['widget_type'] = $widgetDataByPageId[$pageId]['type'];
            }
        }

        $this->setConstructorParam('structureDefinitions', $params['structureDefinitions']);

        return parent::_toHtml();
    }
}