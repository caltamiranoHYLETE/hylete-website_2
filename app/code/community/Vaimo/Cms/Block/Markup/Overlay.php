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

class Vaimo_Cms_Block_Markup_Overlay extends Vaimo_Cms_Block_Js_Lib
{
    protected $_type = self::TYPE_JQUERY;
    protected $_jsClassName = 'markupOverlayManager';
    protected $_constructorParams = array(
        'overlayIdPrefix'       => 'vcms-overlay',
        'attributes'            => array(),
        'container'             => 'body',
        'className'             => 'vcms-markup-overlay',
        'templates'             => array(),
        'realTimeRefresh'       => true,
        'realTimeUpdate'        => true,
        'initialUpdateDelay'    => 50,
        'realTimeUpdateDelay'   => 150,
        'exclude'               => array('.vcms-toolbar', '.vcms-toolbar-margin-top', '.vcms-loader-indicator')
    );

    public function addClassName($class)
    {
        $this->_constructorParams['className'] .= ' ' . $class;

        return $this;
    }

    public function addOverlayType($attribute, $template = false)
    {
        $this->_constructorParams['attributes'][] = $attribute;
        $this->_constructorParams['attributes'] = array_unique($this->_constructorParams['attributes']);

        if ($template) {
            $this->_constructorParams['templates'][$attribute] = $template;
        }

        return $this;
    }

    public function setContext($value)
    {
        $this->setConstructorParam('context', $value);

        return $this;
    }

    public function addExclude($value)
    {
        $this->_constructorParams['exclude'][] = $value;

        return $this;
    }
}