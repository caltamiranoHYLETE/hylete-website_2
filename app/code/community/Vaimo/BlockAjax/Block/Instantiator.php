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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_BlockAjax
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @comment     Helper to extend the cache implementation already present in app()
 */

class Vaimo_BlockAjax_Block_Instantiator extends Mage_Core_Block_Template
{
    protected $_instances = array();

    public function add($instanceName)
    {
        $this->_instances[$instanceName] = true;

        return $this;
    }

    public function getRegisteredInstances()
    {
        return array_keys($this->_instances);
    }

    public function remove($instanceName)
    {
        unset($this->_instances[$instanceName]);

        return $this;
    }

    public function _toHtml()
    {
        if (!array_filter($this->_instances)) {
            return '';
        }

        return parent::_toHtml();
    }
}