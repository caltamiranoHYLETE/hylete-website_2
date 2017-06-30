<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_MultiOptionFilter_Model_Proxy
{
    protected $_delegate;
    protected $_overrides = array();
    protected $_selfFunctions = array();

    public function resetOverrides()
    {
        $this->_overrides = array();

        return $this;
    }

    public function setOverride($name, $override)
    {
        $this->_overrides[$name] = $override;

        return $this;
    }

    public function setDelegate($delegate)
    {
        $this->_delegate = $delegate;

        return $this;
    }

    public function setSelfFunctions(array $functionsThatReturnSelf)
    {
        $this->_selfFunctions = array_flip($functionsThatReturnSelf);

        return $this;
    }

    public function __toString()
    {
        if (isset($this->_overrides[__FUNCTION__])) {
            return (string)$this->__call(__FUNCTION__, array());
        }

        return (string)$this->_delegate;
    }

    public function __call($method, $args)
    {
        if (isset($this->_selfFunctions[$method])) {
            return $this;
        }

        if (isset($this->_overrides[$method])) {
            if (!is_callable($this->_overrides[$method])) {
                return $this->_overrides[$method];
            }

            array_unshift($args, $this->_delegate);
            return call_user_func_array($this->_overrides[$method], $args);
        } else {
            return call_user_func_array(array($this->_delegate, $method), $args);
        }
    }
}