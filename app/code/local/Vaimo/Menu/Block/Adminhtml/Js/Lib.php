<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

/**
 * Class Vaimo_Menu_Block_Adminhtml_Js_Lib
 *
 * @method setClassInstantiationCalls(string $callsDefinition)
 * @method getClassInstantiationCalls()
 */
class Vaimo_Menu_Block_Adminhtml_Js_Lib extends Mage_Adminhtml_Block_Abstract
{
    protected $_template = 'js_lib.phtml';

    protected $_jsClassName;
    protected $_instanceName;
    protected $_controllerPath = false;
    protected $_instantiationCalls = array();

    protected function _construct()
    {
        parent::_construct();

        $params = array();

        if ($this->_controllerPath !== false) {
            $controllerUrl = $this->getUrl($this->_controllerPath);
            $params = array($controllerUrl);
        }

        $this->_instantiationCalls = array($this->_jsClassName => $params);
    }

    public function getInstanceName()
    {
        return $this->_instanceName;
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 1) == '_' && substr($method, 1, 1) != '_') {
            $this->_instantiationCalls[] = array(substr($method, 1) => $args);
            return $this;
        } else {
            return parent::__call($method, $args);
        }
    }

    protected function _toHtml()
    {
        $this->setClassInstantiationCalls(array($this->_instanceName => $this->_instantiationCalls));

        return parent::_toHtml();
    }
}