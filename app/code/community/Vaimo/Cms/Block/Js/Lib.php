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
 * Class Vaimo_Cms_Block_Adminhtml_Js_Lib
 *
 * @method setClassInstantiationCalls(string $callsDefinition)
 * @method getClassInstantiationCalls()
 * @method hasControllerUrlOverride()
 * @method getControllerUrlOverride()
 */
abstract class Vaimo_Cms_Block_Js_Lib extends Vaimo_Cms_Block_Abstract
{
    const TYPE_PROTOTYPE = 1;
    const TYPE_JQUERY = 2;

    protected $_template = 'vaimo/cms/js/lib.phtml';

    protected $_type = self::TYPE_PROTOTYPE;
    protected $_namespace = 'vaimo';
    protected $_prettify = false;
    protected $_jsClassName;
    protected $_instanceName;
    protected $_controllerPath = false;
    protected $_instantiationCalls = array();
    protected $_constructorParams = array();
    protected $_anonymous = false;

    protected function _init()
    {
    }

    public function setLayout(Mage_Core_Model_Layout $layout)
    {
        $returnValue = parent::setLayout($layout);

        $this->_init();

        return $returnValue;
    }

    public function addConstructorParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->setConstructorParam($key, $value);
        }

        return $this;
    }

    public function setConstructorParam($key, $value)
    {
        if (is_array($value) && isset($this->_constructorParams[$key]) && is_array($this->_constructorParams[$key])) {
            $currentValue = $this->_constructorParams[$key];
            $this->_constructorParams[$key] = array_replace_recursive($currentValue, $value);
        } else {
            $this->_constructorParams[$key] = $value;
        }

        return $this;
    }

    public function getInstanceName()
    {
        if (!$this->_instanceName) {
            $this->_instanceName = 'v' . md5(microtime());
            $this->_anonymous = true;
        }

        return $this->_instanceName;
    }

    public function getConstructorParameters()
    {
        $this->_prepareNestedLibraries();

        return $this->_constructorParams;
    }

    public function isAnonymous()
    {
        return $this->_anonymous;
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

    public function useInstance($nameInLayout, $as)
    {
        $parent = $this->getParentBlock();

        $instance = $parent->getChild($nameInLayout);

        $this->_constructorParams[$as] = $instance;
    }

    protected function _prepareNestedLibraries()
    {
        foreach ($this->getChild() as $child) {
            $alias = $child->getBlockAlias();
            if ($child->getBlockAlias() != $child->getNameInLayout() && isset($this->_constructorParams[$alias])) {
                $this->_constructorParams[$alias] = $child;
            }
        }
    }

    protected function _toHtml()
    {
        foreach ($this->getChild() as $child) {
            $child->setIsNestedLibrary(true);
        }

        $params = $this->getConstructorParameters();

        if ($this->_controllerPath !== false) {
            $controllerUrl = $this->getUrl($this->_controllerPath, $params);

            if ($this->hasControllerUrlOverride()) {
                $controllerUrl = $this->getControllerUrlOverride();
            }

            if (isset($params['controller_url'])) {
                $params['controller_url'] = $controllerUrl;
            } else {
                $params = array($controllerUrl);
            }
        }

        $className = ($this->_namespace ? $this->_namespace . '.' : '') . $this->_jsClassName;

        $this->_instantiationCalls = array_merge(
            array($className => $params),
            $this->_instantiationCalls
        );

        $this->setClassInstantiationCalls(array($this->getInstanceName() => $this->_instantiationCalls));

        return parent::_toHtml();
    }

    protected function _getInstantiationCall($pattern, $arrayOfArguments, $padding = 0)
    {
        $instance = $this->getInstanceName();
        $className = $this->_jsClassName;

        $arguments = $this->_getVariableList($arrayOfArguments, $this->_type == self::TYPE_JQUERY, $padding);

        if ($arguments == '{}') {
            $arguments = '';
        }

        return sprintf($pattern, $instance, $this->_namespace . '.' . $className, $arguments);
    }

    protected function _getVariableList($arrayOfArguments, $asObject = false, $padding = 0)
    {
        $arrayOfArguments = array_map(function ($item) use ($asObject) {
            if (is_a($item, 'Vaimo_Cms_Block_Js_Lib')) {
                return $asObject ? $item : $item->getInstanceName();
            }

            return json_encode($item);
        }, $arrayOfArguments);

        if ($asObject) {
            $_arrayOfArguments = array();

            $prefix = '';
            if ($this->_prettify) {
                $prefix = "\n" . str_pad('', $padding);
            }

            foreach ($arrayOfArguments as $key => $value) {
                if (is_a($value, 'Vaimo_Cms_Block_Js_Lib')) {
                    $value = $value->getInstanceName();
                }

                $_arrayOfArguments[] = $prefix . '"' . $key . '":' . $value;
            }

            $arrayOfArguments = $_arrayOfArguments;
        }

        $arguments = implode(',', $arrayOfArguments);

        if ($asObject) {
            $arguments = '{' . $arguments . ($this->_prettify ? ("\n" . str_pad('', $padding - 4)) : '') . '}';
        }

        return $arguments;
    }

    protected function _getFunctionCall($function, $arrayOfArguments)
    {
        $instance = $this->getInstanceName();
        $arguments = $this->_getVariableList($arrayOfArguments);

        return sprintf('%s.%s(%s);', $instance, $function, $arguments);
    }
}