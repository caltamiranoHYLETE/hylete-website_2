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

abstract class Vaimo_Cms_Model_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @param array $_requiredArguments
     */
    protected $_requiredArguments = array();

    /**
     * @param array $_optionalArguments
     */
    protected $_optionalArguments = array();

    public function __construct(array $args = array())
    {
        $this->_factory = isset($args['factory']) ?
            $args['factory'] : Mage::getModel('vaimo_cms/core_factory');

        $this->_app = isset($args['app']) ?
            $args['app'] : Mage::app();

        parent::__construct($args);
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

    protected function _extractArgs(&$args, array $targetedKeys = array(), $failOnMissingArgument = false)
    {
        $requiredArgs = array_flip($targetedKeys);

        $values = array_intersect_key($args, $requiredArgs);

        if (count($values) < count($requiredArgs) && $failOnMissingArgument) {
            throw Mage::exception('Vaimo_Cms', 'Missing required arguments: ' .
                implode(', ', array_keys(array_diff_key($requiredArgs, $values))));
        }

        $args = array_diff_key($args, $values);

        return $values;
    }

    protected function _extractRequiredArgs(&$args)
    {
        return $this->_extractArgs($args, $this->_requiredArguments, true);
    }

    protected function _extractOptionalArgs(&$args)
    {
        return $this->_extractArgs($args, $this->_optionalArguments, false);
    }
}