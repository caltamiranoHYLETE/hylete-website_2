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

class Vaimo_Cms_Helper_Directive extends Vaimo_Cms_Helper_Abstract
{
    public function replaceUrlAttributes($input, $tag, $attribute, $urlSuffix, $directivePattern)
    {
        $factory = $this->getFactory();

        $domHelper = $factory->getHelper('vaimo_cms/dom');
        $urlHelper = $factory->getHelper('vaimo_cms/url');

        $urlSuffix = trim($urlSuffix, '/');
        $suffixLength = strlen($urlSuffix);

        $processor = function($value) use ($urlSuffix, $directivePattern, $suffixLength, $urlHelper) {
            if (substr($value, 0, $suffixLength) !== $urlSuffix) {
                return $value;
            }

            if (strlen($value) != $suffixLength && substr($value, $suffixLength, 1) != '/') {
                return $value;
            }

            list($path, $query, $anchor) = $urlHelper->decomposeUrl($value);

            $directive = sprintf($directivePattern,
                trim(substr($path, strlen($urlSuffix)), '/'),
                str_replace('%2C', ',', $query . ($anchor ? '#' . $anchor : ''))
            );

            return trim($directive, '?');
        };

        return $domHelper->walkTagAttributeValues($input, $tag, $attribute, $processor);
    }

    public function createDirectiveFromMediaUrl($value)
    {
        $baseMediaUrl = Mage::getBaseUrl('media');

        if (substr($value, 0, strlen($baseMediaUrl)) === $baseMediaUrl) {
            return '{{media url="' . substr($value, strlen($baseMediaUrl)) . '"}}';
        }

        return $value;
    }
}