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
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_MultiOptionFilter_Model_Runtime_Proxies_BlockProxy extends Mage_Core_Block_Template
{
    protected $_delegate;
    protected $_interceptor;

    public function setDelegate(Mage_Catalog_Block_Layer_Filter_Abstract $filterBlock)
    {
        $this->_delegate = $filterBlock;

        $this->setTemplate($filterBlock->getTemplate());
        $this->setLayout($filterBlock->getLayout());
    }

    public function setInterceptor($interceptor)
    {
        $this->_interceptor = $interceptor;
        $this->_interceptor->setDelegate($this->_delegate);
    }

    public function urlEscape($data)
    {
        return $this->__call('urlEscape', func_get_args());
    }

    public function __call($method, $args)
    {
        if ($this->_interceptor && method_exists($this->_interceptor, $method)) {
            return call_user_func_array(array($this->_interceptor, $method), $args);
        }

        if ($this->_delegate) {
            return call_user_func_array(array($this->_delegate, $method), $args);
        }

        return parent::__call($method, $args);
    }

    public function getData($key = '', $index = null)
    {
        if ($this->_delegate) {
            return $this->_delegate->getData($key, $index);
        } else {
            return parent::getData($key, $index);
        }
    }

    public function getHtml()
    {
        if ($this->_interceptor && method_exists($this->_interceptor, 'getHtml')) {
            call_user_func_array(array($this->_interceptor, 'getHtml'), array());
        }

        return parent::_toHtml();
    }
}
