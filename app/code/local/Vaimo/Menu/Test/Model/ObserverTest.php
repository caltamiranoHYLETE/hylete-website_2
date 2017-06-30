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

class Vaimo_Menu_Test_Model_Observer extends Vaimo_Menu_Test_BaseCase
{
    protected $_model;
    protected $_observer;
    protected $_update;

    public function _setupDesignPackageMock()
    {
        $designPackageMock = $this->getMock('Mage_Core_Model_Design_Package', array('designPackageExists'));
        $designPackageMock->expects($this->any())
            ->method('designPackageExists')
            ->will($this->returnValue(true));
        $this->_mockModelSingleton('core/design_package', $designPackageMock);

        return $designPackageMock;
    }

    public function setUp()
    {
        Mage::app()->cleanCache(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG);

        $this->_model = Mage::getModel('vaimo_menu/observer');
        $layout = new Varien_Object();
        $this->_update = $this->getMock('Mage_Core_Model_Layout_Update', array('fetchDbLayoutUpdates'));
        $layout->setUpdate($this->_update);

        $this->_observer = new Varien_Event_Observer(array('layout' => $layout));
    }

    public function testOnControllerActionLayoutGenerateXmlBeforeShouldAddBaseDesignPackageDbLayoutUpdatesForEachHandle()
    {
        $calls = array();

        $designPackageMock = $this->_setupDesignPackageMock();

        $this->_update->expects($this->any())
            ->method('fetchDbLayoutUpdates')
            ->will($this->returnCallback(
                function($handle) use (&$calls, $designPackageMock) {
                    $calls[$handle] = $designPackageMock->getPackageName() . '/' . $designPackageMock->getTheme();
                }
            ));

        $this->_update->addHandle(array('handle1', 'handle2', 'handle3'));

        $this->_model->onControllerActionLayoutGenerateXmlBefore($this->_observer);

        $expected = array(
            'handle1' => 'base/default',
            'handle2' => 'base/default',
            'handle3' => 'base/default'
        );

        $this->assertEquals($expected, $calls);
    }

    public function testOnControllerActionLayoutGenerateXmlBeforeShouldNotReloadDatabaseUpdatesWhenCalledTwiceAndLayoutCacheEnabled()
    {
        $this->_setupCacheTest(Vaimo_Menu_Model_Layout_Update::LAYOUT_CACHE_FLAG);

        $calls = array();

        $designPackageMock = $this->_setupDesignPackageMock();

        $this->_update->expects($this->any())
            ->method('fetchDbLayoutUpdates')
            ->will($this->returnCallback(
                function($handle) use (&$calls, $designPackageMock) {
                    $calls[] = $handle . $designPackageMock->getPackageName() . '/' . $designPackageMock->getTheme();
                }
            ));

        $this->_update->addHandle(array('handle1', 'handle2', 'handle3'));

        $this->_model->onControllerActionLayoutGenerateXmlBefore($this->_observer);
        $this->_model->onControllerActionLayoutGenerateXmlBefore($this->_observer);


        $this->assertEquals($calls, array_unique($calls));

        $this->_tearDownCacheTest();
    }

    public function testOnControllerActionLayoutGenerateXmlBeforeShouldRestoreDesignPackageConfigurationWhenReturning()
    {
        $designPackageMock = $this->_setupDesignPackageMock();
        $designPackageMock->setAllGetOld(array('package' => 'specific_package'));

        $this->_model->onControllerActionLayoutGenerateXmlBefore($this->_observer);

        $this->assertEquals('specific_package', $designPackageMock->getPackageName());
    }

    public function testOnControllerActionLayoutGenerateXmlBeforeShouldNotInitiateLoadLayout()
    {
        $layout = Mage::app()->getLayout();
        $this->_observer->setLayout($layout);
        $this->_model->onControllerActionLayoutGenerateXmlBefore($this->_observer);

        $this->assertEquals('<layout/>', $layout->getXmlString());
        $this->assertEquals(0, count($layout->getAllBlocks()));
    }
}