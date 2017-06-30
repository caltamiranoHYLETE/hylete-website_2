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

class Vaimo_Cms_Helper_Url extends Vaimo_Cms_Helper_Abstract
{
    public function decomposeUrl($url)
    {
        $dataHelper = $this->getFactory()->getHelper('vaimo_cms');

        $decodedUrl = htmlspecialchars_decode($url);

        $urlComponents = $dataHelper->formatValues(parse_url($decodedUrl), array(
            'scheme' => '%s://',
            'port' => '%s:'
        ));

        return array(
            $dataHelper->implodeSelected('', $urlComponents, array('scheme', 'host', 'port', 'path')),
            isset($urlComponents['query']) ? $urlComponents['query'] : '',
            isset($urlComponents['fragment']) ? $urlComponents['fragment'] : ''
        );
    }

    public function composeUrl($path, $query, $fragment)
    {
        return implode('?', array_filter(array($path, $query))) . ($fragment ? '#' . $fragment : '');
    }

    public function standardize($url)
    {
        list($path, $query, $fragment) = $this->decomposeUrl($url);

        return $this->composeUrl(trim($path, '/') . '/', $query, $fragment);
    }

    public function modifyQuery($query, array $updates)
    {
        $params = array();
        foreach (explode('&', $query) as $param) {
            $paramParts = explode('=', $param);
            $params[array_shift($paramParts)] = array_shift($paramParts);
        }

        $params = array_replace($params, $updates);

        return implode('&', array_map(function($name, $value) {
            return $name . '=' . $value;
        }, array_keys($params), $params));
    }
}