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

class Vaimo_Cms_Helper_Dom extends Mage_Core_Helper_Abstract
{
    protected $_dom = null;
    protected $_inLoop = false;
    protected $_lastWalkedHtml = false;

    const HTML_V4_HEADER = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    const SCRIPT_PLACEHOLDER_TAG = 'cms_script_placeholder';

    protected function _loadHtml($dom, $html, $strict = true)
    {
        $dom->strictErrorChecking = $strict;

        /**
         * Done because php version used do develop the module did not support HTML5 tags. So there's a way to do
         * non-strict html->object and object->html conversion
         */
        if (!$strict) {
            libxml_use_internal_errors(true);
        }

        if ($html) {
            try {
                $dom->loadHTML(mb_convert_encoding(self::HTML_V4_HEADER . $html, 'HTML-ENTITIES', 'UTF-8'));
            } catch (Exception $e) {
                return false;
            }
        }

        if (!$strict) {
            libxml_use_internal_errors(false);
        }

        $dom->strictErrorChecking = true;

        return $dom;
    }

    protected function _domToHtml($node)
    {
        if (isset($node->documentElement)) {
            $node = $node->documentElement->firstChild;
        }

        $html = '';
        $dom = $this->getDom();

        foreach ($node->childNodes as $item) {
            $html .= $dom->saveHTML($item);
        }

        $html = str_replace('&amp;quot;', '&quot;', $html);

        return $html;
    }

    protected function _createNodeTreeFromHtml($html)
    {
        $dom = $this->getDom();
        $this->_loadHtml($dom, $html, false);

        return $this->_getCurrentRootNode();
    }

    protected function _walkChildNodes($rootNode, closure $callable)
    {
        foreach($rootNode->childNodes as $node) {
            $this->_inLoop = true;
            if (method_exists($node, 'getAttribute')) {
                $callable($node, $this);
            }
        }

        $this->_inLoop = false;
    }

    public function getDom()
    {
        if (!$this->_dom) {
            $this->_dom = new DOMDocument('1.0', 'UTF-8');
            $this->_dom->formatOutput = false;
        }

        return $this->_dom;
    }

    protected function _getCurrentRootNode()
    {
        $dom = $this->getDom();

        return $dom && $dom->documentElement ? $dom->documentElement->firstChild : false;
    }

    protected function _stripLineBreaks($value)
    {
        return str_replace(array("\r\n", "\r", "\n"), "", $value);
    }

    public function getTagName($node)
    {
        return $node->tagName;
    }

    public function walkTopmostDomNodesOfHtml($html, closure $callable)
    {
        $_html = trim($this->_stripLineBreaks($html));

        if (!$_html) {
            return $html;
        }

        $scriptPlaceholder = '<' . self::SCRIPT_PLACEHOLDER_TAG . '>' . 'KEY_' . md5(time()) . '</' . self::SCRIPT_PLACEHOLDER_TAG . '>';
        $scriptTags = $this->_getScriptTagFromHtml($html);

        $html = $this->_replaceScriptTagsWithPlaceholders($html, $scriptTags, $scriptPlaceholder);

        if ($this->_lastWalkedHtml !== $_html) {
            $this->_lastWalkedHtml = $_html;
            $rootNode = $this->_createNodeTreeFromHtml($html, false);
        } else {
            $rootNode = $this->_getCurrentRootNode();
        }

        $that = $this;

        if ($rootNode) {
            $this->_walkChildNodes($rootNode, function($node, $dom) use (&$callable, $that) {
                return $callable($node, $dom);
            });

            $html = $this->_domToHtml($rootNode);
        }

        $html = $this->_addScriptTagsBack($html, $scriptTags, $scriptPlaceholder);

        $this->_lastWalkedHtml = $this->_stripLineBreaks($html);

        return $html;
    }

    public function setAttributeForNode($node, $key, $value)
    {
        if (!$this->_inLoop) {
            throw Mage::exception('Vaimo_Cms', 'Function should be used only inside walk loop');
        }

        $value = str_replace('"', '&quot;', $value);
        $node->setAttribute($key, $value);
    }

    public function walkTagAttributeValues($html, $tag, $attribute, closure $closure)
    {
        $pattern = "/(<" . $tag . "[^>]*" . $attribute. " *= *[\"']?)([^\"']*)/i";

        $html = preg_replace_callback($pattern, function($matches) use ($closure) {
            $value = $closure($matches[2]);

            return  $matches[1] . ($value === null ? $matches[2] : $value);
        }, $html);

        return $html;
    }

    public function _getScriptTagFromHtml($html)
    {
        $matches = preg_match_all('/<script(.*?)<\/script>/s', $html, $scriptsInHtml);

        if ($matches == 0) {
            return array();
        }

        return $scriptsInHtml[0];
    }

    public function _replaceScriptTagsWithPlaceholders($html, $scriptTags, $placeholder)
    {
        foreach ($scriptTags as $tag) {
            $html = str_replace($tag, $placeholder, $html);
        }

        return $html;
    }

    public function _addScriptTagsBack($html, $scriptTags, $placeholder)
    {
        foreach ($scriptTags as $tag) {
            $html = preg_replace('/' . preg_quote($placeholder, '/') . '/', $tag, $html, 1);
        }

        return $html;
    }
}