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
 * @package     Vaimo_UploadLogo
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_UploadLogo_Test_Case extends PHPUnit_Framework_TestCase
{
    /**
     * A helper function to be able to test private functions.
     * Only works on PHP version >= 5.3.2
     *
     * @param $obj
     * @param $name
     * @param  array $args
     * @return mixed
     * @throws Exception
     */
    protected function _callMethod($obj, $name, array $args)
    {
        if (!(strnatcmp(phpversion(),'5.3.2') >= 0)) {
            throw new Exception('callMethod requires PHP version >= 5.3.2');
        }

        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }


    /**
     * Return block mock by using Magento's getModel approach
     *
     * @param $name
     * @param bool $mockMethods
     * @return mixed
     */
    protected function _getBlockMock($name, $mockMethods = false)
    {
        $class = Mage::getConfig()->getBlockClassName($name);

        if ($mockMethods && !is_array($mockMethods)) {
            $mockMethods = array($mockMethods);
        }

        if ($mockMethods) {
            return $this->getMock($class, $mockMethods);
        } else {
            return $this->getMock($class);
        }
    }

    /**
     * Return model mock by using Magento's getModel approach
     *
     * @param $name
     * @param bool $mockMethods
     * @return mixed
     */
    protected function _getModelMock($name, $mockMethods = false)
    {
        $class = Mage::getConfig()->getModelClassName($name);

        if ($mockMethods && !is_array($mockMethods)) {
            $mockMethods = array($mockMethods);
        }

        if ($mockMethods) {
            return $this->getMock($class, $mockMethods);
        } else {
            return $this->getMock($class);
        }
    }

    protected function _getFixturePath($name)
    {
        return realpath(dirname(__FILE__)) . DS . 'Fixtures' . DS . $name;
    }

    protected function _loadCSVFixtureFile($name)
    {
        $filename = $this->_getFixturePath($name);
        $csv = new Varien_File_Csv();
        return  $csv->getData($filename);
    }
}
