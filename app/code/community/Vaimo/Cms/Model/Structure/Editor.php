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
class Vaimo_Cms_Model_Structure_Editor extends Vaimo_Cms_Model_Editor_Abstract
{
    const UPDATE_ACTION = 'structure_save';

    protected $_actions = array(
        self::UPDATE_ACTION => 'structureSave'
    );

    public function structureSave($arguments)
    {
        $factory = $this->getFactory();

        /** @var Vaimo_Cms_Model_Structure $structure */
        $structure = $factory->getModel('vaimo_cms/structure');

        /* @var $widgetHelper Vaimo_Cms_Helper_Widget */
        $widgetHelper = $factory->getHelper('vaimo_cms/widget');

        if (isset($arguments['structure_id'])) {
            $structure->load($arguments['structure_id']);
        }

        $structureItems = array_filter((array)$arguments['structure']);

        foreach ($structureItems as &$item) {
            if (!isset($item['widget_parameters'])) {
                continue;
            }

            $parameters = $widgetHelper->parseWidgetParameters($item['widget_parameters']);
            $parameters['parameters']['_vcms_user_configured'] = 1;

            $item['widget_parameters'] = $parameters;
        }

        $structure->setBlockReference($arguments['block_reference']);
        $structure->setStructureData($structureItems);

        /** @var Vaimo_Cms_Model_Page $target */
        $page = $factory->getModel('vaimo_cms/page', array(
            'handle' => $arguments['handle'],
            'store' => (int)$this->getApp()->getStore()->getId()
        ));

        $page->assignStructure($structure, \Vaimo_Cms_Helper_Page::DRAFT);
        $page->save();
    }

    public function structureSaveResponse($arguments)
    {
        $factory = $this->getFactory();
        $layout = $this->getApp()->getLayout();

        $handle = $arguments['handle'];
        $reference = $arguments['block_reference'];

        $storeId = $this->getApp()->getStore()->getId();

        $page = $factory->getModel('vaimo_cms/page', array(
            'handle' => $handle,
            'store' => $storeId
        ));

        $structures = $page->getStageStructures(Vaimo_Cms_Helper_Page::DRAFT);

        if (!isset($structures[$reference])) {
            return false;
        }

        $structure = $structures[$reference];

        $structureId = $structure->getId();

        $data = array(
            'structures' => array(
                array('reference' => $reference, 'id' => $structureId, 'items' => $structure->getStructureData())
            ),
            'html' => ''
        );

        $block = $layout->getBlock($reference);

        if ($block) {
            $data['html'] = $block->toHtml();
        }

        return $data;
    }

    public function getCurrentStructureDefinitions()
    {
        $handle = $this->getCurrentLayoutHandle();

        if (!$handle) {
            return array();
        }

        $factory = $this->getFactory();

        $storeId = $this->getApp()->getStore()->getId();

        /** @var Vaimo_Cms_Model_Page $page */
        $page = $factory->getModel('vaimo_cms/page', array(
            'handle' => $handle,
            'store' => (int)$storeId
        ));

        return $page->getStageStructures(Vaimo_Cms_Helper_Page::DRAFT);
    }
}