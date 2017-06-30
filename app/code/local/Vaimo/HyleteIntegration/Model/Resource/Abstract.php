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
 * @package     Vaimo_HyleteIntegration
 * @file        Abstract.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

class Vaimo_HyleteIntegration_Model_Resource_Abstract extends Mage_Core_Model_Resource_Abstract
{
    /**
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    protected function _construct()
    {
    }

    protected function _getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('read');
    }

    protected function _getWriteAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('write');
    }

    public function __construct(array $args = array())
    {
        $this->_factory = isset($args['factory']) ?
            $args['factory'] : Mage::getModel('vaimo_cms/core_factory');


        $this->_app = isset($args['app']) ?
            $args['app'] : Mage::app();

        parent::__construct($args);
    }

    public function getFactory()
    {
        return $this->_factory;
    }

    public function getApp()
    {
        return $this->_app;
    }
}