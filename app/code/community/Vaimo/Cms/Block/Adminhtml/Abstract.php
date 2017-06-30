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

abstract class Vaimo_Cms_Block_Adminhtml_Abstract extends Mage_Adminhtml_Block_Template
{
    protected $_factory;
    protected $_app;

    public function __construct(array $args = array())
    {
        $this->_factory = isset($args['factory']) ?
            $args['factory'] : Mage::getModel('vaimo_cms/core_factory');

        $this->_app = isset($args['app']) ?
            $args['app'] : Mage::app();

        return parent::__construct($args);
    }

    /**
     * @return false|Vaimo_Cms_Model_Core_Factory
     */
    public function getFactory()
    {
        return $this->_factory;
    }

    /**
     * @return false|Mage_Core_Model_App
     */
    public function getApp()
    {
        return $this->_app;
    }
}