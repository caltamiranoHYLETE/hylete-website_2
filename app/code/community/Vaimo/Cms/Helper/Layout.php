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

class Vaimo_Cms_Helper_Layout extends Vaimo_Cms_Helper_Abstract
{
    protected $_blockDefinitionsWithLabels;

    public function getWidgetContainers(Mage_Core_Model_Layout $layout)
    {
        if (!$this->_blockDefinitionsWithLabels) {
            $this->_blockDefinitionsWithLabels = array();
            $blockDefinitionsWithLabels = $layout->getXpath('//block/label/..');
            $this->_blockDefinitionsWithLabels = array();
            $allBlocks = $layout->getAllBlocks();

            if ($blockDefinitionsWithLabels == false) {
                return $this->_blockDefinitionsWithLabels;
            }

            foreach ($blockDefinitionsWithLabels as $blockDefinition) {
                $nameInLayout = $blockDefinition->getAttribute('name');

                if (!isset($allBlocks[$nameInLayout])) {
                    continue;
                }

                $blockWithLabel = $allBlocks[$nameInLayout];

                if ($blockWithLabel->hasDisableContentPageContainer()) {
                    continue;
                }

                if ($blockWithLabel instanceof Mage_Core_Block_Text_List) {
                    $this->_blockDefinitionsWithLabels[$nameInLayout] = $blockWithLabel;
                }
            }
        }

        return $this->_blockDefinitionsWithLabels;
    }

    public function getWidgetContainerNames(Mage_Core_Model_Layout $layout)
    {
        $containers = $this->getWidgetContainers($layout);

        return array_keys($containers);
    }

    public function removeNonWidgetBlocksFromContainers($layout, $targets = array())
    {
        $widgets = $this->getFactory()->getSingleton('vaimo_cms/layout_update')
            ->getWidgetInstanceLayoutUpdates($layout->getUpdate());

        $containers = $this->getWidgetContainers($layout);

        if (!$targets && $targets !== false) {
            $targets = array_keys($containers);
        } else {
            $targets = $targets ? $targets : array();
            foreach ($containers as $name => $container) {
                if ($container->getIsContentPageContainer()) {
                    $targets[] = $name;
                }
            }
        }

        foreach ($targets as $target) {
            if (!isset($containers[$target])) {
                continue;
            }

            if (!$block = $layout->getBlock($target)) {
                continue;
            }

            $children = $block->getChild();

            foreach ($children as $name => $child) {
                if (isset($widgets[$name])) {
                    continue;
                }

                $block->unsetChild($name);
            }
        }

        return $layout;
    }

    public function getNameInLayoutFromUpdateXml($xml)
    {
        $updateStr = '<xml>' . $xml . '</xml>';

        $name = '';

        try {
            $updateXml = simplexml_load_string($updateStr);

            if ($updateXml && $updateXml->reference && $updateXml->reference->block) {
                $name = (string)$updateXml->reference->block->attributes()->name;
            }
        } catch (Exception $e) {}

        return $name;
    }

    public function getWidgetLayoutUpdateDataGroupedByPageId($update)
    {
        $widgetDataByPageId = array();

        $items = $this->getFactory()->getSingleton('vaimo_cms/layout_update')
            ->getWidgetInstanceLayoutUpdates($update);

        foreach ($items as $nameInLayout => $data) {
            $widgetDataByPageId[$data['page_id']] = $data;
        }

        return $widgetDataByPageId;
    }

    public function getWidgetLayoutUpdateDataGroupedByInstanceId($update)
    {
        $namesInLayoutByInstanceId = array();

        $items = $this->getFactory()->getSingleton('vaimo_cms/layout_update')
            ->getWidgetInstanceLayoutUpdates($update);

        foreach ($items as $nameInLayout => $data) {
            $instanceId = $data['id'];

            if (!isset($namesInLayoutByInstanceId[$instanceId])) {
                $namesInLayoutByInstanceId[$instanceId] = array();
            }

            $namesInLayoutByInstanceId[$instanceId][$nameInLayout] = $data;
        }

        return $namesInLayoutByInstanceId;
    }

    public function createUniqueBlock($container, $type, $name, $alias, $data = array())
    {
        $layout = $container->getLayout();

        if ($layout->getBlock($name)) {
            return false;
        }

        $block = $layout->createBlock($type, $name);

        $container->unsetChild($alias);
        $container->insert($block, '', false, $alias);

        $block->addData($data);

        return $block;
    }

    public function adoptBlocks($from, $to, array $targets)
    {
        foreach ($targets as $name => $item) {
            $block = $from->getChild($name);

            if (!$block) {
                continue;
            }

            $from->unsetChild($name);
            $to->insert($block);

            $block->addData($item);
        }
    }
}
