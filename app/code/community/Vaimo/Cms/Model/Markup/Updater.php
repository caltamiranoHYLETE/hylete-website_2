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

class Vaimo_Cms_Model_Markup_Updater extends Vaimo_Cms_Model_Abstract
{
    const CMS_BLOCK_ID_MARKUP = 'block-id';
    const CMS_BLOCK_CONTENT_MARKUP = 'block-content';
    const STRUCTURE_CHILD_OF_MARKUP = 'child-of';
    const STRUCTURE_CLASS = 'text-list-output';
    const CMS_WIDGET_PAGE_ID_MARKUP = 'vcms-widget-page-id';
    const CMS_WIDGET_TYPE_MARKUP = 'widget-instance-type';
    const CMS_WIDGET_CLONE_ID_MARKUP = 'widget-clone-of';

    const MULTI_VALUE_DELIMITER = '|';

    /**
     * @param Mage_Core_Block_Abstract $block
     * @param string $html
     * @param bool $includeParents
     * @return string
     */
    public function process($block, $html, $includeParents = false)
    {
        $blockType = $block->getType();
        $blockName = $block->getNameInLayout();
        $factory = $this->getFactory();
        $parent = $block->getParentBlock();

        if ($blockType == 'cms/block') {
            $blockId = $block->getBlockId();

            $cmsBlock = $factory->getModel('cms/block')->load($blockId);

            if ($blockId = $cmsBlock->getId()) {
                $content = $cmsBlock->getContent();

                $dataAttributes = array('data-' . self::CMS_BLOCK_ID_MARKUP . '="' . $blockId . '"');

                if (strstr($content, '{{block') !== false) {
                    $processor = $factory->getSingleton('vaimo_cms/directive_analyser');
                    $info = $processor->getInfo($content);

                    if ($info['content'] || count($info['directives']) > 1) {
                        $dataAttributes[] = 'data-' . self::CMS_BLOCK_CONTENT_MARKUP . '="' .  htmlentities($content) . '"';
                    } else {
                        $dataAttributes = array();
                    }
                }

                if (!$html) {
                    array_unshift($dataAttributes, 'class="vcms-placeholder"');
                }

                if ($dataAttributes) {
                    $html = '<div ' . implode(' ', $dataAttributes) . '>' . $html . '</div>';
                }
            }
        }

        if ($blockType == 'cms/widget_block') {
            if ($blockId = $block->getBlockId()) {
                $domHelper = $factory->getHelper('vaimo_cms/dom');

                $cmsBlock = $factory->getModel('cms/block')->load($blockId);

                if ($cmsBlock->hasData()) {
                    if (!$html) {
                        $html = '<div class="vcms-placeholder"></div>';
                    }

                    $html = $domHelper->walkTopmostDomNodesOfHtml($html, function($node, $dom) use ($cmsBlock, $factory) {
                        $dataAttributes = array(
                            Vaimo_Cms_Model_Markup_Updater::CMS_BLOCK_ID_MARKUP => $cmsBlock->getId()
                        );

                        $content = $cmsBlock->getContent();
                        if (strstr($content, '{{block') !== false) {
                            $processor = $factory->getSingleton('vaimo_cms/directive_analyser');
                            $info = $processor->getInfo($content);

                            if ($info['content'] || count($info['directives']) > 1) {
                                $dataAttributes[Vaimo_Cms_Model_Markup_Updater::CMS_BLOCK_CONTENT_MARKUP] = $content;
                            } else {
                                $dataAttributes = array();
                            }
                        }

                        foreach ($dataAttributes as $key => $value) {
                            $dom->setAttributeForNode($node, 'data-' . $key, $value);
                        }
                    });
                }
            }
        }

        if ($blockType == 'vaimo_cms/structure') {
            if ($structureId = $block->getStructureId()) {
                $domHelper = $factory->getHelper('vaimo_cms/dom');
                $html = $domHelper->walkTopmostDomNodesOfHtml($html, function($node, $dom) use ($structureId) {
                    $dom->setAttributeForNode($node, 'data-structure-id', $structureId);
                });


            }
        }

        $update = $block->getLayout()->getUpdate();

        $references = $this->getFactory()->getSingleton('vaimo_cms/layout_update')
            ->getWidgetInstanceLayoutUpdates($update);

        if (isset($references[$blockName])) {
            $domHelper = $factory->getHelper('vaimo_cms/dom');

            $pageId = $references[$blockName]['page_id'];

            $type = false;
            if (isset($references[$blockName]['type'])) {
                $type = $references[$blockName]['type'];
            }

            $cloneOf = false;
            if ($block->hasVaimoCmsStructureItemConfiguration()) {
                $widgetConf = $block->getVaimoCmsStructureItemConfiguration();

                if (isset($widgetConf['clone_of'])) {
                    $cloneOf = $widgetConf['clone_of'];
                }
            }

            if (!$html) {
                $html = '<div class="vcms-placeholder"></div>';
            }

            $html = $domHelper->walkTopmostDomNodesOfHtml($html, function($node, $dom) use ($pageId, $type, $cloneOf) {
                if ($dom->getTagName($node) == Vaimo_Cms_Helper_Dom::SCRIPT_PLACEHOLDER_TAG) {
                    return;
                }

                $dom->setAttributeForNode($node, 'data-' . Vaimo_Cms_Model_Markup_Updater::CMS_WIDGET_PAGE_ID_MARKUP, $pageId);

                if ($type) {
                    $dom->setAttributeForNode($node, 'data-' . Vaimo_Cms_Model_Markup_Updater::CMS_WIDGET_TYPE_MARKUP, $type);
                }

                if ($cloneOf) {
                    $dom->setAttributeForNode($node, 'data-' . Vaimo_Cms_Model_Markup_Updater::CMS_WIDGET_CLONE_ID_MARKUP, $cloneOf);
                }
            });
        }

        $containers = $factory->getHelper('vaimo_cms/layout')->getWidgetContainers($block->getLayout());

        $parentNames = array();
        if (isset($containers[$blockName])) {
            if (!trim($html)) {
                $parentNames = array($blockName);
                $html = '<div class="vcms-placeholder"></div>';
            } else if (!trim(preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html))) {
                $parentNames = array($blockName);
                $html .= '<div class="vcms-placeholder"></div>';
            }
        }

        if($parent && isset($containers[$parent->getNameInLayout()])) {
            $parentNames = array($parent->getNameInLayout());

            if ($includeParents) {
                while ($parent = $parent->getParentBlock()) {
                    if (!isset($containers[$parent->getNameInLayout()])) {
                        continue;
                    }

                    $parentNames[] = $parent->getNameInLayout();
                }
            }
        }

        foreach ($parentNames as $name) {
            $html = $this->insertStructureDataAttributes($html, $name);
        }

        return $html;
    }

    /**
     * @param string $html
     * @param string $name
     * @return string
     */
    public function insertStructureDataAttributes($html, $name)
    {
        $factory = $this->getFactory();
        $domHelper = $factory->getHelper('vaimo_cms/dom');

        return $domHelper->walkTopmostDomNodesOfHtml($html, function ($node, $dom) use ($name) {
            if ($dom->getTagName($node) == Vaimo_Cms_Helper_Dom::SCRIPT_PLACEHOLDER_TAG) {
                return;
            }

            $classes = explode(';', $node->getAttribute('class'));
            $classes[] = Vaimo_Cms_Model_Markup_Updater::STRUCTURE_CLASS;

            $key = 'data-' . Vaimo_Cms_Model_Markup_Updater::STRUCTURE_CHILD_OF_MARKUP;

            $delimiter = Vaimo_Cms_Model_Markup_Updater::MULTI_VALUE_DELIMITER;

            $names = explode($delimiter,
                trim($node->getAttribute($key), $delimiter));

            $names[] = $name;

            $classes = array_unique(array_filter($classes));
            $names = array_unique(array_filter($names));

            $dom->setAttributeForNode($node, 'class', implode(' ', $classes));
            $dom->setAttributeForNode($node, $key, $delimiter . implode($delimiter, $names) . $delimiter);
        });
    }

    public function convertImagesToMediaDirectives($html)
    {
        $factory = $this->getFactory();

        /* @var $domHelper Vaimo_Cms_Helper_Dom */
        $domHelper = $factory->getHelper('vaimo_cms/dom');

        /* @var $directiveHelper Vaimo_Cms_Helper_Directive */
        $directiveHelper = $factory->getHelper('vaimo_cms/directive');

        $targets = array(
            array('tag' => 'img', 'attribute' => 'src'),
            array('tag' => 'a', 'attribute' => 'href')
        );

        foreach ($targets as $target) {
            $html = $domHelper->walkTagAttributeValues(
                $html,
                $target['tag'],
                $target['attribute'],
                function($value) use ($directiveHelper) {
                    return $directiveHelper->createDirectiveFromMediaUrl($value);
                }
            );
        }

        return $html;
    }

    public function removeEditModeParamsFromLinks($html)
    {
        $factory = $this->getFactory();

        /* @var $domHelper Vaimo_Cms_Helper_Dom */
        $domHelper = $factory->getHelper('vaimo_cms/dom');

        $allowedSchemas = array_flip(array('http', 'https'));

        return $domHelper->walkTagAttributeValues($html, 'a', 'href', function($value) use ($allowedSchemas) {
            if (strpos($value, '?') === false) {
                return $value;
            }

            $schema = strtok($value, ':');

            if (!isset($allowedSchemas[$schema]) && $schema !== $value) {
                return $value;
            }

            $value = htmlspecialchars_decode($value);

            $uriModel = Zend_Uri_Http::fromString($value);

            $uriModel->addReplaceQueryParameters(array(
                Vaimo_Cms_Model_Mode::EDIT_PARAMETER => null,
                Vaimo_Cms_Model_Mode::STORE_ID_PARAMETER => null
            ));

            return $uriModel->getUri();
        });
    }

    public function convertRelativeUrlsToAbsolute($html)
    {
        /* @var $domHelper Vaimo_Cms_Helper_Dom */
        $domHelper = $this->getFactory()->getHelper('vaimo_cms/dom');
        $store = $this->getApp()->getStore();

        $skippedSchemas = array_flip(array('http', 'https', 'mailto', 'tel'));

        return $domHelper->walkTagAttributeValues($html, 'a', 'href', function($value) use ($store, $skippedSchemas) {
            $schema = strtok($value, ':');

            if (!isset($skippedSchemas[$schema])) {
                $valueParts = explode('?', $value);

                array_unshift($valueParts, $store->getUrl('', array(
                    '_direct' => trim(array_shift($valueParts), '/')
                )));

                $value = implode('?', $valueParts);
            }

            return $value;
        });
    }

    public function convertStoreUrlDirectives($html)
    {
        $factory = $this->getFactory();

        $directiveHelper = $factory->getHelper('vaimo_cms/directive');

        $baseUrl = $this->getApp()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        return  $directiveHelper->replaceUrlAttributes(
            $html, 'a', 'href', trim($baseUrl, '/'), '{{store direct_url="%s" _query="%s"}}'
        );
    }

    public function convertDirectUrlDirectives($html)
    {
        $factory = $this->getFactory();

        if (Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL)) {
            $store = $this->getApp()->getStore();

            $directiveHelper = $factory->getHelper('vaimo_cms/directive');

            $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $html = $directiveHelper->replaceUrlAttributes(
                $html, 'a', 'href', trim($baseUrl, '/'), '{{config path="web/unsecure/base_url"}}%s?%s'
            );

            $secureBaseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);
            $html = $directiveHelper->replaceUrlAttributes(
                $html, 'a', 'href', trim($secureBaseUrl, '/'), '{{config path="web/secure/base_url"}}%s?%s'
            );
        }

        return $html;
    }

    protected function _replaceMediaUrlsToDirective($value, $baseMediaUrl)
    {
        if (substr($value, 0, strlen($baseMediaUrl)) === $baseMediaUrl) {
            return '{{media url="' . substr($value, strlen($baseMediaUrl)) . '"}}';
        }

        return $value;
    }
}