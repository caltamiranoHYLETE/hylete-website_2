<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_BlockAjax
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 * @author      Arne Steinarson <arne.steinarson@vaimo.com>
 * @comment     Originates from Vaimo_PlugAndPlay. Used for AJAX container id/class detection.
 */

class Vaimo_BlockAjax_Helper_Dom extends Mage_Core_Helper_Abstract
{
    /**
     * Container for the main document
     *
     * @var null
     */
    protected $_dom = null;

    /**
     * HTML header for loading html segments
     */
    const HTML_V4 = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';

    /**
     * Container for loading pnp block html output into dom
     *
     * @var null
     */
    protected $_loader = null;

    /**
     * Get main DOM document
     *
     * @param string|bool $html
     * @param bool $strict
     * @return DOMDocument|null
     */
    public function getDom($html = false, $strict = true)
    {
        if (!$this->_dom) {
            $this->_dom = new DOMDocument();
        }

        return $this->_loadHtml($this->_dom, $html, $strict);
    }

    /**
     * Get DOM node loader
     *
     * @return DOMDocument|null
     */
    protected function _getDomLoader()
    {
        if (!$this->_loader) {
            $this->_loader = new DOMDocument();
        }

        return $this->_loader;
    }

    /**
     * Convert HTML to dom with specified dom object
     *
     * @param $dom
     * @param $html
     * @param $strict
     *
     * @return bool|object
     */
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
                $dom->loadHTML(self::HTML_V4 . $html);
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

    /**
     * Create DOM-node tree from html input.
     *
     * DOMDocument creates nodes by adding typical HTML wrappers around them - so we need to access the <body> node
     * to get our actual content.
     *
     * @param $html
     * @return DOMElement
     */
    public function createNodeTreeFromHtml($html)
    {
        $loader = $this->_getDomLoader();

        if ($this->_loadHtml($loader, $html, false)) {
            if (!$loader->documentElement) {
                return false;
            }

            $node = $loader->documentElement->firstChild;

            return $node;
        }

        return false;
    }

    /**
     * Get first child element that is based on DOMElement (skip text, etc)
     *
     * @param $node
     * @return bool
     */
    public function getFirstChildElement($node)
    {
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    return $child;
                }
            }
        }

        return false;
    }
}